<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanTugasLuar;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Services\PresensiScoreService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPresensiTugasLuarTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(): array
    {
        $divisi = Divisi::create([
            'nama_divisi' => 'Teknologi Informasi',
            'nama_pimpinan' => 'Kadiv IT',
            'nip_pimpinan' => '197501012000031001',
            'jabatan_pimpinan' => 'Kadiv IT',
            'latitude' => -3.4893886,
            'longitude' => 114.8252584,
            'radius_meter' => 50,
        ]);

        $pembimbingUser = Pengguna::create([
            'username' => 'mentor_tl',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $pembimbingUser->id,
            'nip' => '198001012005011001',
            'nama_lengkap' => 'Pak Pembimbing TL',
            'jabatan' => 'Pembimbing Lapangan',
            'divisi_id' => $divisi->id,
        ]);

        $internUser = Pengguna::create([
            'username' => 'intern_tl',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $internUser->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => '2026999',
            'nama_lengkap' => 'Budi Tugas Luar',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-31',
            'status' => 'aktif',
            'face_registered_at' => now(),
        ]);

        return compact('divisi', 'pembimbingUser', 'pembimbing', 'internUser', 'magang');
    }

    public function test_riwayat_presensi_magang_shows_waktu_pulang_for_tugas_luar(): void
    {
        $data = $this->setupData();
        $date = '2026-10-14';

        $presensi = Presensi::create([
            'pengguna_id' => $data['internUser']->id,
            'tanggal' => $date,
            'jam_masuk' => '07:45:00',
            'jam_keluar' => null, // Belum/tidak presensi keluar manual
            'mode_kerja' => 'tugas_luar',
            'status' => 'hadir',
            'keterangan' => 'Tugas Luar Dinas Kominfo',
        ]);

        PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $data['internUser']->id,
            'magang_id' => $data['magang']->id,
            'tanggal' => $date,
            'tujuan' => 'Kantor Gubernur',
            'keperluan' => 'Rapat koordinasi',
            'status_verifikasi' => 'disetujui',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '16:00:00',
        ]);

        $response = $this->actingAs($data['internUser'])->get(route('presensi.riwayat'));
        $response->assertOk();
        $response->assertSee('08:00 (TL) WITA');
        $response->assertSee('Tugas Luar');
    }

    public function test_riwayat_presensi_pembimbing_binaan_shows_jam_pulang_for_tugas_luar(): void
    {
        $data = $this->setupData();
        $date = '2026-10-14';

        $presensi = Presensi::create([
            'pengguna_id' => $data['internUser']->id,
            'tanggal' => $date,
            'jam_masuk' => '08:00:00',
            'jam_keluar' => null,
            'mode_kerja' => 'tugas_luar',
            'status' => 'hadir',
        ]);

        PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $data['internUser']->id,
            'magang_id' => $data['magang']->id,
            'tanggal' => $date,
            'tujuan' => 'Diskominfo Banjarbaru',
            'keperluan' => 'Pemasangan jaringan',
            'status_verifikasi' => 'disetujui',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '16:00:00',
        ]);

        $response = $this->actingAs($data['pembimbingUser'])->get(route('pembimbing.binaan.riwayat', $data['magang']->id));
        $response->assertOk();
        $response->assertSee('16.00');
        $response->assertSee('TUGAS LUAR');
    }

    public function test_pembimbing_validasi_tugas_luar_sets_jam_keluar_automatically(): void
    {
        $data = $this->setupData();
        $date = '2026-10-15';

        $presensi = Presensi::create([
            'pengguna_id' => $data['internUser']->id,
            'tanggal' => $date,
            'jam_masuk' => '07:50:00',
            'jam_keluar' => null,
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        $pengajuan = PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $data['internUser']->id,
            'magang_id' => $data['magang']->id,
            'tanggal' => $date,
            'tujuan' => 'Workshop IT Pemprov',
            'keperluan' => 'Mengikuti sosialisasi',
            'status_verifikasi' => 'menunggu',
            'waktu_mulai' => '08:30:00',
            'waktu_selesai' => '16:00:00',
        ]);

        $res = $this->actingAs($data['pembimbingUser'])
            ->post(route('pembimbing.tugas-luar.validasi', $pengajuan->id), [
                'status_verifikasi' => 'disetujui',
                'catatan_pembimbing' => 'Silakan bertugas.',
            ]);

        $res->assertRedirect();

        $presensi->refresh();
        $this->assertEquals('08:30:00', $presensi->jam_keluar);
        $this->assertEquals('hadir', $presensi->status);
    }

    public function test_jam_kerja_for_tugas_luar_remains_counted_until_1600_with_full_480_minutes(): void
    {
        $data = $this->setupData();
        $date = '2026-10-14';

        $presensi = Presensi::create([
            'pengguna_id' => $data['internUser']->id,
            'tanggal' => $date,
            'jam_masuk' => '07:30:00',
            'jam_keluar' => '17:30:00', // Even if checkout / end time is 17:30
            'mode_kerja' => 'tugas_luar',
            'status' => 'hadir',
        ]);

        PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $data['internUser']->id,
            'magang_id' => $data['magang']->id,
            'tanggal' => $date,
            'tujuan' => 'Studi Banding Luar Kota',
            'keperluan' => 'Kunjungan kerja',
            'status_verifikasi' => 'disetujui',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '17:30:00',
        ]);

        $scoreService = new PresensiScoreService;
        $score = $scoreService->calculateScore($data['magang']);

        // Cari rincian harian tanggal 2026-10-14
        $rincian = collect($score['rincian_harian'])->firstWhere('tanggal', $date);
        $this->assertNotNull($rincian);
        $this->assertEquals('hadir', $rincian['status']);
        // Jam kerja tetap terhitung sampai jam 16:00 (maksimal 480 menit / 8 jam per hari)
        $this->assertEquals(480, $rincian['menit']);
        $this->assertEquals('17:30', $rincian['jam_keluar']);
    }

    public function test_dashboard_dan_cetak_presensi_shows_waktu_pulang_for_tugas_luar(): void
    {
        $data = $this->setupData();
        $today = Carbon::today()->toDateString();

        $presensi = Presensi::create([
            'pengguna_id' => $data['internUser']->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'jam_keluar' => null,
            'mode_kerja' => 'tugas_luar',
            'status' => 'hadir',
        ]);

        PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $data['internUser']->id,
            'magang_id' => $data['magang']->id,
            'tanggal' => $today,
            'tujuan' => 'Kantor Badan Kepegawaian',
            'keperluan' => 'Pengantaran berkas',
            'status_verifikasi' => 'disetujui',
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '16:00:00',
        ]);

        // Dashboard magang
        $dashResponse = $this->actingAs($data['internUser'])->get(route('presensi.index'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('08:00 (TL)');

        // Cetak dokumen HTML
        $cetakResponse = $this->actingAs($data['internUser'])->get(route('presensi.cetak', ['format' => 'html']));
        $cetakResponse->assertOk();
        $cetakResponse->assertSee('08:00 (TL) WITA');
    }

    public function test_tugas_luar_terlambat_masuk_diakumulasi_sesuai_jam_efektif_dikurangi_keterlambatan(): void
    {
        $data = $this->setupData();
        $date = '2026-10-14';

        // Pengguna masuk / mengajukan tugas luar pada pukul 09:12
        $presensi = Presensi::create([
            'pengguna_id' => $data['internUser']->id,
            'tanggal' => $date,
            'jam_masuk' => '09:12:00',
            'jam_keluar' => null,
            'mode_kerja' => 'tugas_luar',
            'status' => 'hadir',
            'keterangan' => 'Tugas Luar Dinas Sosial [Terlambat 72 menit]',
        ]);

        PengajuanTugasLuar::create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $data['internUser']->id,
            'magang_id' => $data['magang']->id,
            'tanggal' => $date,
            'tujuan' => 'Dinas Sosial',
            'keperluan' => 'Pemasukan bantuan',
            'status_verifikasi' => 'disetujui',
            'waktu_mulai' => '09:12:00',
            'waktu_selesai' => '16:00:00',
        ]);

        // Cek tampilan riwayat presensi: menampilkan jam 09:12 dan 16:00 (TL)
        $responseRiwayat = $this->actingAs($data['internUser'])->get(route('presensi.riwayat'));
        $responseRiwayat->assertOk();
        $responseRiwayat->assertSee('09:12');
        $responseRiwayat->assertSee('16:00 (TL)');
        $responseRiwayat->assertSee('Terlambat');

        // Cek perhitungan akumulasi presensi
        $scoreService = new PresensiScoreService;
        $score = $scoreService->calculateScore($data['magang']);

        $rincian = collect($score['rincian_harian'])->firstWhere('tanggal', $date);
        $this->assertNotNull($rincian);
        $this->assertEquals('hadir', $rincian['status']);
        $this->assertEquals('09:12', $rincian['jam_masuk']);
        $this->assertEquals('16:00', $rincian['jam_keluar']);

        // Masuk 09:12 -> terlambat 72 menit dari 08:00
        // Realisasi menit kerja = 480 - 72 = 408 menit (tugas luar dianggap tetap bekerja sampai 16:00)
        $this->assertEquals(408, $rincian['menit']);
        $this->assertStringContainsString('Terlambat 72 Menit', $rincian['keterangan']);

        // Akumulasi total keterlambatan
        $this->assertEquals(72, $score['menit_terlambat_potong']);
        $this->assertEquals(1, $score['total_hari_terlambat']);
    }

    public function test_pengajuan_tugas_luar_setelah_absen_masuk_menampilkan_jam_pengajuan_tl_dan_dihitung_full_8_jam(): void
    {
        Carbon::setTestNow('2026-10-14 09:30:00');
        $data = $this->setupData();
        $date = Carbon::today()->toDateString();

        // 1. Peserta absen masuk pukul 08:00
        $presensi = Presensi::create([
            'pengguna_id' => $data['internUser']->id,
            'tanggal' => $date,
            'jam_masuk' => '08:00:00',
            'jam_keluar' => null,
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        // 2. Peserta mengajukan tugas luar pukul 09:30 lewat form/controller
        $res = $this->actingAs($data['internUser'])->post(route('presensi.tugas-luar'), [
            'tujuan' => 'Dinas Pendidikan',
            'keperluan' => 'Sosialisasi aplikasi',
            'tanggal_mulai' => $date,
            'tanggal_selesai' => $date,
            'waktu_mulai' => '09:30:00',
            'waktu_selesai' => '16:00:00',
        ]);
        $res->assertRedirect(route('presensi.index'));

        $presensi->refresh();
        $this->assertEquals('09:30:00', $presensi->jam_keluar);
        $this->assertEquals('TL', $presensi->catatan_pulang);
        $this->assertEquals('09:30 (TL)', $presensi->formatJamKeluar(':'));

        // Cek dashboard magang
        $dash = $this->actingAs($data['internUser'])->get(route('presensi.index'));
        $dash->assertOk();
        $dash->assertSee('09:30 (TL)');

        // Cek riwayat presensi magang
        $riwayat = $this->actingAs($data['internUser'])->get(route('presensi.riwayat'));
        $riwayat->assertOk();
        $riwayat->assertSee('09:30 (TL)');

        // Cek cetak presensi
        $cetak = $this->actingAs($data['internUser'])->get(route('presensi.cetak', ['format' => 'html']));
        $cetak->assertOk();
        $cetak->assertSee('09:30 (TL) WITA');

        // Setujui tugas luar oleh pembimbing
        $tl = PengajuanTugasLuar::where('presensi_id', $presensi->id)->first();
        $this->actingAs($data['pembimbingUser'])->post(route('pembimbing.tugas-luar.validasi', $tl->id), [
            'status_verifikasi' => 'disetujui',
            'catatan_pembimbing' => 'OK',
        ]);

        $presensi->refresh();
        $this->assertEquals('09:30:00', $presensi->jam_keluar);

        // Akumulasi nilai akhir tetap dihitung full 8 jam (480 menit) karena masuk tepat waktu 08:00
        $scoreService = new PresensiScoreService;
        $score = $scoreService->calculateScore($data['magang']);
        $rincian = collect($score['rincian_harian'])->firstWhere('tanggal', $date);

        $this->assertNotNull($rincian);
        $this->assertEquals(480, $rincian['menit']);
        $this->assertEquals('hadir', $rincian['status']);
    }

    public function test_pulang_awal_sakit_atau_izin_menampilkan_label_sakit_atau_izin_dan_mengurangi_menit_kerja(): void
    {
        Carbon::setTestNow('2026-10-14 09:30:00');
        $data = $this->setupData();
        $date = Carbon::today()->toDateString();

        // 1. Peserta presensi masuk jam 08:00
        $presensi = Presensi::create([
            'pengguna_id' => $data['internUser']->id,
            'tanggal' => $date,
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '09:30:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
            'keterangan' => 'Pulang: Sakit (demam)',
        ]);

        $this->assertEquals('sakit', $presensi->catatan_pulang);
        $this->assertEquals('09:30 (sakit)', $presensi->formatJamKeluar(':'));

        // Cek dashboard magang
        $dash = $this->actingAs($data['internUser'])->get(route('presensi.index'));
        $dash->assertOk();
        $dash->assertSee('09:30 (sakit)');

        // Cek riwayat presensi magang
        $riwayat = $this->actingAs($data['internUser'])->get(route('presensi.riwayat'));
        $riwayat->assertOk();
        $riwayat->assertSee('09:30 (sakit)');

        // Cek cetak presensi
        $cetak = $this->actingAs($data['internUser'])->get(route('presensi.cetak', ['format' => 'html']));
        $cetak->assertOk();
        $cetak->assertSee('09:30 (sakit) WITA');

        // Monitoring pembimbing
        $mon = $this->actingAs($data['pembimbingUser'])->get(route('pembimbing.presensi.index'));
        $mon->assertOk();
        $mon->assertSee('09:30');
        $mon->assertSee('sakit');

        // Akumulasi nilai: bukan tugas luar, jadi pulang lebih awal mengurangi menit (08:00 s.d 09:30 = 90 menit)
        $scoreService = new PresensiScoreService;
        $score = $scoreService->calculateScore($data['magang']);
        $rincian = collect($score['rincian_harian'])->firstWhere('tanggal', $date);

        $this->assertNotNull($rincian);
        $this->assertEquals(90, $rincian['menit']);
        $this->assertStringContainsString('Pulang Lebih Awal', $rincian['keterangan']);
    }
}
