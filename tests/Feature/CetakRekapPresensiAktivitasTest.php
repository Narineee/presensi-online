<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Cs;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CetakRekapPresensiAktivitasTest extends TestCase
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
        $user = Pengguna::create([
            'username' => 'magang_test',
            'password' => 'password123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $divisi = Divisi::firstOrCreate(
            ['nama_divisi' => 'Teknologi Informasi'],
            [
                'nama_pimpinan' => 'Ir. H. Budi Santoso, M.T.',
                'nip_pimpinan' => '197501012000031001',
                'jabatan_pimpinan' => 'Kepala Sub-Bagian IT',
                'latitude' => -3.4893886,
                'longitude' => 114.8252584,
                'radius_meter' => 100,
            ]
        );

        $pembimbingUser = Pengguna::firstOrCreate(
            ['username' => 'pembimbing_test'],
            ['password' => 'password123', 'role' => 'pembimbing', 'is_active' => true]
        );

        $pembimbing = Pembimbing::firstOrCreate(
            ['pengguna_id' => $pembimbingUser->id],
            [
                'nip' => '198801012015011001',
                'nama_lengkap' => 'Dr. Pembimbing, M.Kom',
                'jabatan' => 'Senior Developer',
                'no_hp' => '08123456789',
            ]
        );

        Magang::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'MG-2026-001',
            'nama_lengkap' => 'Ahmad Magang',
            'jurusan' => 'Teknik Informatika',
            'instansi_pendidikan' => 'Universitas Indonesia',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
        ]);

        return $user;
    }

    private function createCsUser(): Pengguna
    {
        $user = Pengguna::create([
            'username' => 'cs_test',
            'password' => 'password123',
            'role' => 'cs',
            'is_active' => true,
        ]);

        $pembimbingUser = Pengguna::firstOrCreate(
            ['username' => 'pembimbing_test'],
            ['password' => 'password123', 'role' => 'pembimbing', 'is_active' => true]
        );

        $pembimbing = Pembimbing::firstOrCreate(
            ['pengguna_id' => $pembimbingUser->id],
            [
                'nip' => '198801012015011001',
                'nama_lengkap' => 'Dr. Pembimbing, M.Kom',
                'jabatan' => 'Senior Developer',
                'no_hp' => '08123456789',
            ]
        );

        Divisi::firstOrCreate(
            ['nama_divisi' => 'Teknologi Informasi'],
            [
                'nama_pimpinan' => 'Ir. H. Budi Santoso, M.T.',
                'nip_pimpinan' => '197501012000031001',
                'jabatan_pimpinan' => 'Kepala Sub-Bagian IT',
                'latitude' => -3.4893886,
                'longitude' => 114.8252584,
                'radius_meter' => 100,
            ]
        );

        Cs::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $pembimbing->id,
            'nik' => 'CS-889900',
            'nama_lengkap' => 'Siti CS Profesional',
            'jabatan' => 'Customer Service Representative',
            'no_hp' => '08987654321',
            'tanggal_bergabung' => '2025-06-01',
            'status' => 'aktif',
        ]);

        return $user;
    }

    public function test_admin_can_access_presensi_cetak_page(): void
    {
        $admin = $this->createAdminUser();
        $magang = $this->createMagangUser();
        $today = Carbon::today()->toDateString();

        Presensi::create([
            'pengguna_id' => $magang->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '17:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
            'keterangan' => 'Tepat waktu',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.presensi.cetak', [
            'bulan' => Carbon::today()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('REKAPITULASI PRESENSI KEHADIRAN');
        $response->assertSee('Ahmad Magang');
        $response->assertSee('08:00 WIB');
        $response->assertSee('17:00 WIB');
        $response->assertSee('Dr. Pembimbing, M.Kom');
        $response->assertSee('Ir. H. Budi Santoso, M.T.');
        $response->assertSee('Pembimbing Lapangan');
        $response->assertSee('Kepala Sub-Bagian IT');
    }

    public function test_admin_can_access_aktivitas_cetak_page(): void
    {
        $admin = $this->createAdminUser();
        $magang = $this->createMagangUser();
        $today = Carbon::today()->toDateString();

        Aktivitas::create([
            'pengguna_id' => $magang->id,
            'tanggal' => $today,
            'isi' => 'Mengembangkan modul cetak presensi dan aktivitas',
            'progress' => 100,
            'status' => 'approve',
            'catatan_validasi' => 'Pekerjaan sangat baik',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.aktivitas.cetak', [
            'bulan' => Carbon::today()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('REKAPITULASI LOG AKTIVITAS HARIAN');
        $response->assertSee('Ahmad Magang');
        $response->assertSee('Mengembangkan modul cetak presensi dan aktivitas');
        $response->assertSee('Pekerjaan sangat baik');
        $response->assertSee('Dr. Pembimbing, M.Kom');
        $response->assertSee('Ir. H. Budi Santoso, M.T.');
        $response->assertSee('Pembimbing Lapangan');
        $response->assertSee('Kepala Sub-Bagian IT');
    }

    public function test_magang_can_access_personal_presensi_cetak(): void
    {
        $magang = $this->createMagangUser();
        $today = Carbon::today()->toDateString();

        Presensi::create([
            'pengguna_id' => $magang->id,
            'tanggal' => $today,
            'jam_masuk' => '08:05:00',
            'jam_keluar' => '17:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($magang)->get(route('presensi.cetak', [
            'bulan' => Carbon::today()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('LEMBAR REKAPITULASI PRESENSI KEHADIRAN');
        $response->assertSee('Ahmad Magang');
        $response->assertSee('MG-2026-001');
        $response->assertSee('Peserta Magang');
        $response->assertSee('Teknologi Informasi');
        $response->assertSee('08:05 WITA');
        $response->assertSee('Dr. Pembimbing, M.Kom');
        $response->assertSee('Ir. H. Budi Santoso, M.T.');
        $response->assertSee('Pembimbing Lapangan');
        $response->assertSee('Kepala Sub-Bagian IT');
    }

    public function test_magang_can_access_personal_aktivitas_cetak(): void
    {
        $magang = $this->createMagangUser();
        $today = Carbon::today()->toDateString();

        Aktivitas::create([
            'pengguna_id' => $magang->id,
            'tanggal' => $today,
            'isi' => 'Membuat unit test untuk fitur laporan cetak',
            'progress' => 100,
            'status' => 'approve',
        ]);

        $response = $this->actingAs($magang)->get(route('aktivitas.cetak', [
            'bulan' => Carbon::today()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('LEMBAR REKAPITULASI AKTIVITAS HARIAN');
        $response->assertSee('Ahmad Magang');
        $response->assertSee('Membuat unit test untuk fitur laporan cetak');
        $response->assertSee('100%');
        $response->assertSee('Dr. Pembimbing, M.Kom');
        $response->assertSee('Ir. H. Budi Santoso, M.T.');
        $response->assertSee('Pembimbing Lapangan');
        $response->assertSee('Kepala Sub-Bagian IT');
    }

    public function test_cs_can_access_personal_presensi_cetak(): void
    {
        $cs = $this->createCsUser();
        $today = Carbon::today()->toDateString();

        Presensi::create([
            'pengguna_id' => $cs->id,
            'tanggal' => $today,
            'jam_masuk' => '07:55:00',
            'jam_keluar' => '16:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($cs)->get(route('presensi.cetak', [
            'bulan' => Carbon::today()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('LEMBAR REKAPITULASI PRESENSI KEHADIRAN');
        $response->assertSee('Siti CS Profesional');
        $response->assertSee('CS-889900');
        $response->assertSee('Customer Service (CS)');
        $response->assertSee('07:55 WITA');
        $response->assertSee('Dr. Pembimbing, M.Kom');
        $response->assertSee('Ir. H. Budi Santoso, M.T.');
        $response->assertSee('Pembimbing Lapangan');
        $response->assertSee('Kepala Sub-Bagian IT');
    }

    public function test_cs_can_access_aktivitas_cetak(): void
    {
        $cs = $this->createCsUser();

        $response = $this->actingAs($cs)->get(route('aktivitas.cetak'));

        $response->assertStatus(200);
        $response->assertSee('LEMBAR REKAPITULASI AKTIVITAS HARIAN');
        $response->assertSee('Siti CS Profesional');
    }

    public function test_cs_can_access_aktivitas_index(): void
    {
        $cs = $this->createCsUser();

        $response = $this->actingAs($cs)->get(route('aktivitas.index'));

        $response->assertStatus(200);
    }

    public function test_presensi_index_shows_cetak_button(): void
    {
        $magang = $this->createMagangUser();

        $response = $this->actingAs($magang)->get(route('presensi.index'));

        $response->assertStatus(200);
        $response->assertSee(route('presensi.cetak', ['bulan' => date('Y-m')]));
        $response->assertSee('Cetak Rekap');
    }

    public function test_aktivitas_index_shows_cetak_button_for_magang(): void
    {
        $magang = $this->createMagangUser();

        $response = $this->actingAs($magang)->get(route('aktivitas.index'));

        $response->assertStatus(200);
        $response->assertSee(route('aktivitas.cetak'));
        $response->assertSee('Cetak Rekap');
    }

    public function test_admin_pages_show_cetak_buttons(): void
    {
        $admin = $this->createAdminUser();

        // Presensi Monitoring
        $resPresensi = $this->actingAs($admin)->get(route('admin.presensi.index'));
        $resPresensi->assertStatus(200);
        $resPresensi->assertSee(route('admin.presensi.cetak'));
        $resPresensi->assertSee('Cetak Rekap Presensi');

        // Aktivitas Monitoring
        $resAktivitas = $this->actingAs($admin)->get(route('admin.aktivitas.index'));
        $resAktivitas->assertStatus(200);
        $resAktivitas->assertSee(route('admin.aktivitas.cetak'));
        $resAktivitas->assertSee('Cetak Rekap Aktivitas');
    }

    public function test_cetak_handles_invalid_bulan_parameter_gracefully_without_exception(): void
    {
        $magang = $this->createMagangUser();

        // Presensi cetak with malformed bulan
        $resPresensi = $this->actingAs($magang)->get(route('presensi.cetak', ['bulan' => 'invalid-format']));
        $resPresensi->assertStatus(200);

        // Aktivitas cetak with malformed bulan
        $resAktivitas = $this->actingAs($magang)->get(route('aktivitas.cetak', ['bulan' => 'invalid-format']));
        $resAktivitas->assertStatus(200);
    }
}
