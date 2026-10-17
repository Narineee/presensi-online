<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutoDeactivateExpiredMagangTest extends TestCase
{
    use RefreshDatabase;

    private function createMentor(string $prefix = 'mentor'): Pembimbing
    {
        $unique = uniqid();
        $user = Pengguna::create([
            'username' => $prefix.'_'.$unique,
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        return Pembimbing::create([
            'pengguna_id' => $user->id,
            'nip' => '19800101'.rand(100000, 999999),
            'nama_lengkap' => 'Pembimbing Uji '.$unique,
            'jabatan' => 'Pembimbing',
        ]);
    }

    private function createAdmin(): Pengguna
    {
        return Pengguna::create([
            'username' => 'admin_test',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createIntern(string $username, string $tglMulai, string $tglSelesai, string $status = 'aktif'): array
    {
        $mentor = $this->createMentor();
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Umum']);

        $user = Pengguna::create([
            'username' => $username,
            'password' => Hash::make('password123'),
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $mentor->id,
            'divisi_id' => $divisi->id,
            'nama_lengkap' => 'Peserta Uji',
            'tanggal_mulai' => $tglMulai,
            'tanggal_selesai' => $tglSelesai,
            'status' => $status,
        ]);

        return [$user, $magang];
    }

    public function test_magang_with_expired_period_can_still_login_and_status_is_marked_selesai(): void
    {
        // Periode berakhir 10 hari yang lalu
        $tglMulai = Carbon::today()->subMonths(2)->format('Y-m-d');
        $tglSelesai = Carbon::today()->subDays(10)->format('Y-m-d');

        [$user, $magang] = $this->createIntern('magang_expired', $tglMulai, $tglSelesai, 'aktif');

        // Peserta magang mencoba login setelah periodenya selesai
        $response = $this->post(route('login'), [
            'username' => 'magang_expired',
            'password' => 'password123',
        ]);

        // Login harus berhasil dan masuk ke dashboard
        $response->assertRedirect(route('magang.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Akun tetap aktif agar peserta bisa mencetak rekap
        $this->assertDatabaseHas('pengguna', [
            'id' => $user->id,
            'is_active' => true,
        ]);

        // Status magang otomatis ditandai selesai
        $this->assertDatabaseHas('magang', [
            'id' => $magang->id,
            'status' => 'selesai',
        ]);
    }

    public function test_admin_can_toggle_magang_account_status(): void
    {
        $admin = $this->createAdmin();

        $tglMulai = Carbon::today()->subMonths(2)->format('Y-m-d');
        $tglSelesai = Carbon::today()->subDays(5)->format('Y-m-d');

        [$user, $magang] = $this->createIntern('magang_toggle', $tglMulai, $tglSelesai, 'aktif');

        // 1. Admin menonaktifkan akun peserta
        $response = $this->actingAs($admin)->post(route('admin.magang.toggle-status', $magang->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengguna', [
            'id' => $user->id,
            'is_active' => false,
        ]);

        // Saat non-aktif, peserta tidak bisa login
        $this->post('/logout');
        $loginAttempt = $this->post(route('login'), [
            'username' => 'magang_toggle',
            'password' => 'password123',
        ]);
        $loginAttempt->assertSessionHasErrors('username');
        $this->assertGuest();

        // 2. Admin mengaktifkan akun kembali
        $response2 = $this->actingAs($admin)->post(route('admin.magang.toggle-status', $magang->id));
        $response2->assertSessionHas('success');

        $this->assertDatabaseHas('pengguna', [
            'id' => $user->id,
            'is_active' => true,
        ]);

        // Sekarang peserta bisa login lagi
        $this->post('/logout');
        $loginSuccess = $this->post(route('login'), [
            'username' => 'magang_toggle',
            'password' => 'password123',
        ]);
        $loginSuccess->assertRedirect(route('magang.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_update_magang_and_check_or_uncheck_is_active(): void
    {
        $admin = $this->createAdmin();
        $tglMulai = Carbon::today()->subMonths(1)->format('Y-m-d');
        $tglSelesai = Carbon::today()->addMonths(1)->format('Y-m-d');

        [$user, $magang] = $this->createIntern('magang_edit', $tglMulai, $tglSelesai, 'aktif');

        // Admin mencentang akun aktif saat edit (is_active dikirim)
        $response = $this->actingAs($admin)->put(route('admin.magang.update', $magang->id), [
            'nama_lengkap' => 'Peserta Update',
            'pembimbing_id' => $magang->pembimbing_id,
            'tanggal_mulai' => $tglMulai,
            'tanggal_selesai' => $tglSelesai,
            'status' => 'aktif',
            'username' => 'magang_edit',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.magang.index'));
        $this->assertDatabaseHas('pengguna', [
            'id' => $user->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('magang', [
            'id' => $magang->id,
            'nama_lengkap' => 'Peserta Update',
        ]);

        // Admin mengosongkan centang is_active saat edit (is_active tidak dikirim)
        $response2 = $this->actingAs($admin)->put(route('admin.magang.update', $magang->id), [
            'nama_lengkap' => 'Peserta Update Nonaktif',
            'pembimbing_id' => $magang->pembimbing_id,
            'tanggal_mulai' => $tglMulai,
            'tanggal_selesai' => $tglSelesai,
            'status' => 'aktif',
            'username' => 'magang_edit',
        ]);

        $response2->assertRedirect(route('admin.magang.index'));
        $this->assertDatabaseHas('pengguna', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_authenticated_magang_with_expired_period_is_not_logged_out(): void
    {
        $tglMulai = Carbon::today()->subMonths(2)->format('Y-m-d');
        $tglSelesai = Carbon::today()->subDays(3)->format('Y-m-d');

        [$user, $magang] = $this->createIntern('magang_session', $tglMulai, $tglSelesai, 'aktif');

        // Peserta mengakses rute rekap dan dashboard
        $response = $this->actingAs($user)->get(route('magang.rekap'));

        // Tidak di-kick atau redirect ke login
        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);

        // Akun tetap aktif
        $this->assertDatabaseHas('pengguna', [
            'id' => $user->id,
            'is_active' => true,
        ]);
    }

    public function test_artisan_command_syncs_expired_magang_to_selesai_without_deactivating_accounts(): void
    {
        $tglMulai = Carbon::today()->subMonths(2)->format('Y-m-d');
        $tglSelesai = Carbon::today()->subDays(1)->format('Y-m-d');

        [$user1, $magang1] = $this->createIntern('magang_cron1', $tglMulai, $tglSelesai, 'aktif');
        [$user2, $magang2] = $this->createIntern('magang_cron2', Carbon::today()->format('Y-m-d'), Carbon::today()->addMonth()->format('Y-m-d'), 'aktif');

        // Jalankan perintah console
        Artisan::call('magang:sync-periode');

        // magang1 periodenya telah lewat: status ditandai selesai, tapi akun tetap aktif
        $this->assertDatabaseHas('pengguna', [
            'id' => $user1->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('magang', [
            'id' => $magang1->id,
            'status' => 'selesai',
        ]);

        // magang2 periodenya belum lewat: status tetap aktif
        $this->assertDatabaseHas('pengguna', [
            'id' => $user2->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('magang', [
            'id' => $magang2->id,
            'status' => 'aktif',
        ]);
    }
}
