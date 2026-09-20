<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanAplikasiTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): Pengguna
    {
        return Pengguna::create([
            'username' => 'admin_test',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createMagangUser(): Pengguna
    {
        return Pengguna::create([
            'username' => 'magang_test',
            'password' => 'password123',
            'role' => 'magang',
            'is_active' => true,
        ]);
    }

    private function createPembimbingUser(): Pengguna
    {
        return Pengguna::create([
            'username' => 'pembimbing_test',
            'password' => 'password123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_pengaturan_page(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.pengaturan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Aplikasi');
        $response->assertSee('Data Ketua / Kepala Dinas');
        $response->assertSee('Pratinjau Tanda Tangan');
    }

    public function test_admin_can_update_nama_ketua_dinas_and_settings(): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'nama_kepala_dinas' => 'Prof. Dr. Ir. H. Muhammad Ridwan, M.Eng.',
            'nip_kepala_dinas' => '196805121993031002',
            'jabatan_kepala_dinas' => 'Kepala Dinas Komunikasi, Informatika, dan Statistik',
            'pangkat_golongan' => 'Pembina Utama (IV/d)',
            'nama_instansi' => 'Dinas Komunikasi, Informatika, dan Statistik',
            'nama_aplikasi' => 'Portal Presensi Digital Terpadu',
            'kota_surat' => 'Kota Banjarbaru',
            'alamat_instansi' => 'Jl. Panglima Batur Timur No. 12, Banjarbaru',
            'telepon' => '(0511) 4771234',
            'email' => 'diskominfostatistik@banjarbarukota.go.id',
            'website' => 'https://diskominfostatistik.banjarbarukota.go.id',
        ];

        $response = $this->actingAs($admin)
            ->put(route('admin.pengaturan.update'), $payload);

        $response->assertRedirect(route('admin.pengaturan.index'));
        $response->assertSessionHas('success', 'Pengaturan aplikasi dan data Ketua / Kepala Dinas berhasil disimpan!');

        $this->assertDatabaseHas('pengaturan', [
            'nama_kepala_dinas' => 'Prof. Dr. Ir. H. Muhammad Ridwan, M.Eng.',
            'nip_kepala_dinas' => '196805121993031002',
            'jabatan_kepala_dinas' => 'Kepala Dinas Komunikasi, Informatika, dan Statistik',
            'pangkat_golongan' => 'Pembina Utama (IV/d)',
            'nama_instansi' => 'Dinas Komunikasi, Informatika, dan Statistik',
            'nama_aplikasi' => 'Portal Presensi Digital Terpadu',
            'kota_surat' => 'Kota Banjarbaru',
        ]);
    }

    public function test_update_pengaturan_requires_nama_kepala_dinas(): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'nama_kepala_dinas' => '', // Kosong (invalid)
            'nip_kepala_dinas' => '196805121993031002',
            'jabatan_kepala_dinas' => 'Kepala Dinas',
            'nama_instansi' => 'Dinas Komunikasi dan Informatika',
            'nama_aplikasi' => 'Presensi Digital',
            'kota_surat' => 'Banjarbaru',
        ];

        $response = $this->actingAs($admin)
            ->put(route('admin.pengaturan.update'), $payload);

        $response->assertSessionHasErrors(['nama_kepala_dinas']);
    }

    public function test_guest_cannot_access_pengaturan(): void
    {
        $response = $this->get(route('admin.pengaturan.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_magang_user_cannot_access_pengaturan(): void
    {
        $magang = $this->createMagangUser();

        $response = $this->actingAs($magang)->get(route('admin.pengaturan.index'));

        $response->assertStatus(403);
    }

    public function test_pembimbing_user_cannot_access_pengaturan(): void
    {
        $pembimbing = $this->createPembimbingUser();

        $response = $this->actingAs($pembimbing)->get(route('admin.pengaturan.index'));

        $response->assertStatus(403);
    }
}
