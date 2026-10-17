<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\DetailPenilaian;
use App\Models\Divisi;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengaturan;
use App\Models\Pengguna;
use App\Models\Penilaian;
use App\Models\Presensi;
use App\Models\RencanaKerja;
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
            'face_registered_at' => now(),
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

        // Test default response returns direct PDF file
        $pdfResponse = $this->actingAs($admin)->get(route('admin.presensi.cetak', [
            'bulan' => Carbon::today()->format('Y-m'),
        ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        // Test HTML template rendering
        $response = $this->actingAs($admin)->get(route('admin.presensi.cetak', [
            'bulan' => Carbon::today()->format('Y-m'),
            'format' => 'html',
        ]));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN PRESENSI MAGANG');
        $response->assertSee('Ahmad Magang');
        $response->assertSee('Dr. Pembimbing, M.Kom');
        $response->assertSee('Hadir');
        $response->assertSee('Sakit');
        $response->assertSee('Izin');
        $response->assertSee('Alfa');
        $response->assertSee('Tugas Luar');
        $pengaturan = Pengaturan::getPengaturan();
        $response->assertSee($pengaturan->nama_kepala_dinas);
        $response->assertSee($pengaturan->jabatan_kepala_dinas);
        $response->assertSee($pengaturan->nip_kepala_dinas);
        $response->assertDontSee('Pembimbing Lapangan');
    }

    public function test_admin_can_access_aktivitas_cetak_page(): void
    {
        $admin = $this->createAdminUser();
        $magangUser = $this->createMagangUser();
        $magangModel = $magangUser->magang;
        $today = Carbon::today()->toDateString();

        // Buat beberapa rencana kerja untuk memverifikasi urutan 1-N
        RencanaKerja::create([
            'magang_id' => $magangModel->id,
            'judul' => 'Analisis Kebutuhan Sistem',
            'status' => 'aktif',
        ]);
        RencanaKerja::create([
            'magang_id' => $magangModel->id,
            'judul' => 'Desain Basis Data dan Antarmuka',
            'status' => 'aktif',
        ]);
        RencanaKerja::create([
            'magang_id' => $magangModel->id,
            'judul' => 'Pengembangan Modul Laporan',
            'status' => 'aktif',
        ]);

        // Buat aktivitas dengan progres maksimal 90
        Aktivitas::create([
            'pengguna_id' => $magangUser->id,
            'tanggal' => $today,
            'isi' => 'Mengembangkan modul cetak presensi dan aktivitas',
            'progress' => 90,
            'status' => 'approve',
            'catatan_validasi' => 'Pekerjaan sangat baik',
        ]);

        // 1. Uji default request menghasilkan PDF stream langsung
        $pdfResponse = $this->actingAs($admin)->get(route('admin.aktivitas.cetak', [
            'bulan' => Carbon::today()->format('Y-m'),
        ]));

        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('Laporan-Aktivitas-Magang-', (string) $pdfResponse->headers->get('Content-Disposition'));

        // 2. Uji HTML view dengan format=html
        $response = $this->actingAs($admin)->get(route('admin.aktivitas.cetak', [
            'bulan' => Carbon::today()->format('Y-m'),
            'format' => 'html',
        ]));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN AKTIVITAS MAGANG');
        $response->assertSee('No');
        $response->assertSee('Nama');
        $response->assertSee('Pembimbing');
        $response->assertSee('Periode');
        $response->assertSee('Rencana Kerja');
        $response->assertSee('Progres');
        $response->assertSeeInOrder(['<th style="width: 20%;">Nama</th>', '<th style="width: 20%;">Pembimbing</th>'], false);

        // Verifikasi nama peserta & pembimbing
        $response->assertSee('Ahmad Magang');
        $response->assertSee('Dr. Pembimbing, M.Kom');

        // Verifikasi rencana kerja berurutan 1-N
        $response->assertSee('1. Analisis Kebutuhan Sistem');
        $response->assertSee('2. Desain Basis Data dan Antarmuka');
        $response->assertSee('3. Pengembangan Modul Laporan');

        // Verifikasi progres akhir dalam format persentase (90%)
        $response->assertSee('90%');

        // Verifikasi tanda tangan kepala dinas
        $pengaturan = Pengaturan::getPengaturan();
        $response->assertSee($pengaturan->nama_kepala_dinas);
        $response->assertSee($pengaturan->jabatan_kepala_dinas);
        $response->assertSee($pengaturan->nip_kepala_dinas);
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
        $response->assertSee('Teknik Informatika');
        $response->assertSee('Universitas Indonesia');
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

        // 1. Presensi Cetak (returns direct PDF file via mPDF)
        $resPresensi = $this->actingAs($admin)->get(route('admin.presensi.cetak'));
        $resPresensi->assertStatus(200);
        $resPresensi->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $resPresensi->getContent());

        // 2. Aktivitas Cetak (returns direct PDF file via mPDF)
        $resAktivitas = $this->actingAs($admin)->get(route('admin.aktivitas.cetak'));
        $resAktivitas->assertStatus(200);
        $resAktivitas->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $resAktivitas->getContent());

        // HTML version
        $resAktivitasHtml = $this->actingAs($admin)->get(route('admin.aktivitas.cetak', ['format' => 'html']));
        $resAktivitasHtml->assertStatus(200);
        $resAktivitasHtml->assertDontSee('id="admin-sidebar"', false);
        $resAktivitasHtml->assertDontSee('Kembali ke Monitoring Aktivitas');
        $resAktivitasHtml->assertDontSee('action="'.route('admin.aktivitas.cetak').'"', false);
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
        $resPresensi = $this->actingAs($admin)->get(route('admin.presensi.cetak', ['format' => 'html']));
        $resPresensi->assertStatus(200);
        $resPresensi->assertSee('Dr. H. Bambang Hermanto, M.M.');
        $resPresensi->assertSee('196805201994031005');
        $resPresensi->assertSee('Kepala Badan Pengelola');
        $resPresensi->assertSee('Badan Pengelola Sistem Informasi');
        $resPresensi->assertSee('Jakarta Selatan');
        $resPresensi->assertDontSee('Pembimbing Lapangan');

        // 2. Aktivitas Cetak
        $resAktivitas = $this->actingAs($admin)->get(route('admin.aktivitas.cetak', ['format' => 'html']));
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
        $magang = $magangUser->magang;
        $pembimbing = $magang->pembimbing;
        $pengaturan = Pengaturan::getPengaturan();

        // Buat kriteria penilaian
        $kriteria1 = KriteriaPenilaian::create([
            'nama' => 'Presensi',
            'bobot' => 50,
            'is_presensi' => true,
        ]);
        $kriteria2 = KriteriaPenilaian::create([
            'nama' => 'Sikap',
            'bobot' => 50,
            'is_presensi' => false,
        ]);

        // Buat penilaian magang dengan skor Presensi=90, Sikap=80
        $penilaian = Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $pembimbing->id,
            'total_nilai' => 85,
        ]);

        DetailPenilaian::create([
            'penilaian_id' => $penilaian->id,
            'kriteria_id' => $kriteria1->id,
            'nilai' => 90,
        ]);
        DetailPenilaian::create([
            'penilaian_id' => $penilaian->id,
            'kriteria_id' => $kriteria2->id,
            'nilai' => 80,
        ]);

        // 1. Uji default request menghasilkan PDF stream langsung via mPDF
        $pdfResponse = $this->actingAs($admin)->get(route('admin.penilaian.cetak'));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
        $this->assertStringContainsString('Laporan-Rekapitulasi-Penilaian-Magang', (string) $pdfResponse->headers->get('Content-Disposition'));

        // 2. Uji HTML view template dengan format=html
        $response = $this->actingAs($admin)->get(route('admin.penilaian.cetak', ['format' => 'html']));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN REKAPITULASI PENILAIAN MAGANG');
        $response->assertSee('No');
        $response->assertSee('Nama');
        $response->assertSee('Kriteria Penilaian');
        $response->assertSee('Nilai Akhir');
        $response->assertSee('Predikat');

        // Verifikasi urutan kolom di header tabel multi-level
        $response->assertSeeInOrder([
            '<th rowspan="2" style="width: 5%;">No</th>',
            '<th rowspan="2" style="width: 27%;">Nama</th>',
            '<th colspan="2">Kriteria Penilaian</th>',
            '<th rowspan="2" style="width: 12%;">Nilai Akhir</th>',
            '<th rowspan="2" style="width: 14%;">Predikat</th>',
        ], false);

        // Verifikasi sub-kolom kriteria A dan B
        $response->assertSee('>A</th>', false);
        $response->assertSee('>B</th>', false);

        // Verifikasi nama peserta
        $response->assertSee('Ahmad Magang');

        // Verifikasi nilai sub-kolom kriteria (90 untuk A, 80 untuk B)
        $response->assertSee('90');
        $response->assertSee('80');

        // Verifikasi nilai akhir dan predikat
        $response->assertSee('85');
        $response->assertSee('B');
        $response->assertSee('Baik');

        // Verifikasi keterangan kriteria di bawah tabel (A = Presensi, B = Sikap)
        $response->assertSee('Keterangan:');
        $response->assertSee('A = Presensi (50%)');
        $response->assertSee('B = Sikap (50%)');

        // Verifikasi tanda tangan kepala dinas
        $response->assertSee($pengaturan->nama_kepala_dinas);
        $response->assertSee($pengaturan->jabatan_kepala_dinas);
        $response->assertSee($pengaturan->nip_kepala_dinas);
    }

    public function test_admin_presensi_cetak_laporan_presensi_magang_rekap_seluruh_peserta_dengan_kolom_lengkap(): void
    {
        $admin = $this->createAdminUser();

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

        $pembimbingUserA = Pengguna::create([
            'username' => 'pem_a',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);
        $pembimbingA = Pembimbing::create([
            'pengguna_id' => $pembimbingUserA->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Pembimbing Alpha',
            'jabatan' => 'Pranata Komputer',
            'no_hp' => '0811111111',
        ]);

        $pembimbingUserB = Pengguna::create([
            'username' => 'pem_b',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);
        $pembimbingB = Pembimbing::create([
            'pengguna_id' => $pembimbingUserB->id,
            'nip' => '198601012010011002',
            'nama_lengkap' => 'Pembimbing Beta',
            'jabatan' => 'Arsiparis',
            'no_hp' => '0822222222',
        ]);

        $internUser1 = Pengguna::create([
            'username' => 'intern_satu',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);
        $magang1 = Magang::create([
            'pengguna_id' => $internUser1->id,
            'pembimbing_id' => $pembimbingA->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM001',
            'nama_lengkap' => 'Intern Satu Lengkap',
            'instansi_pendidikan' => 'Universitas Indonesia',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-30',
            'status' => 'aktif',
        ]);

        $internUser2 = Pengguna::create([
            'username' => 'intern_dua',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);
        $magang2 = Magang::create([
            'pengguna_id' => $internUser2->id,
            'pembimbing_id' => $pembimbingB->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM002',
            'nama_lengkap' => 'Intern Dua Lengkap',
            'instansi_pendidikan' => 'Universitas Gadjah Mada',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-30',
            'status' => 'aktif',
        ]);

        // Presensi intern 1:
        // 1. Hadir
        Presensi::create([
            'pengguna_id' => $internUser1->id,
            'tanggal' => '2026-09-01',
            'jam_masuk' => '07:55:00',
            'jam_keluar' => '16:05:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);
        // 2. Tugas Luar
        Presensi::create([
            'pengguna_id' => $internUser1->id,
            'tanggal' => '2026-09-02',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'tugas_luar',
            'keterangan' => 'Tugas luar peliputan',
        ]);
        // 3. Sakit
        PengajuanIzin::create([
            'pengguna_id' => $internUser1->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-09-03',
            'tanggal_selesai' => '2026-09-03',
            'alasan' => 'Sakit flu',
            'bukti_file' => 'bukti-izin/flu.pdf',
            'status_approval' => 'disetujui',
        ]);
        // 4. Izin
        PengajuanIzin::create([
            'pengguna_id' => $internUser1->id,
            'jenis_izin' => 'izin',
            'tanggal_mulai' => '2026-09-04',
            'tanggal_selesai' => '2026-09-04',
            'alasan' => 'Izin praktikum',
            'bukti_file' => 'bukti-izin/lab.pdf',
            'status_approval' => 'disetujui',
        ]);

        // 1. Verifikasi response default langsung berupa stream PDF (.pdf) ukuran A4
        $pdfResponse = $this->actingAs($admin)->get(route('admin.presensi.cetak', [
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
        ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        // 2. Verifikasi konten dokumen melalui format HTML
        $response = $this->actingAs($admin)->get(route('admin.presensi.cetak', [
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
            'format' => 'html',
        ]));

        $response->assertStatus(200);
        // Judul laporan
        $response->assertSee('LAPORAN PRESENSI MAGANG');
        // Subjudul periode
        $response->assertSee('Dari Tanggal 1 September 2026 Sampai Tanggal 30 September 2026');

        // Header tabel
        $response->assertSee('No');
        $response->assertSee('Nama');
        $response->assertSee('Pembimbing');
        $response->assertSee('Periode');
        $response->assertSee('Hadir');
        $response->assertSee('Sakit');
        $response->assertSee('Izin');
        $response->assertSee('Alfa');
        $response->assertSee('Tugas Luar');

        // Seluruh peserta magang muncul
        $response->assertSee('Intern Satu Lengkap');
        $response->assertSee('Pembimbing Alpha');
        $response->assertSee('Intern Dua Lengkap');
        $response->assertSee('Pembimbing Beta');
    }
}
