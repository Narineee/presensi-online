<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pekerjaan;
use App\Models\Pembimbing;
use App\Models\PengajuanTugasLuar;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Services\PresensiScoreService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TugasLuarTest extends TestCase
{
    use RefreshDatabase;

    private array $fakeFaceDescriptor;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->fakeFaceDescriptor = array_fill(0, 128, 0.1);
    }

    private function createValidBase64Image(): string
    {
        $im = imagecreatetruecolor(10, 10);
        ob_start();
        imagejpeg($im);
        $binary = ob_get_clean();
        imagedestroy($im);

        return 'data:image/jpeg;base64,'.base64_encode($binary);
    }

    private function createPembimbingWithUser(string $username = 'pembimbing1'): array
    {
        $user = Pengguna::create([
            'username' => $username,
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $user->id,
            'nip' => '19800101'.rand(1000, 9999),
            'nama_lengkap' => 'Pembimbing '.ucfirst($username),
            'jabatan' => 'Pembimbing Lapangan',
            'no_hp' => '08123456789',
        ]);

        return [$user, $pembimbing];
    }

    private function createInternWithPembimbing(Pembimbing $pembimbing, string $name = 'Budi Intern', string $username = 'budi_intern'): array
    {
        $divisi = Divisi::firstOrCreate(
            ['nama_divisi' => 'Teknologi Informasi'],
            [
                'nama_pimpinan' => 'Kadiv IT',
                'nip_pimpinan' => '197501012000031001',
                'jabatan_pimpinan' => 'Kadiv IT',
                'latitude' => -3.4893886,
                'longitude' => 114.8252584,
                'radius_meter' => 40,
            ]
        );

        $user = Pengguna::create([
            'username' => $username,
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM'.rand(1000, 9999),
            'nama_lengkap' => $name,
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
            'face_descriptors' => [$this->fakeFaceDescriptor],
            'face_registered_at' => now(),
        ]);

        return [$user, $magang];
    }

    public function test_magang_can_render_presensi_index_page_with_tugas_luar_components(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser();
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing);

        $response = $this->actingAs($internUser)->get(route('presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Tugas Luar (TL)');
        $response->assertSee('container-tugas-luar');
        $response->assertSee('modal-ajukan-tugas-luar');
    }

    public function test_skenario_1_presensi_masuk_tugas_luar_bypasses_office_radius_and_creates_pending_pengajuan(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser();
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing);

        Carbon::setTestNow(Carbon::parse('2026-10-02 08:15:00', 'Asia/Makassar'));
        $today = '2026-10-02';

        $fileBukti = UploadedFile::fake()->create('surat_tugas.pdf', 500, 'application/pdf');

        // Lokasi GPS berada 10 km dari kantor (-3.550000, 114.900000)
        $response = $this->actingAs($internUser)
            ->post(route('presensi.masuk'), [
                'mode_kerja' => 'tugas_luar',
                'lokasi_masuk' => '-3.550000, 114.900000',
                'foto_masuk' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
                'tujuan' => 'Dinas Kominfo Provinsi Kalsel',
                'keperluan' => 'Koordinasi integrasi API Presensi',
                'waktu_mulai' => '08:30',
                'waktu_selesai' => '15:00',
                'bukti_tugas_luar' => $fileBukti,
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        // Presensi tetap berstatus hadir dengan mode tugas_luar
        $presensi = Presensi::where('pengguna_id', $internUser->id)->first();
        $this->assertNotNull($presensi);
        $this->assertEquals('tugas_luar', $presensi->mode_kerja);
        $this->assertEquals('hadir', $presensi->status);
        $this->assertEquals($today, Carbon::parse($presensi->tanggal)->toDateString());

        // Pengajuan Tugas Luar tercatat dengan status_verifikasi menunggu
        $this->assertDatabaseHas('pengajuan_tugas_luar', [
            'presensi_id' => $presensi->id,
            'pengguna_id' => $internUser->id,
            'magang_id' => $intern->id,
            'tujuan' => 'Dinas Kominfo Provinsi Kalsel',
            'status_verifikasi' => 'menunggu',
        ]);

        $pengajuan = PengajuanTugasLuar::where('presensi_id', $presensi->id)->first();
        $this->assertNotNull($pengajuan);
        $this->assertNotNull($pengajuan->bukti);
        Storage::disk('public')->assertExists($pengajuan->bukti);
    }

    public function test_skenario_2_pengajuan_tugas_luar_after_onsite_presensi_retains_original_checkin_time(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser();
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing);

        Carbon::setTestNow(Carbon::parse('2026-10-02 07:45:00', 'Asia/Makassar'));
        $today = '2026-10-02';

        // 1. Presensi Onsite di pagi hari
        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
            'lokasi_masuk' => '-3.4893886, 114.8252584',
            'keterangan' => 'Tepat waktu',
        ]);

        // 2. Di siang hari (10:00), peserta mengajukan Tugas Luar
        Carbon::setTestNow(Carbon::parse('2026-10-02 10:00:00', 'Asia/Makassar'));

        $response = $this->actingAs($internUser)
            ->post(route('presensi.tugas-luar'), [
                'tujuan' => 'Pengadilan Negeri Banjarbaru',
                'keperluan' => 'Menghadiri sidang sebagai saksi ahli magang',
                'waktu_mulai' => '10:30',
                'waktu_selesai' => '14:30',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        // Jam masuk awal dan mode kerja onsite TIDAK BERUBAH
        $presensi->refresh();
        $this->assertEquals('07:45:00', $presensi->jam_masuk);
        $this->assertEquals('onsite', $presensi->mode_kerja);

        // Pengajuan Tugas Luar tercatat dengan status menunggu
        $this->assertDatabaseHas('pengajuan_tugas_luar', [
            'presensi_id' => $presensi->id,
            'pengguna_id' => $internUser->id,
            'tujuan' => 'Pengadilan Negeri Banjarbaru',
            'status_verifikasi' => 'menunggu',
        ]);
    }

    public function test_presensi_pulang_bypasses_office_radius_when_intern_has_tugas_luar(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser();
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing);

        Carbon::setTestNow(Carbon::parse('2026-10-02 16:30:00', 'Asia/Makassar'));
        $today = '2026-10-02';

        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        // Peserta memiliki Tugas Luar aktif
        PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $internUser->id,
            'magang_id' => $intern->id,
            'tanggal' => $today,
            'tujuan' => 'Lokasi Penugasan Lapangan',
            'keperluan' => 'Inspeksi lapangan dinas',
            'waktu_mulai' => '09:00',
            'status_verifikasi' => 'disetujui',
        ]);

        // Buat pekerjaan aktif
        $pekerjaan = Pekerjaan::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $pembimbing->id,
            'judul' => 'Pengembangan Sistem',
            'deskripsi' => 'Tugas magang',
            'pemberi_tugas' => 'Kadiv IT',
            'tanggal_mulai' => '2026-10-01',
            'status' => 'aktif',
            'jenis' => 'rutin',
        ]);

        // Syarat aktivitas terpenuhi
        Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'pekerjaan_id' => $pekerjaan->id,
            'tanggal' => $today,
            'isi' => 'Menyelesaikan inspeksi penugasan luar.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        // Pulang dari lokasi berjarak 20 km dari kantor (harus berhasil tanpa terhalang radius)
        $response = $this->actingAs($internUser)
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.600000, 114.950000',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
                'keterangan_keluar' => 'Selesai tugas luar',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertNotNull($presensi->jam_keluar);
        $this->assertStringContainsString('Selesai tugas luar', $presensi->keterangan);
    }

    public function test_pembimbing_can_approve_and_reject_tugas_luar(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser();
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing);

        $today = '2026-10-02';
        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'mode_kerja' => 'tugas_luar',
            'status' => 'hadir',
        ]);

        $pengajuan = PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $internUser->id,
            'magang_id' => $intern->id,
            'tanggal' => $today,
            'tujuan' => 'Kantor Gubernur',
            'keperluan' => 'Audiensi kegiatan magang',
            'waktu_mulai' => '09:00',
            'status_verifikasi' => 'menunggu',
        ]);

        // Pembimbing melihat daftar pengajuan
        $indexResponse = $this->actingAs($pembimbingUser)->get(route('pembimbing.tugas-luar.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Kantor Gubernur');
        $indexResponse->assertSee($intern->nama_lengkap);

        // Pembimbing menyetujui pengajuan
        $setujuiResponse = $this->actingAs($pembimbingUser)
            ->post(route('pembimbing.tugas-luar.validasi', $pengajuan->id), [
                'status_verifikasi' => 'disetujui',
            ]);

        $setujuiResponse->assertRedirect();
        $setujuiResponse->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->status_verifikasi);
        $this->assertEquals($pembimbingUser->id, $pengajuan->verified_by);
        $this->assertNotNull($pengajuan->verified_at);

        $presensi->refresh();
        $this->assertTrue($presensi->is_tugas_luar);

        // Sekarang coba skenario penolakan pada pengajuan baru
        $pengajuan2 = PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $internUser->id,
            'magang_id' => $intern->id,
            'tanggal' => '2026-10-03',
            'tujuan' => 'Lokasi Tidak Resmi',
            'keperluan' => 'Keperluan pribadi',
            'waktu_mulai' => '10:00',
            'status_verifikasi' => 'menunggu',
        ]);

        $tolakResponse = $this->actingAs($pembimbingUser)
            ->post(route('pembimbing.tugas-luar.validasi', $pengajuan2->id), [
                'status_verifikasi' => 'ditolak',
                'catatan_pembimbing' => 'Penugasan tidak sesuai dengan program magang.',
            ]);

        $tolakResponse->assertRedirect();
        $tolakResponse->assertSessionHas('success');

        $pengajuan2->refresh();
        $this->assertEquals('ditolak', $pengajuan2->status_verifikasi);
        $this->assertEquals('Penugasan tidak sesuai dengan program magang.', $pengajuan2->catatan_pembimbing);
    }

    public function test_admin_can_monitor_all_tugas_luar(): void
    {
        $adminUser = Pengguna::create([
            'username' => 'admin_super',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser();
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing);

        $pengajuan = PengajuanTugasLuar::create([
            'pengguna_id' => $internUser->id,
            'magang_id' => $intern->id,
            'tanggal' => '2026-10-02',
            'tujuan' => 'Kantor Arsip Daerah',
            'keperluan' => 'Digitalisasi berkas dinas',
            'waktu_mulai' => '08:30',
            'status_verifikasi' => 'menunggu',
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.tugas-luar.index'));
        $response->assertStatus(200);
        $response->assertSee('Kantor Arsip Daerah');
        $response->assertSee('Digitalisasi berkas dinas');
        $response->assertSee($intern->nama_lengkap);
    }

    public function test_presensi_score_service_counts_approved_tugas_luar_as_full_presence(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser();
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing);

        $intern->update([
            'tanggal_mulai' => '2026-10-02',
            'tanggal_selesai' => '2026-10-02',
        ]);

        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => '2026-10-02',
            'jam_masuk' => '08:00:00',
            'mode_kerja' => 'tugas_luar',
            'status' => 'hadir',
        ]);

        PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $internUser->id,
            'magang_id' => $intern->id,
            'tanggal' => '2026-10-02',
            'tujuan' => 'Studi Lapangan',
            'keperluan' => 'Riset implementasi',
            'waktu_mulai' => '08:00',
            'status_verifikasi' => 'disetujui',
        ]);

        $service = new PresensiScoreService;
        $scoreResult = $service->calculateScore($intern);

        $this->assertEquals(480, $scoreResult['target_menit']);
        $this->assertEquals(480, $scoreResult['total_menit_realisasi']);
        $this->assertEquals(100.0, $scoreResult['skor_presensi']);
        $this->assertEquals(100, $scoreResult['nilai_angka']);

        $rincian = $scoreResult['rincian_harian'][0] ?? null;
        $this->assertNotNull($rincian);
        $this->assertEquals('hadir', $rincian['status']);
        $this->assertEquals(480, $rincian['menit']);
        $this->assertStringContainsString('Hadir — Tugas Luar', $rincian['keterangan']);
    }
}
