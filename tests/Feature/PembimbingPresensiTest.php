<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembimbingPresensiTest extends TestCase
{
    use RefreshDatabase;

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

    private function createIntern(Pembimbing $pembimbing, string $name, string $username): array
    {
        $divisi = Divisi::firstOrCreate(
            ['nama_divisi' => 'Teknologi Informasi'],
            [
                'nama_pimpinan' => 'Kepala Divisi IT',
                'nip_pimpinan' => '197501012000031001',
                'jabatan_pimpinan' => 'Kadiv IT',
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

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM'.rand(1000, 9999),
            'nama_lengkap' => $name,
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
        ]);

        return [$user, $magang];
    }

    public function test_pembimbing_can_access_presensi_index_and_see_supervised_interns_attendance(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_a');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Peserta Binaan A', 'magang_a');

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '07:55:00',
            'jam_keluar' => '16:05:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'lokasi_masuk' => '-3.489, 114.825',
            'keterangan' => 'Hadir tepat waktu',
        ]);

        $response = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pembimbing.presensi.index');
        $response->assertSee('Peserta Binaan A');
        $response->assertSee('Hadir tepat waktu');
        $response->assertSee('07:55');
    }

    public function test_pembimbing_cannot_see_attendance_of_other_pembimbing_interns(): void
    {
        [$pembimbingAUser, $pembimbingA] = $this->createPembimbingWithUser('pembimbing_a');
        [$pembimbingBUser, $pembimbingB] = $this->createPembimbingWithUser('pembimbing_b');

        [$internAUser, $internA] = $this->createIntern($pembimbingA, 'Anak Binaan A', 'magang_a');
        [$internBUser, $internB] = $this->createIntern($pembimbingB, 'Anak Binaan B Lain', 'magang_b');

        Presensi::create([
            'pengguna_id' => $internAUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        Presensi::create([
            'pengguna_id' => $internBUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:15:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // Pembimbing A mengakses riwayat presensi
        $response = $this->actingAs($pembimbingAUser)->get(route('pembimbing.presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Anak Binaan A');
        $response->assertDontSee('Anak Binaan B Lain');
    }

    public function test_pembimbing_can_filter_by_specific_intern(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_multi');

        [$intern1User, $intern1] = $this->createIntern($pembimbing, 'Peserta Satu', 'magang_1');
        [$intern2User, $intern2] = $this->createIntern($pembimbing, 'Peserta Dua', 'magang_2');

        Presensi::create([
            'pengguna_id' => $intern1User->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'keterangan' => 'Kehadiran Intern Satu Unik',
        ]);

        Presensi::create([
            'pengguna_id' => $intern2User->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:10:00',
            'status' => 'hadir',
            'mode_kerja' => 'wfh',
            'keterangan' => 'Kehadiran Intern Dua Unik',
        ]);

        $response = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.index', [
            'magang_id' => $intern1->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kehadiran Intern Satu Unik');
        $response->assertDontSee('Kehadiran Intern Dua Unik');
        $response->assertViewHas('presensi', function ($presensi) use ($intern1User, $intern2User) {
            return $presensi->contains('pengguna_id', $intern1User->id)
                && ! $presensi->contains('pengguna_id', $intern2User->id);
        });
    }

    public function test_pembimbing_can_filter_by_date_and_mode_kerja(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_filter');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Peserta Filter', 'magang_f');

        // Presensi kemarin onsite
        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => '2026-09-25',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'keterangan' => 'Presensi Onsite 25 Sep',
        ]);

        // Presensi hari ini wfh
        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'wfh',
            'keterangan' => 'Presensi WFH 28 Sep',
        ]);

        // Filter mode WFH
        $response = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.index', [
            'mode_kerja' => 'wfh',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Presensi WFH 28 Sep');
        $response->assertDontSee('Presensi Onsite 25 Sep');
    }

    public function test_pembimbing_can_access_presensi_cetak(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_cetak');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Peserta Cetak', 'magang_c');

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // 1. Default request menghasilkan PDF stream langsung via mPDF
        $pdfResponse = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.cetak', [
            'tanggal' => '2026-09-28',
        ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        // 2. Format HTML untuk pratinjau / verifikasi template blade
        $response = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.cetak', [
            'tanggal' => '2026-09-28',
            'format' => 'html',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('pembimbing.presensi.cetak');
        $response->assertSee('LAPORAN PRESENSI MAGANG');
        $response->assertSee('Peserta Cetak');
        $response->assertSee($pembimbing->nama_lengkap);
        $response->assertSee('Periode (pada unit tersebut)');
        $response->assertSee('TL');
    }

    public function test_unauthorized_user_cannot_access_pembimbing_presensi(): void
    {
        // Tamu tidak login -> dialihkan ke login
        $guestResponse = $this->get(route('pembimbing.presensi.index'));
        $guestResponse->assertRedirect(route('login'));

        // Akun magang -> 403 Forbidden
        $magangUser = Pengguna::create([
            'username' => 'magang_biasa',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $forbiddenResponse = $this->actingAs($magangUser)->get(route('pembimbing.presensi.index'));
        $forbiddenResponse->assertStatus(403);
    }

    public function test_pembimbing_laporan_presensi_magang_format_tabel_lengkap_dan_informasi_unit(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_rekap');
        [$intern1User, $intern1] = $this->createIntern($pembimbing, 'Peserta Rekap Satu', 'rekap_1');
        $divisi = $intern1->divisi;
        $pembimbing->update(['divisi_id' => $divisi->id]);
        $intern1->update([
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-30',
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat',
        ]);

        [$intern2User, $intern2] = $this->createIntern($pembimbing, 'Peserta Rekap Dua', 'rekap_2');
        $intern2->update([
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-30',
            'instansi_pendidikan' => 'Politeknik Negeri Banjarmasin',
        ]);

        // Intern 1: Hadir biasa pada 2026-09-01 (Senin)
        Presensi::create([
            'pengguna_id' => $intern1User->id,
            'tanggal' => '2026-09-01',
            'jam_masuk' => '07:55:00',
            'jam_keluar' => '16:05:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // Intern 1: Tugas Luar pada 2026-09-02 (Selasa)
        Presensi::create([
            'pengguna_id' => $intern1User->id,
            'tanggal' => '2026-09-02',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'tugas_luar',
            'keterangan' => 'Tugas luar peliputan',
        ]);

        // Intern 1: Sakit pada 2026-09-03 (Rabu)
        PengajuanIzin::create([
            'pengguna_id' => $intern1User->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-09-03',
            'tanggal_selesai' => '2026-09-03',
            'alasan' => 'Demam tinggi',
            'status_approval' => 'disetujui',
        ]);

        // Intern 2: Izin pada 2026-09-01 (Senin)
        PengajuanIzin::create([
            'pengguna_id' => $intern2User->id,
            'jenis_izin' => 'izin',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-01',
            'alasan' => 'Urusan kampus',
            'status_approval' => 'disetujui',
        ]);

        // Intern 2: Hadir pada 2026-09-02 (Selasa)
        Presensi::create([
            'pengguna_id' => $intern2User->id,
            'tanggal' => '2026-09-02',
            'jam_masuk' => '07:50:00',
            'jam_keluar' => '16:10:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        $response = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.cetak', [
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
            'format' => 'html',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('pembimbing.presensi.cetak');

        // Judul laporan & periode
        $response->assertSee('LAPORAN PRESENSI MAGANG');
        $response->assertSee('Dari Tanggal 1 September 2026 Sampai Tanggal 30 September 2026');

        // Informasi pembimbing dan unit kerja
        $response->assertSee($pembimbing->nama_lengkap);
        $response->assertSee($divisi->nama_divisi);
        $response->assertSee('Waktu Cetak');
        $response->assertSee('Cakupan Data');

        // Format kolom tabel
        $response->assertSee('No');
        $response->assertSee('Nama');
        $response->assertSee('Periode (pada unit tersebut)');
        $response->assertSee('Hadir');
        $response->assertSee('Sakit');
        $response->assertSee('Izin');
        $response->assertSee('Alfa');
        $response->assertSee('TL');

        // Data peserta dan ringkasan
        $response->assertSee('Peserta Rekap Satu');
        $response->assertSee('Peserta Rekap Dua');
        $response->assertSee('1 Sep 2026 - 30 Sep 2026');
        $response->assertSee('Ringkasan:');
        $response->assertSee('Total Peserta 2');
    }
}
