<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\HariLibur;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PenempatanMagang;
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
        $response->assertSee('Daftar Binaan');
        $response->assertSee('Peserta Magang A');
        $response->assertSee('Hari Kerja');
        $response->assertSee('Hadir');
        $response->assertSee('Sakit');
        $response->assertSee('Izin');
        $response->assertSee('Cuti');
        $response->assertSee('TL');
        $response->assertSee('Alfa');
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
        $responseDetail->assertSee('TL');
        $responseDetail->assertSee('Sakit');
        $responseDetail->assertSee('Izin');
        $responseDetail->assertSee('Cuti');
        $responseDetail->assertSee('Buka riwayat presensi harian');
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
        $response->assertSee('1 Peserta aktif');
        $response->assertSee('1 selesai');

        // Namun peserta berstatus selesai TETAP tampil di tabel dan riwayatnya bisa dilihat
        $response->assertSee('Peserta Sudah Selesai');
        $response->assertSee('Peserta Masih Aktif');
        $response->assertSee('Detail &amp; Riwayat harian', false);
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
        $response->assertSee('2 Peserta aktif');
        $response->assertSee('Cakupan rekap');
        $response->assertSee('Progres Masa Binaan');
        $response->assertSee('Rekap Hadir');
    }

    public function test_fitur_binaan_menampilkan_periode_unit_dan_progres_hari_ke_serta_rekap_kehadiran(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 13, 10, 0, 0));

        [$userPembimbing, $pembimbing] = $this->createPembimbingWithUser('pembimbing_rotasi');

        $divisiAgama = Divisi::create([
            'nama_divisi' => 'Divisi Keagamaan',
            'nama_pimpinan' => 'Kabid Agama',
            'nip_pimpinan' => '197001011990011002',
            'jabatan_pimpinan' => 'Kabid',
            'latitude' => -3.489,
            'longitude' => 114.825,
            'radius_meter' => 50,
        ]);

        // Magang 6 bulan di dinas (2026-10-01 s/d 2027-03-31)
        [$userMeli, $meli] = $this->createIntern($pembimbing, 'Meli Binaan', 'meli_binaan', [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2027-03-31',
        ]);

        // Namun di unit Divisi Keagamaan hanya 2 bulan (2026-10-01 s/d 2026-11-28)
        PenempatanMagang::create([
            'magang_id' => $meli->id,
            'divisi_id' => $divisiAgama->id,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-28',
        ]);

        // Presensi 2 hari kemarin
        Presensi::create([
            'pengguna_id' => $userMeli->id,
            'tanggal' => '2026-10-01',
            'jam_masuk' => '07:50:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);
        Presensi::create([
            'pengguna_id' => $userMeli->id,
            'tanggal' => '2026-10-02',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // Index
        $responseIndex = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Divisi Keagamaan');
        $responseIndex->assertSee('2 Bulan');
        $responseIndex->assertSee('Total Magang Dinas');
        $responseIndex->assertSee('6 Bulan');
        $responseIndex->assertSee('Progres Masa Binaan');
        $responseIndex->assertSee('Hari ke-9');
        $responseIndex->assertSee('Hadir 2 kali');

        // Show
        $responseShow = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.show', $meli->id));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Divisi Keagamaan');
        $responseShow->assertSee('2 Bulan');
        $responseShow->assertSee('Dinas: 6 Bulan');
        $responseShow->assertSee('Hari ke-9');
        $responseShow->assertSeeText('Hadir 2 kali');

        Carbon::setTestNow();
    }

    public function test_urutan_binaan_berdasarkan_status_aktif_dahulu_lalu_sesuai_abjad(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 13, 10, 0, 0));

        [$userPembimbing, $pembimbing] = $this->createPembimbingWithUser('pembimbing_urutan');

        // 1. Zulfa: Aktif, inisial Z
        $this->createIntern($pembimbing, 'Zulfa Aktif', 'zulfa_aktif', [
            'status' => 'aktif',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-30',
        ]);

        // 2. Ahmad: Selesai, inisial A
        $this->createIntern($pembimbing, 'Ahmad Selesai', 'ahmad_selesai', [
            'status' => 'selesai',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-09-30',
        ]);

        // 3. Budi: Aktif, inisial B
        $this->createIntern($pembimbing, 'Budi Aktif', 'budi_aktif', [
            'status' => 'aktif',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-30',
        ]);

        // 4. Citra: Selesai, inisial C
        $this->createIntern($pembimbing, 'Citra Selesai', 'citra_selesai', [
            'status' => 'selesai',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-09-30',
        ]);

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.index'));

        $response->assertStatus(200);

        // Urutan yang diharapkan: Aktif dahulu (A-Z) -> Budi Aktif, Zulfa Aktif; lalu Selesai (A-Z) -> Ahmad Selesai, Citra Selesai
        $response->assertSeeInOrder([
            'Budi Aktif',
            'Zulfa Aktif',
            'Ahmad Selesai',
            'Citra Selesai',
        ]);

        Carbon::setTestNow();
    }

    public function test_riwayat_presensi_binaan_menampilkan_catatan_sakit_atau_izin_saat_pulang_duluan(): void
    {
        [$userPembimbing, $pembimbing] = $this->createPembimbingWithUser('pembimbing_riwayat_pulang');

        [$userIntern, $intern] = $this->createIntern($pembimbing, 'Peserta Pulang Awal', 'peserta_pulang_awal', [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-11-30',
        ]);

        // Hari 1: Pulang duluan pukul 14:51 karena sakit
        Presensi::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-10-01',
            'jam_masuk' => '07:55:00',
            'jam_keluar' => '14:51:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'keterangan' => 'Pulang: Sakit',
        ]);

        // Hari 2: Pulang duluan pukul 13:30 karena izin
        Presensi::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-10-02',
            'jam_masuk' => '07:45:00',
            'jam_keluar' => '13:30:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'keterangan' => 'Terlambat 0 menit | Pulang: Izin',
        ]);

        // Hari 3: Pulang normal pukul 16:15
        Presensi::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-10-05',
            'jam_masuk' => '07:50:00',
            'jam_keluar' => '16:15:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.binaan.riwayat', $intern->id));

        $response->assertStatus(200);
        // Menampilkan 14.51 (sakit)
        $response->assertSee('14.51');
        $response->assertSee('(sakit)');
        // Menampilkan 13.30 (izin)
        $response->assertSee('13.30');
        $response->assertSee('(izin)');
        // Pulang normal 16.15
        $response->assertSee('16.15');
    }
}
