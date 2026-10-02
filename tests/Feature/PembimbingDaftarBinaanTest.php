<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\HariLibur;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembimbingDaftarBinaanTest extends TestCase
{
    use RefreshDatabase;

    private function createPembimbingWithUser(string $username = 'pembimbing_test'): array
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
            'jabatan' => 'Pranata Komputer',
            'no_hp' => '08123456789',
        ]);

        return [$user, $pembimbing];
    }

    private function createIntern(Pembimbing $pembimbing, string $name, string $username, array $attributes = []): array
    {
        $divisi = Divisi::firstOrCreate(
            ['nama_divisi' => 'Bidang Komunikasi Publik'],
            [
                'nama_pimpinan' => 'Kepala Bidang',
                'nip_pimpinan' => '197501012000031001',
                'jabatan_pimpinan' => 'Kabid',
                'latitude' => -3.489,
                'longitude' => 114.825,
                'radius_meter' => 50,
            ]
        );

        $user = Pengguna::create([
            'username' => $username,
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create(array_merge([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM'.rand(1000, 9999),
            'nama_lengkap' => $name,
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat',
            'jurusan' => 'Teknik Informatika',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
        ], $attributes));

        return [$user, $magang];
    }

    public function test_pembimbing_bisa_mengakses_halaman_daftar_binaan(): void
    {
        [$userPembimbing, $pembimbing] = $this->createPembimbingWithUser('pembimbing1');
        $this->createIntern($pembimbing, 'Peserta Magang A', 'magang_a');

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Peserta Binaan');
        $response->assertSee('Peserta Magang A');
        $response->assertSee('Total Hari Kerja');
        $response->assertSee('Hadir');
        $response->assertSee('Sakit');
        $response->assertSee('Izin');
        $response->assertSee('Cuti');
        $response->assertSee('Tugas Luar');
        $response->assertSee('Alpa');
    }

    public function test_total_hari_magang_hanya_menghitung_hari_kerja_dan_libur_nasional_tidak_dihitung(): void
    {
        [$userPembimbing, $pembimbing] = $this->createPembimbingWithUser('pembimbing2');

        // Rentang 1 pekan: Senin 7 September 2026 s/d Jumat 11 September 2026 (5 hari kalender Senin-Jumat)
        // Tambahkan 1 hari libur nasional pada hari Rabu 9 September 2026
        HariLibur::create([
            'tanggal' => '2026-09-09',
            'nama' => 'Hari Libur Uji Coba',
            'keterangan' => 'Hari Libur Nasional',
        ]);

        [$userMagang, $magang] = $this->createIntern($pembimbing, 'Peserta Magang Libur Test', 'magang_libur', [
            'tanggal_mulai' => '2026-09-07',
            'tanggal_selesai' => '2026-09-11',
        ]);

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.index'));

        $response->assertStatus(200);
        // Dari 5 hari kerja (Senin-Jumat), 1 hari libur nasional -> total hari kerja magang = 4 Hari Kerja
        $response->assertSee('4 Hari Kerja');
    }

    public function test_rekapitulasi_kehadiran_menghitung_hadir_sakit_izin_cuti_tugas_luar_dan_alpa(): void
    {
        [$userPembimbing, $pembimbing] = $this->createPembimbingWithUser('pembimbing3');

        // Periode magang: 1 September 2026 s/d 30 September 2026
        [$userMagang, $magang] = $this->createIntern($pembimbing, 'Budi Santoso', 'budi_santoso', [
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-30',
        ]);

        // 1. Hadir normal: 2026-09-01
        Presensi::create([
            'pengguna_id' => $userMagang->id,
            'tanggal' => '2026-09-01',
            'jam_masuk' => '07:55:00',
            'jam_keluar' => '16:05:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // 2. Tugas Luar: 2026-09-02
        Presensi::create([
            'pengguna_id' => $userMagang->id,
            'tanggal' => '2026-09-02',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'keterangan' => 'Tugas luar peliputan dinas',
        ]);

        // 3. Sakit resmi: 2026-09-03
        PengajuanIzin::create([
            'pengguna_id' => $userMagang->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-09-03',
            'tanggal_selesai' => '2026-09-03',
            'alasan' => 'Demam flu surat dokter',
            'bukti_file' => 'bukti-izin/surat.pdf',
            'status_approval' => 'disetujui',
        ]);

        // 4. Izin resmi: 2026-09-04
        PengajuanIzin::create([
            'pengguna_id' => $userMagang->id,
            'jenis_izin' => 'izin',
            'tanggal_mulai' => '2026-09-04',
            'tanggal_selesai' => '2026-09-04',
            'alasan' => 'Urusan akademik kampus',
            'bukti_file' => 'bukti-izin/surat_kampus.pdf',
            'status_approval' => 'disetujui',
        ]);

        // 5. Cuti resmi: 2026-09-07
        PengajuanIzin::create([
            'pengguna_id' => $userMagang->id,
            'jenis_izin' => 'cuti',
            'tanggal_mulai' => '2026-09-07',
            'tanggal_selesai' => '2026-09-07',
            'alasan' => 'Cuti keperluan keluarga',
            'bukti_file' => 'bukti-izin/cuti.pdf',
            'status_approval' => 'disetujui',
        ]);

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.index'));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');

        // Masuk ke detail show
        $responseDetail = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.show', $magang->id));
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('Budi Santoso');
        $responseDetail->assertSee('Tugas Luar');
        $responseDetail->assertSee('Sakit');
        $responseDetail->assertSee('Izin');
        $responseDetail->assertSee('Cuti');
        $responseDetail->assertSee('Log Presensi Harian Peserta');
    }

    public function test_pembimbing_tidak_dapat_melihat_detail_binaan_milik_pembimbing_lain(): void
    {
        [$userPembimbingA, $pembimbingA] = $this->createPembimbingWithUser('pembimbing_a');
        [$userPembimbingB, $pembimbingB] = $this->createPembimbingWithUser('pembimbing_b');

        [$userMagangB, $magangB] = $this->createIntern($pembimbingB, 'Anak Magang B', 'magang_b');

        // Pembimbing A mencoba membuka detail binaan Pembimbing B
        $response = $this->actingAs($userPembimbingA)->get(route('pembimbing.binaan.show', $magangB->id));

        $response->assertStatus(404);
    }

    public function test_filter_pencarian_pada_daftar_binaan(): void
    {
        [$userPembimbing, $pembimbing] = $this->createPembimbingWithUser('pembimbing_filter');

        $this->createIntern($pembimbing, 'Siti Nurhaliza', 'siti_magang');
        $this->createIntern($pembimbing, 'Ahmad Dahlan', 'ahmad_magang');

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.index', ['q' => 'Siti']));

        $response->assertStatus(200);
        $response->assertSee('Siti Nurhaliza');
        $response->assertDontSee('Ahmad Dahlan');
    }

    public function test_binaan_status_selesai_tidak_dihitung_pada_binaan_aktif_tetapi_riwayat_tetap_tampil(): void
    {
        [$userPembimbing, $pembimbing] = $this->createPembimbingWithUser('pembimbing_aktif_selesai');

        // Intern 1: Masih Aktif
        $this->createIntern($pembimbing, 'Peserta Masih Aktif', 'magang_aktif', [
            'status' => 'aktif',
            'tanggal_mulai' => Carbon::today()->subMonths(1)->toDateString(),
            'tanggal_selesai' => Carbon::today()->addMonths(1)->toDateString(),
        ]);

        // Intern 2: Sudah Selesai
        $this->createIntern($pembimbing, 'Peserta Sudah Selesai', 'magang_selesai', [
            'status' => 'selesai',
            'tanggal_mulai' => Carbon::today()->subMonths(3)->toDateString(),
            'tanggal_selesai' => Carbon::today()->subMonths(1)->toDateString(),
        ]);

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.index'));

        $response->assertStatus(200);
        // Total Binaan Aktif hanya 1 orang
        $response->assertSee('Binaan Aktif');
        $response->assertSee('1 selesai');

        // Namun peserta berstatus selesai TETAP tampil di tabel dan riwayatnya bisa dilihat
        $response->assertSee('Peserta Sudah Selesai');
        $response->assertSee('Peserta Masih Aktif');
        $response->assertSee('Detail Rekap');
    }

    public function test_metrik_atas_adalah_per_hari_ini_dan_bagian_bawah_adalah_kumulatif(): void
    {
        [$userPembimbing, $pembimbing] = $this->createPembimbingWithUser('pembimbing_hari_ini');

        $today = Carbon::today()->toDateString();

        // Intern A: Hadir hari ini
        [$userA, $magangA] = $this->createIntern($pembimbing, 'Peserta A Hadir', 'magang_a_today', [
            'status' => 'aktif',
            'tanggal_mulai' => Carbon::today()->subDays(10)->toDateString(),
            'tanggal_selesai' => Carbon::today()->addDays(20)->toDateString(),
        ]);
        Presensi::create([
            'pengguna_id' => $userA->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // Intern B: Tugas Luar hari ini
        [$userB, $magangB] = $this->createIntern($pembimbing, 'Peserta B Tugas Luar', 'magang_b_today', [
            'status' => 'aktif',
            'tanggal_mulai' => Carbon::today()->subDays(10)->toDateString(),
            'tanggal_selesai' => Carbon::today()->addDays(20)->toDateString(),
        ]);
        Presensi::create([
            'pengguna_id' => $userB->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'keterangan' => 'Tugas luar liputan lapangan',
        ]);

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.index'));

        $response->assertStatus(200);
        $response->assertSee('Status Presensi Hari Ini');
        $response->assertSee('Total Keseluruhan (Akumulasi Masa Magang)');
    }
}
