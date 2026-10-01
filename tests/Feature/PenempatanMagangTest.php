<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PenempatanMagang;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenempatanMagangTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    private Pembimbing $pembimbing;

    private Divisi $divisiA;

    private Divisi $divisiB;

    private Divisi $divisiC;

    private Magang $magang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'username' => 'admin_test',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing_test',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $this->pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198001012005011001',
            'nama_lengkap' => 'Pembimbing Satu',
            'jabatan' => 'Kepala Seksi',
            'no_hp' => '081234567890',
        ]);

        $this->divisiA = Divisi::create([
            'nama_divisi' => 'Divisi Kominfo',
            'nama_pimpinan' => 'Pimpinan A',
            'nip_pimpinan' => '197001011990011001',
            'jabatan_pimpinan' => 'Kepala Divisi A',
        ]);

        $this->divisiB = Divisi::create([
            'nama_divisi' => 'Divisi Statistik',
            'nama_pimpinan' => 'Pimpinan B',
            'nip_pimpinan' => '197501011995011002',
            'jabatan_pimpinan' => 'Kepala Divisi B',
        ]);

        $this->divisiC = Divisi::create([
            'nama_divisi' => 'Divisi Persandian',
            'nama_pimpinan' => 'Pimpinan C',
            'nip_pimpinan' => '198001012000011003',
            'jabatan_pimpinan' => 'Kepala Divisi C',
        ]);

        $userMagang = Pengguna::create([
            'username' => 'andi_magang',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        // Magang 6 bulan: 1 Jan 2027 s/d 30 Jun 2027
        $this->magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisiA->id,
            'nama_lengkap' => 'Andi Wijaya',
            'no_induk' => '2027001',
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-06-30',
            'status' => 'aktif',
        ]);
    }

    public function test_admin_can_view_detail_magang_with_penempatan_tab(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.magang.show', $this->magang->id));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Penempatan Divisi');
        $response->assertSee('Andi Wijaya');
        $response->assertSee('+ Tambah Penempatan');
    }

    public function test_admin_can_add_penempatan_divisi_within_periode_magang(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.magang.penempatan.store', $this->magang->id),
            [
                'divisi_id' => $this->divisiA->id,
                'tanggal_mulai' => '2027-01-01',
                'tanggal_selesai' => '2027-02-28',
            ]
        );

        $response->assertRedirect(route('admin.magang.show', ['magang' => $this->magang->id, 'tab' => 'penempatan']));

        $penempatan = PenempatanMagang::where('magang_id', $this->magang->id)->first();
        $this->assertNotNull($penempatan);
        $this->assertEquals($this->divisiA->id, $penempatan->divisi_id);
        $this->assertEquals('2027-01-01', $penempatan->tanggal_mulai->format('Y-m-d'));
        $this->assertEquals('2027-02-28', $penempatan->tanggal_selesai->format('Y-m-d'));
    }

    public function test_cannot_add_penempatan_starting_before_magang_periode(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.magang.penempatan.store', $this->magang->id),
            [
                'divisi_id' => $this->divisiA->id,
                'tanggal_mulai' => '2026-12-15', // sebelum 2027-01-01
                'tanggal_selesai' => '2027-02-28',
            ]
        );

        $response->assertSessionHasErrors(['tanggal_mulai']);
        $this->assertDatabaseCount('penempatan_magang', 0);
    }

    public function test_cannot_add_penempatan_ending_after_magang_periode(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.magang.penempatan.store', $this->magang->id),
            [
                'divisi_id' => $this->divisiA->id,
                'tanggal_mulai' => '2027-05-01',
                'tanggal_selesai' => '2027-07-15', // melewati 2027-06-30
            ]
        );

        $response->assertSessionHasErrors(['tanggal_selesai']);
        $this->assertDatabaseCount('penempatan_magang', 0);
    }

    public function test_cannot_add_penempatan_with_overlapping_dates(): void
    {
        // Penempatan pertama: 1 Jan s/d 28 Feb
        PenempatanMagang::create([
            'magang_id' => $this->magang->id,
            'divisi_id' => $this->divisiA->id,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-02-28',
        ]);

        // Coba masukkan penempatan yang bertabrakan: 15 Feb s/d 31 Mar
        $response = $this->actingAs($this->admin)->post(
            route('admin.magang.penempatan.store', $this->magang->id),
            [
                'divisi_id' => $this->divisiB->id,
                'tanggal_mulai' => '2027-02-15',
                'tanggal_selesai' => '2027-03-31',
            ]
        );

        $response->assertSessionHasErrors(['tanggal_mulai']);
        $this->assertDatabaseCount('penempatan_magang', 1);
    }

    public function test_can_add_multiple_sequential_penempatan_without_overlap(): void
    {
        // Penempatan 1: Divisi A (Jan - Feb)
        $this->actingAs($this->admin)->post(
            route('admin.magang.penempatan.store', $this->magang->id),
            [
                'divisi_id' => $this->divisiA->id,
                'tanggal_mulai' => '2027-01-01',
                'tanggal_selesai' => '2027-02-28',
            ]
        )->assertSessionHasNoErrors();

        // Penempatan 2: Divisi B (Mar - Apr)
        $this->actingAs($this->admin)->post(
            route('admin.magang.penempatan.store', $this->magang->id),
            [
                'divisi_id' => $this->divisiB->id,
                'tanggal_mulai' => '2027-03-01',
                'tanggal_selesai' => '2027-04-30',
            ]
        )->assertSessionHasNoErrors();

        // Penempatan 3: Divisi C (Mei - Jun)
        $this->actingAs($this->admin)->post(
            route('admin.magang.penempatan.store', $this->magang->id),
            [
                'divisi_id' => $this->divisiC->id,
                'tanggal_mulai' => '2027-05-01',
                'tanggal_selesai' => '2027-06-30',
            ]
        )->assertSessionHasNoErrors();

        $this->assertDatabaseCount('penempatan_magang', 3);
    }

    public function test_get_divisi_at_resolves_correct_division_for_different_dates(): void
    {
        // Buat 3 penempatan sequential sesuai PRD
        PenempatanMagang::create([
            'magang_id' => $this->magang->id,
            'divisi_id' => $this->divisiA->id,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-02-28',
        ]);

        PenempatanMagang::create([
            'magang_id' => $this->magang->id,
            'divisi_id' => $this->divisiB->id,
            'tanggal_mulai' => '2027-03-01',
            'tanggal_selesai' => '2027-04-30',
        ]);

        PenempatanMagang::create([
            'magang_id' => $this->magang->id,
            'divisi_id' => $this->divisiC->id,
            'tanggal_mulai' => '2027-05-01',
            'tanggal_selesai' => '2027-06-30',
        ]);

        // Cek 15 Januari -> Divisi A
        $divisiJan = $this->magang->getDivisiAt('2027-01-15');
        $this->assertNotNull($divisiJan);
        $this->assertEquals($this->divisiA->id, $divisiJan->id);

        // Cek 15 Maret -> Divisi B
        $divisiMar = $this->magang->getDivisiAt('2027-03-15');
        $this->assertNotNull($divisiMar);
        $this->assertEquals($this->divisiB->id, $divisiMar->id);

        // Cek 15 Mei -> Divisi C
        $divisiMei = $this->magang->getDivisiAt('2027-05-15');
        $this->assertNotNull($divisiMei);
        $this->assertEquals($this->divisiC->id, $divisiMei->id);
    }

    public function test_penempatan_status_computation(): void
    {
        // 1. Masa lalu -> Selesai
        $lampau = PenempatanMagang::create([
            'magang_id' => $this->magang->id,
            'divisi_id' => $this->divisiA->id,
            'tanggal_mulai' => Carbon::today()->subMonths(3)->toDateString(),
            'tanggal_selesai' => Carbon::today()->subMonths(1)->toDateString(),
        ]);
        $this->assertEquals('Selesai', $lampau->status);

        // 2. Sekarang -> Berjalan
        $berjalan = PenempatanMagang::create([
            'magang_id' => $this->magang->id,
            'divisi_id' => $this->divisiB->id,
            'tanggal_mulai' => Carbon::today()->subDays(5)->toDateString(),
            'tanggal_selesai' => Carbon::today()->addDays(20)->toDateString(),
        ]);
        $this->assertEquals('Berjalan', $berjalan->status);
        $this->assertTrue($berjalan->isBerjalan());

        // 3. Masa depan -> Akan Datang
        $depan = PenempatanMagang::create([
            'magang_id' => $this->magang->id,
            'divisi_id' => $this->divisiC->id,
            'tanggal_mulai' => Carbon::today()->addMonths(2)->toDateString(),
            'tanggal_selesai' => Carbon::today()->addMonths(4)->toDateString(),
        ]);
        $this->assertEquals('Akan Datang', $depan->status);
    }

    public function test_admin_can_update_penempatan(): void
    {
        $penempatan = PenempatanMagang::create([
            'magang_id' => $this->magang->id,
            'divisi_id' => $this->divisiA->id,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-02-28',
        ]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.magang.penempatan.update', ['magang' => $this->magang->id, 'penempatan' => $penempatan->id]),
            [
                'divisi_id' => $this->divisiB->id,
                'tanggal_mulai' => '2027-01-01',
                'tanggal_selesai' => '2027-02-25',
            ]
        );

        $response->assertRedirect(route('admin.magang.show', ['magang' => $this->magang->id, 'tab' => 'penempatan']));

        $this->assertEquals($this->divisiB->id, $penempatan->fresh()->divisi_id);
        $this->assertEquals('2027-02-25', $penempatan->fresh()->tanggal_selesai->format('Y-m-d'));
    }

    public function test_admin_can_delete_penempatan(): void
    {
        $penempatan = PenempatanMagang::create([
            'magang_id' => $this->magang->id,
            'divisi_id' => $this->divisiA->id,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-02-28',
        ]);

        $response = $this->actingAs($this->admin)->delete(
            route('admin.magang.penempatan.destroy', ['magang' => $this->magang->id, 'penempatan' => $penempatan->id])
        );

        $response->assertRedirect(route('admin.magang.show', ['magang' => $this->magang->id, 'tab' => 'penempatan']));
        $this->assertDatabaseMissing('penempatan_magang', [
            'id' => $penempatan->id,
        ]);
    }
}
