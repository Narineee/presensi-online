<?php

namespace Tests\Feature;

use App\Models\DetailPenilaian;
use App\Models\HariLibur;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Penilaian;
use App\Models\Presensi;
use App\Services\PresensiScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiScoreServiceTest extends TestCase
{
    use RefreshDatabase;

    private PresensiScoreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PresensiScoreService::class);
    }

    public function test_normal_attendance_earns_four_hundred_eighty_minutes(): void
    {
        // Setup peserta magang 1 hari kerja (Senin, 2026-08-03)
        $user = Pengguna::create([
            'username' => 'magang1',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'nama_lengkap' => 'Budi Santoso',
            'tanggal_mulai' => '2026-08-03', // Senin
            'tanggal_selesai' => '2026-08-03', // Senin
            'status' => 'aktif',
        ]);

        // Presensi normal: 08:00 - 16:00
        Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => '2026-08-03',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
        ]);

        $score = $this->service->calculateScore($magang);

        $this->assertEquals(1, $score['target_hari']);
        $this->assertEquals(480, $score['target_menit']);
        $this->assertEquals(480, $score['total_menit_realisasi']);
        $this->assertEquals(100.0, $score['skor_presensi']);
        $this->assertEquals(100, $score['nilai_angka']);
        $this->assertEquals('Sangat Baik', $score['predikat']);
    }

    public function test_early_clock_in_is_capped_at_eight_am_and_late_clock_out_is_capped_at_four_pm(): void
    {
        $user = Pengguna::create([
            'username' => 'magang2',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'nama_lengkap' => 'Siti Nurhaliza',
            'tanggal_mulai' => '2026-08-03', // Senin
            'tanggal_selesai' => '2026-08-03', // Senin
            'status' => 'aktif',
        ]);

        // Datang lebih awal (07:30) dan pulang lebih lambat (17:30)
        Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => '2026-08-03',
            'jam_masuk' => '07:30:00',
            'jam_keluar' => '17:30:00',
            'status' => 'hadir',
        ]);

        $score = $this->service->calculateScore($magang);

        // Menit tidak bertambah lebih dari 480 menit
        $this->assertEquals(480, $score['total_menit_realisasi']);
        $this->assertEquals(100.0, $score['skor_presensi']);
    }

    public function test_late_arrival_reduces_minutes(): void
    {
        $user = Pengguna::create([
            'username' => 'magang3',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'nama_lengkap' => 'Doni Kusuma',
            'tanggal_mulai' => '2026-08-03', // Senin
            'tanggal_selesai' => '2026-08-03', // Senin
            'status' => 'aktif',
        ]);

        // Terlambat 30 menit: masuk 08:30, pulang 16:00
        Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => '2026-08-03',
            'jam_masuk' => '08:30:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
        ]);

        $score = $this->service->calculateScore($magang);

        // 480 - 30 = 450 menit
        $this->assertEquals(450, $score['total_menit_realisasi']);
        $this->assertEquals(30, $score['menit_terlambat_potong']);
        $this->assertEquals(93.75, $score['skor_presensi']);
        $this->assertEquals(94, $score['nilai_angka']);
        $this->assertEquals('Sangat Baik', $score['predikat']);
    }

    public function test_forgot_clock_out_deducts_fifty_percent(): void
    {
        $user = Pengguna::create([
            'username' => 'magang4',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'nama_lengkap' => 'Rina Amalia',
            'tanggal_mulai' => '2026-08-03', // Senin
            'tanggal_selesai' => '2026-08-03', // Senin
            'status' => 'aktif',
        ]);

        // Masuk tepat waktu 08:00, tetapi lupa presensi keluar (jam_keluar null)
        Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => '2026-08-03',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => null,
            'status' => 'hadir',
        ]);

        $score = $this->service->calculateScore($magang);

        // Dipotong 50% dari 480 = 240 menit
        $this->assertEquals(240, $score['total_menit_realisasi']);
        $this->assertEquals(1, $score['total_hari_lupa_checkout']);
        $this->assertEquals(50.0, $score['skor_presensi']);
        $this->assertEquals(50, $score['nilai_angka']);
        $this->assertEquals('Tidak Baik', $score['predikat']);
    }

    public function test_approved_sick_or_leave_counts_as_full_four_hundred_eighty_minutes(): void
    {
        $user = Pengguna::create([
            'username' => 'magang5',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'nama_lengkap' => 'Fajar Pratama',
            'tanggal_mulai' => '2026-08-03', // Senin
            'tanggal_selesai' => '2026-08-03', // Senin
            'status' => 'aktif',
        ]);

        // Pengajuan izin resmi yang disetujui pembimbing
        PengajuanIzin::create([
            'pengguna_id' => $user->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-08-03',
            'tanggal_selesai' => '2026-08-03',
            'alasan' => 'Demam berdarah',
            'status_approval' => 'disetujui',
        ]);

        $score = $this->service->calculateScore($magang);

        // Izin resmi dihitung hadir penuh = 480 menit
        $this->assertEquals(480, $score['total_menit_realisasi']);
        $this->assertEquals(1, $score['total_hari_izin']);
        $this->assertEquals(100.0, $score['skor_presensi']);
        $this->assertEquals('Sangat Baik', $score['predikat']);
    }

    public function test_national_holiday_and_weekend_do_not_penalize_intern(): void
    {
        $user = Pengguna::create([
            'username' => 'magang6',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        // Periode: 2026-08-15 (Sabtu) s/d 2026-08-18 (Selasa)
        // 15 = Sabtu (Weekend)
        // 16 = Minggu (Weekend)
        // 17 = Senin (Hari Kemerdekaan RI - Tanggal Merah)
        // 18 = Selasa (Hari Kerja Normal)
        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'nama_lengkap' => 'Dewi Sartika',
            'tanggal_mulai' => '2026-08-15',
            'tanggal_selesai' => '2026-08-18',
            'status' => 'aktif',
        ]);

        // Simpan hari libur nasional 17 Agustus 2026
        HariLibur::firstOrCreate(
            ['tanggal' => '2026-08-17'],
            ['keterangan' => 'Hari Kemerdekaan RI ke-81']
        );

        // Peserta hadir normal hanya di hari kerja aktif (18 Agustus)
        Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => '2026-08-18',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
        ]);

        $score = $this->service->calculateScore($magang);

        // Target hari kerja hanya 1 hari (18 Agustus), karena 15-16 weekend dan 17 libur nasional
        $this->assertEquals(1, $score['target_hari']);
        $this->assertEquals(480, $score['target_menit']);
        $this->assertEquals(480, $score['total_menit_realisasi']);
        $this->assertEquals(1, $score['total_hari_libur_nasional']);
        $this->assertEquals(100.0, $score['skor_presensi']);
        $this->assertEquals('Sangat Baik', $score['predikat']);
    }

    public function test_penilaian_store_automatically_applies_objective_presensi_score(): void
    {
        // Setup kriteria
        $kriteriaPresensi = KriteriaPenilaian::create([
            'nama' => 'Kedisiplinan & Presensi',
            'bobot' => 30,
            'is_presensi' => true,
        ]);

        $kriteriaTeknis = KriteriaPenilaian::create([
            'nama' => 'Kualitas Teknis',
            'bobot' => 70,
            'is_presensi' => false,
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing1',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Pak Pembimbing',
        ]);

        $userMagang = Pengguna::create([
            'username' => 'magang7',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $pembimbing->id,
            'nama_lengkap' => 'Anak Magang 7',
            'tanggal_mulai' => '2026-08-03', // Senin
            'tanggal_selesai' => '2026-08-03', // Senin
            'status' => 'aktif',
        ]);

        // Hadir normal (skor 100)
        Presensi::create([
            'pengguna_id' => $userMagang->id,
            'tanggal' => '2026-08-03',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
        ]);

        // Pembimbing menginput nilai (misal pembimbing mencoba mengirim 50 untuk presensi dan 80 untuk teknis)
        $response = $this->actingAs($userPembimbing)->post(route('pembimbing.penilaian.store'), [
            'magang_id' => $magang->id,
            'nilai' => [
                $kriteriaPresensi->id => 50, // Akan di-override oleh skor objektif presensi (100)
                $kriteriaTeknis->id => 80,
            ],
            'status_magang' => 'selesai',
        ]);

        $response->assertSessionHasNoErrors();

        // Nilai berbobot: (100 * 30 + 80 * 70) / 100 = (3000 + 5600) / 100 = 86
        $penilaian = Penilaian::where('magang_id', $magang->id)->first();
        $this->assertNotNull($penilaian);
        $this->assertEquals(86, $penilaian->total_nilai);

        // Detail kriteria presensi harus tersimpan dengan nilai objektif (100), bukan nilai input (50)
        $detailPresensi = DetailPenilaian::where('penilaian_id', $penilaian->id)
            ->where('kriteria_id', $kriteriaPresensi->id)
            ->first();

        $this->assertEquals(100, $detailPresensi->nilai);
    }

    public function test_penilaian_update_also_enforces_objective_presensi_score(): void
    {
        $kriteriaPresensi = KriteriaPenilaian::create([
            'nama' => 'Kedisiplinan',
            'bobot' => 40,
            'is_presensi' => true,
        ]);

        $kriteriaLain = KriteriaPenilaian::create([
            'nama' => 'Inisiatif',
            'bobot' => 60,
            'is_presensi' => false,
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing2',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198501012010011002',
            'nama_lengkap' => 'Pak Pembimbing 2',
        ]);

        $userMagang = Pengguna::create([
            'username' => 'magang8',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $pembimbing->id,
            'nama_lengkap' => 'Anak Magang 8',
            'tanggal_mulai' => '2026-08-03', // Senin
            'tanggal_selesai' => '2026-08-03', // Senin
            'status' => 'aktif',
        ]);

        // Masuk tepat waktu tapi lupa presensi keluar (50% = 240 menit -> skor 50)
        Presensi::create([
            'pengguna_id' => $userMagang->id,
            'tanggal' => '2026-08-03',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => null,
            'status' => 'hadir',
        ]);

        $penilaian = Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $pembimbing->id,
            'total_nilai' => 50,
        ]);

        DetailPenilaian::create([
            'penilaian_id' => $penilaian->id,
            'kriteria_id' => $kriteriaPresensi->id,
            'nilai' => 50,
        ]);

        DetailPenilaian::create([
            'penilaian_id' => $penilaian->id,
            'kriteria_id' => $kriteriaLain->id,
            'nilai' => 50,
        ]);

        // Pembimbing update form nilai, coba mengisi kriteria presensi dengan 99
        $response = $this->actingAs($userPembimbing)->put(route('pembimbing.penilaian.update', $penilaian->id), [
            'nilai' => [
                $kriteriaPresensi->id => 99, // Di-override menjadi 50 (objektif)
                $kriteriaLain->id => 90,
            ],
            'status_magang' => 'selesai',
        ]);

        $response->assertSessionHasNoErrors();

        // Total nilai terhitung: (50 * 40 + 90 * 60) / 100 = (2000 + 5400) / 100 = 74
        $penilaian->refresh();
        $this->assertEquals(74, $penilaian->total_nilai);

        $detailPresensi = DetailPenilaian::where('penilaian_id', $penilaian->id)
            ->where('kriteria_id', $kriteriaPresensi->id)
            ->first();

        $this->assertEquals(50, $detailPresensi->nilai);
    }
}
