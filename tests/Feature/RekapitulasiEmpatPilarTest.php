<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pekerjaan;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapitulasiEmpatPilarTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $userMagang;

    private Magang $magang;

    private Pembimbing $pembimbing;

    private Divisi $divisi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->divisi = Divisi::create([
            'nama_divisi' => 'Bidang Informatika',
            'nama_pimpinan' => 'Dr. H. Kepala Dinas',
            'nip_pimpinan' => '197501012000031001',
            'jabatan_pimpinan' => 'Kepala Bidang',
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing_test',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $this->pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Budi Santoso, S.Kom',
            'jabatan' => 'Pranata Komputer Ahli Muda',
            'no_hp' => '081234567890',
        ]);

        $this->userMagang = Pengguna::create([
            'username' => 'magang_test',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $this->magang = Magang::create([
            'pengguna_id' => $this->userMagang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'nama_lengkap' => 'Hasnia Putri Magang',
            'no_induk' => '20261001',
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat',
            'jurusan' => 'Ilmu Komputer',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
            'face_registered_at' => now(),
        ]);
    }

    public function test_magang_rekap_hub_menampilkan_4_pilar_dan_metrik(): void
    {
        Presensi::create([
            'pengguna_id' => $this->userMagang->id,
            'tanggal' => '2026-10-01',
            'jam_masuk' => '07:45:00',
            'jam_keluar' => '16:15:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($this->userMagang)->get(route('magang.rekap'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi &amp; Cetak Dokumen', false);
        $response->assertSee('Pantau dan cetak rekapitulasi');
        $response->assertSee('Kehadiran');
        $response->assertSee('Jam Magang');
        $response->assertSee('Progres periode');
        $response->assertSee('Riwayat Anda');
        $response->assertSee(route('presensi.riwayat'));
        $response->assertSee(route('aktivitas.riwayat'));
        $response->assertSee(route('izin.riwayat'));
        $response->assertSee(route('magang.penilaian.index'));
    }

    public function test_riwayat_presensi_pilar_1_menampilkan_data_dan_filter(): void
    {
        Presensi::create([
            'pengguna_id' => $this->userMagang->id,
            'tanggal' => '2026-10-02',
            'jam_masuk' => '07:50:00',
            'jam_keluar' => '16:05:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($this->userMagang)->get(route('presensi.riwayat', [
            'tanggal_awal' => '2026-10-01',
            'tanggal_selesai' => '2026-10-05',
            'mode_kerja' => 'onsite',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rekap Presensi Anda');
        $response->assertSee('Riwayat Presensi Saya');
        $response->assertSee('Terapkan Filter');
        $response->assertSee('Cetak Rekapitulasi');
        $response->assertSee('07:50 WITA');
    }

    public function test_rekap_aktivitas_pilar_2_menampilkan_4_kotak_metrik_dan_daftar(): void
    {
        $pekerjaan = Pekerjaan::create([
            'magang_id' => $this->magang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'judul' => 'Sistem Rekapitulasi',
            'deskripsi' => 'Pengembangan fitur rekap',
            'jenis' => 'proyek',
            'progress' => 50,
            'tanggal_mulai' => '2026-09-01',
            'status' => 'aktif',
        ]);

        Aktivitas::create([
            'pengguna_id' => $this->userMagang->id,
            'pekerjaan_id' => $pekerjaan->id,
            'judul' => 'Pembuatan Halaman Rekap Hub',
            'tanggal' => '2026-10-05',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '16:00',
            'isi' => 'Mengimplementasikan UI Glassmorphism dan 4 kartu riwayat sesuai PRD images.',
            'progress' => 50,
            'status' => 'approve',
        ]);

        $response = $this->actingAs($this->userMagang)->get(route('aktivitas.index'));

        $response->assertStatus(200);
        $response->assertSee('Rekap Aktivitas Harian Anda');
        $response->assertSee('TOTAL AKTIVITAS');
        $response->assertSee('DISETUJUI');
        $response->assertSee('MENUNGGU');
        $response->assertSee('PERLU REVISI');
        $response->assertSee('Riwayat Aktivitas Saya');
        $response->assertSee('Cetak Rekapitulasi');
        $response->assertSee('Pembuatan Halaman Rekap Hub');
    }

    public function test_detail_aktivitas_sesuai_prd_layout(): void
    {
        $pekerjaan = Pekerjaan::create([
            'magang_id' => $this->magang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'judul' => 'Tugas Integrasi PRD',
            'deskripsi' => 'Deskripsi pekerjaan',
            'jenis' => 'rutin',
            'progress' => 0,
            'tanggal_mulai' => '2026-09-01',
            'status' => 'aktif',
        ]);

        $act = Aktivitas::create([
            'pengguna_id' => $this->userMagang->id,
            'pekerjaan_id' => $pekerjaan->id,
            'judul' => 'Testing Flow PRD',
            'tanggal' => '2026-10-06',
            'waktu_mulai' => '08:15',
            'waktu_selesai' => '16:30',
            'isi' => 'Detail uraian aktivitas harian pekerjaan yang dikerjakan peserta magang.',
            'progress' => 10,
            'status' => 'approve',
        ]);

        $response = $this->actingAs($this->userMagang)->get(route('aktivitas.show', $act->id));

        $response->assertStatus(200);
        $response->assertSee('Detail aktivitas harian');
        $response->assertSee('TANGGAL PELAKSANAAN');
        $response->assertSee('Capaian Progres Pekerjaan');
        $response->assertSee('PEKERJAAN YANG DIBERIKAN');
        $response->assertSee('URAIAN AKTIVITAS YANG DIBERIKAN');
        $response->assertSee('INFORMASI VALIDASI PEMBIMBING');
    }

    public function test_riwayat_ketidakhadiran_pilar_3_menampilkan_data(): void
    {
        PengajuanIzin::create([
            'pengguna_id' => $this->userMagang->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-10-03',
            'tanggal_selesai' => '2026-10-04',
            'alasan' => 'Demam dan konsultasi dokter',
            'status_approval' => 'disetujui',
        ]);

        $response = $this->actingAs($this->userMagang)->get(route('izin.index'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Permohonan Ketidakhadiran');
        $response->assertSee('TOTAL');
        $response->assertSee('DISETUJUI');
        $response->assertSee('Demam dan konsultasi dokter');
    }

    public function test_lembar_nilai_magang_pilar_4_menampilkan_halaman(): void
    {
        $response = $this->actingAs($this->userMagang)->get(route('magang.penilaian.index'));

        $response->assertStatus(200);
        $response->assertSee('Lembar Nilai Magang');
    }
}
