<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\DetailPenilaian;
use App\Models\Divisi;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Models\Pengguna;
use App\Models\Penilaian;
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
                'radius_meter' => 40,
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
        $response->assertSee('08:00 WITA');
        $pengaturan = Pengaturan::getPengaturan();
        $response->assertSee($pengaturan->nama_kepala_dinas);
        $response->assertSee($pengaturan->jabatan_kepala_dinas);
        $response->assertSee($pengaturan->nip_kepala_dinas);
        $response->assertDontSee('Pembimbing Lapangan');
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
        $pengaturan = Pengaturan::getPengaturan();
        $response->assertSee($pengaturan->nama_kepala_dinas);
        $response->assertSee($pengaturan->jabatan_kepala_dinas);
        $response->assertSee($pengaturan->nip_kepala_dinas);
        $response->assertDontSee('Mengetahui & Mengesahkan,');
        $response->assertDontSee('Kepala Sub-Bagian');
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

    public function test_cetak_pages_render_standalone_print_sheet_without_app_chrome_or_redundant_filters(): void
    {
        $admin = $this->createAdminUser();

        // 1. Presensi Cetak
        $resPresensi = $this->actingAs($admin)->get(route('admin.presensi.cetak'));
        $resPresensi->assertStatus(200);
        $resPresensi->assertSee('print-sheet');
        $resPresensi->assertSee('window.print()');
        $resPresensi->assertDontSee('id="admin-sidebar"', false);
        $resPresensi->assertDontSee('Kembali ke Monitoring Presensi');
        $resPresensi->assertDontSee('action="'.route('admin.presensi.cetak').'"', false);

        // 2. Aktivitas Cetak
        $resAktivitas = $this->actingAs($admin)->get(route('admin.aktivitas.cetak'));
        $resAktivitas->assertStatus(200);
        $resAktivitas->assertSee('print-sheet');
        $resAktivitas->assertSee('window.print()');
        $resAktivitas->assertDontSee('id="admin-sidebar"', false);
        $resAktivitas->assertDontSee('Kembali ke Monitoring Aktivitas');
        $resAktivitas->assertDontSee('action="'.route('admin.aktivitas.cetak').'"', false);
    }

    public function test_monitoring_cetak_buttons_open_in_new_tab_and_forward_filters(): void
    {
        $admin = $this->createAdminUser();

        $resPresensi = $this->actingAs($admin)->get(route('admin.presensi.index', [
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
        ]));
        $resPresensi->assertStatus(200);
        $resPresensi->assertSee('target="_blank"', false);
        $resPresensi->assertSee('tanggal_mulai=2026-09-01');
        $resPresensi->assertSee('tanggal_akhir=2026-09-30');

        $resAktivitas = $this->actingAs($admin)->get(route('admin.aktivitas.index', [
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
        ]));
        $resAktivitas->assertStatus(200);
        $resAktivitas->assertSee('target="_blank"', false);
        $resAktivitas->assertSee('tanggal_mulai=2026-09-01');
        $resAktivitas->assertSee('tanggal_akhir=2026-09-30');
    }

    public function test_admin_cetak_signature_only_for_ketua_instansi_from_pengaturan(): void
    {
        $admin = $this->createAdminUser();
        $pengaturan = Pengaturan::getPengaturan();
        $pengaturan->update([
            'nama_kepala_dinas' => 'Dr. H. Bambang Hermanto, M.M.',
            'nip_kepala_dinas' => '196805201994031005',
            'jabatan_kepala_dinas' => 'Kepala Badan Pengelola',
            'nama_instansi' => 'Badan Pengelola Sistem Informasi',
            'kota_surat' => 'Jakarta Selatan',
        ]);

        // 1. Presensi Cetak
        $resPresensi = $this->actingAs($admin)->get(route('admin.presensi.cetak'));
        $resPresensi->assertStatus(200);
        $resPresensi->assertSee('Dr. H. Bambang Hermanto, M.M.');
        $resPresensi->assertSee('196805201994031005');
        $resPresensi->assertSee('Kepala Badan Pengelola');
        $resPresensi->assertSee('Badan Pengelola Sistem Informasi');
        $resPresensi->assertSee('Jakarta Selatan');
        $resPresensi->assertDontSee('Pembimbing Lapangan');

        // 2. Aktivitas Cetak
        $resAktivitas = $this->actingAs($admin)->get(route('admin.aktivitas.cetak'));
        $resAktivitas->assertStatus(200);
        $resAktivitas->assertSee('Dr. H. Bambang Hermanto, M.M.');
        $resAktivitas->assertSee('196805201994031005');
        $resAktivitas->assertSee('Kepala Badan Pengelola');
        $resAktivitas->assertSee('Badan Pengelola Sistem Informasi');
        $resAktivitas->assertSee('Jakarta Selatan');
        $resAktivitas->assertDontSee('Mengetahui & Mengesahkan,');
        $resAktivitas->assertDontSee('Kepala Sub-Bagian');
    }

    public function test_admin_penilaian_show_uses_print_layout_without_system_chrome(): void
    {
        $admin = $this->createAdminUser();
        $magangUser = $this->createMagangUser();
        $magang = $magangUser->magang;
        $pembimbing = $magang->pembimbing;

        $kriteria = KriteriaPenilaian::create([
            'nama' => 'Inisiatif & Keaktifan',
            'bobot' => 100,
            'keterangan' => 'Kriteria uji',
        ]);

        $penilaian = Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $pembimbing->id,
            'total_nilai' => 90,
        ]);

        DetailPenilaian::create([
            'penilaian_id' => $penilaian->id,
            'kriteria_id' => $kriteria->id,
            'nilai' => 90,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.penilaian.show', $penilaian->id));

        $response->assertStatus(200);
        $response->assertSee('LEMBAR PENILAIAN AKHIR MAGANG');
        $response->assertSee('Lembar Cetak Dokumen');
        $response->assertSee('Cetak / Simpan PDF');
        $response->assertSee('Tutup Tab');
        $response->assertSee($magang->nama_lengkap);
        $response->assertDontSee('sidebar-scroll');
        $response->assertDontSee('toggleAdminSidebar');
    }

    public function test_admin_penilaian_cetak_rekapitulasi_uses_print_layout(): void
    {
        $admin = $this->createAdminUser();
        $magangUser = $this->createMagangUser();
        $pengaturan = Pengaturan::getPengaturan();

        $response = $this->actingAs($admin)->get(route('admin.penilaian.cetak'));

        $response->assertStatus(200);
        $response->assertSee('REKAPITULASI PENILAIAN AKHIR MAGANG');
        $response->assertSee('Lembar Cetak Dokumen');
        $response->assertSee('Cetak / Simpan PDF');
        $response->assertSee($pengaturan->nama_kepala_dinas);
        $response->assertDontSee('sidebar-scroll');
        $response->assertDontSee('toggleAdminSidebar');
    }
}
