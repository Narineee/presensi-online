<?php

namespace Tests\Feature;

use App\Models\DetailPenilaian;
use App\Models\Divisi;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PenempatanMagang;
use App\Models\Pengguna;
use App\Models\Penilaian;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenilaianMultiPembimbingTest extends TestCase
{
    use RefreshDatabase;

    private function createKriteria(): array
    {
        $k1 = KriteriaPenilaian::create([
            'nama' => 'Kedisiplinan & Presensi',
            'deskripsi' => 'Ketepatan waktu dan jam kerja',
            'bobot' => 40,
            'is_presensi' => true,
        ]);

        $k2 = KriteriaPenilaian::create([
            'nama' => 'Kualitas Kerja & Tugas',
            'deskripsi' => 'Hasil pengerjaan proyek dan tugas',
            'bobot' => 60,
            'is_presensi' => false,
        ]);

        return [$k1, $k2];
    }

    private function createMentor(string $username, string $nama, string $nip): array
    {
        $user = Pengguna::create([
            'username' => $username,
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $mentor = Pembimbing::create([
            'pengguna_id' => $user->id,
            'nip' => $nip,
            'nama_lengkap' => $nama,
            'jabatan' => 'Pembimbing Lapangan',
            'no_hp' => '081234567890',
        ]);

        return [$user, $mentor];
    }

    private function createDivisi(string $nama): Divisi
    {
        return Divisi::create([
            'nama_divisi' => $nama,
            'nama_pimpinan' => 'Pimpinan '.$nama,
            'nip_pimpinan' => '198001012000031001',
            'jabatan_pimpinan' => 'Kepala '.$nama,
            'latitude' => -3.4893886,
            'longitude' => 114.8252584,
            'radius_meter' => 50,
        ]);
    }

    public function test_single_mentor_produces_single_evaluation_and_same_accumulated_score(): void
    {
        [$k1, $k2] = $this->createKriteria();
        [$mentorUser, $mentor] = $this->createMentor('mentor_single', 'Pak Budi Single', '198101012005011001');
        $divisi = $this->createDivisi('Divisi IT');

        $internUser = Pengguna::create([
            'username' => 'intern_single',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $internUser->id,
            'pembimbing_id' => $mentor->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'MGG001',
            'nama_lengkap' => 'Ahmad Single Unit',
            'instansi_pendidikan' => 'Politeknik Negeri',
            'jurusan' => 'Teknik Informatika',
            'tanggal_mulai' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addMonth()->format('Y-m-d'),
            'status' => 'aktif',
        ]);

        // Karena tidak pindah unit, hanya ada 1 penempatan dan 1 pembimbing
        $penempatan = PenempatanMagang::create([
            'magang_id' => $magang->id,
            'divisi_id' => $divisi->id,
            'pembimbing_id' => $mentor->id,
            'tanggal_mulai' => $magang->tanggal_mulai,
            'tanggal_selesai' => $magang->tanggal_selesai,
        ]);

        // Mentor submit penilaian
        $penilaian = Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $mentor->id,
            'penempatan_magang_id' => $penempatan->id,
            'total_nilai' => 88,
        ]);
        DetailPenilaian::create(['penilaian_id' => $penilaian->id, 'kriteria_id' => $k1->id, 'nilai' => 88]);
        DetailPenilaian::create(['penilaian_id' => $penilaian->id, 'kriteria_id' => $k2->id, 'nilai' => 88]);

        // Cek getPenilaianAkumulasi()
        $akumulasi = $magang->getPenilaianAkumulasi();
        $this->assertNotNull($akumulasi);
        $this->assertFalse($akumulasi->is_akumulasi);
        $this->assertEquals(88, $akumulasi->total_nilai);
        $this->assertEquals('B', $akumulasi->predikat);

        // Akses halaman sertifikat peserta magang
        $responseSertif = $this->actingAs($internUser)->get(route('magang.sertifikat.index'));
        $responseSertif->assertStatus(200);
        $responseSertif->assertSee('B');
        $responseSertif->assertSee('Baik');
        $responseSertif->assertSee('Ahmad Single Unit');

        // Akses halaman penilaian peserta magang
        $responsePenilaian = $this->actingAs($internUser)->get(route('magang.penilaian.index'));
        $responsePenilaian->assertStatus(200);
        $responsePenilaian->assertSee('88');
        $responsePenilaian->assertSee('Pak Budi Single');
        $responsePenilaian->assertDontSee('Akumulasi Multi-Pembimbing');
    }

    public function test_multiple_mentors_can_evaluate_distinct_unit_placements(): void
    {
        [$k1, $k2] = $this->createKriteria();

        [$mentorUser1, $mentor1] = $this->createMentor('mentor_1', 'Pak Joko Unit A', '198101012005011002');
        [$mentorUser2, $mentor2] = $this->createMentor('mentor_2', 'Bu Rina Unit B', '198301012006012001');

        $divisiA = $this->createDivisi('Subbag Tata Usaha');
        $divisiB = $this->createDivisi('Bidang Pelayanan Informasi');

        $internUser = Pengguna::create([
            'username' => 'intern_multi',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $internUser->id,
            'pembimbing_id' => $mentor1->id,
            'divisi_id' => $divisiA->id,
            'no_induk' => 'MGG002',
            'nama_lengkap' => 'Siti Multi Unit',
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat',
            'jurusan' => 'Ilmu Komunikasi',
            'tanggal_mulai' => Carbon::now()->subMonths(4)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->addMonth()->format('Y-m-d'),
            'status' => 'aktif',
        ]);

        // Rotasi Unit 1: 2 bulan di Tata Usaha dengan Pak Joko
        $penempatan1 = PenempatanMagang::create([
            'magang_id' => $magang->id,
            'divisi_id' => $divisiA->id,
            'pembimbing_id' => $mentor1->id,
            'tanggal_mulai' => Carbon::now()->subMonths(4)->format('Y-m-d'),
            'tanggal_selesai' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'catatan' => 'Periode pertama di TU',
        ]);

        // Rotasi Unit 2: 2 bulan di Pelayanan Informasi dengan Bu Rina
        $penempatan2 = PenempatanMagang::create([
            'magang_id' => $magang->id,
            'divisi_id' => $divisiB->id,
            'pembimbing_id' => $mentor2->id,
            'tanggal_mulai' => Carbon::now()->subMonths(2)->addDay()->format('Y-m-d'),
            'tanggal_selesai' => $magang->tanggal_selesai,
        ]);

        // Pak Joko memberikan penilaian untuk Unit 1: Total Nilai 80 (Kedisiplinan 80, Kualitas 80)
        $penilaian1 = Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $mentor1->id,
            'penempatan_magang_id' => $penempatan1->id,
            'total_nilai' => 80,
        ]);
        DetailPenilaian::create(['penilaian_id' => $penilaian1->id, 'kriteria_id' => $k1->id, 'nilai' => 80]);
        DetailPenilaian::create(['penilaian_id' => $penilaian1->id, 'kriteria_id' => $k2->id, 'nilai' => 80]);

        // Bu Rina memberikan penilaian untuk Unit 2: Total Nilai 90 (Kedisiplinan 90, Kualitas 90)
        $penilaian2 = Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $mentor2->id,
            'penempatan_magang_id' => $penempatan2->id,
            'total_nilai' => 90,
        ]);
        DetailPenilaian::create(['penilaian_id' => $penilaian2->id, 'kriteria_id' => $k1->id, 'nilai' => 90]);
        DetailPenilaian::create(['penilaian_id' => $penilaian2->id, 'kriteria_id' => $k2->id, 'nilai' => 90]);

        // Verifikasi hasil akumulasi
        $akumulasi = $magang->fresh()->getPenilaianAkumulasi();
        $this->assertNotNull($akumulasi);
        $this->assertTrue($akumulasi->is_akumulasi);
        $this->assertEquals(2, $akumulasi->evaluasi_count);
        $this->assertEquals(85.0, $akumulasi->total_nilai);
        $this->assertEquals('B', $akumulasi->predikat);
        $this->assertEquals('Baik', $akumulasi->keterangan_predikat);

        // Kriteria rata-rata
        $this->assertCount(2, $akumulasi->detail_rata_rata);
        $this->assertEquals(85, $akumulasi->detail_rata_rata->firstWhere('kriteria_id', $k1->id)->nilai);
        $this->assertEquals(85, $akumulasi->detail_rata_rata->firstWhere('kriteria_id', $k2->id)->nilai);

        // Halaman Penilaian Saya: menampilkan banner akumulasi dan kartu kedua pembimbing
        $responsePenilaian = $this->actingAs($internUser)->get(route('magang.penilaian.index'));
        $responsePenilaian->assertStatus(200);
        $responsePenilaian->assertSee('(AKUMULASI)');
        $responsePenilaian->assertSee('Nilai diakumulasikan');
        $responsePenilaian->assertSee('85');
        $responsePenilaian->assertSee('Pak Joko Unit A');
        $responsePenilaian->assertSee('Bu Rina Unit B');

        // Halaman Sertifikat: menampilkan nilai akumulasi [B (Baik)]
        $responseSertif = $this->actingAs($internUser)->get(route('magang.sertifikat.index'));
        $responseSertif->assertStatus(200);
        $responseSertif->assertSee('B');
        $responseSertif->assertSee('Baik');
        $responseSertif->assertSee('Siti Multi Unit');

        // Pembimbing 1 dapat melihat lembar nilainya
        $responsePem1 = $this->actingAs($mentorUser1)->get(route('pembimbing.penilaian.show', $penilaian1->id));
        $responsePem1->assertStatus(200);
        $responsePem1->assertSee('Akumulasi 2 Unit Penempatan');
        $responsePem1->assertSee('Pak Joko Unit A');

        // Pembimbing 2 dapat melihat lembar nilainya
        $responsePem2 = $this->actingAs($mentorUser2)->get(route('pembimbing.penilaian.show', $penilaian2->id));
        $responsePem2->assertStatus(200);
        $responsePem2->assertSee('Akumulasi 2 Unit Penempatan');
        $responsePem2->assertSee('Bu Rina Unit B');

        // Admin dapat melihat ringkasan rekapitulasi penilaian dengan label akumulasi
        $adminUser = Pengguna::create([
            'username' => 'admin_multi',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $responseAdminIndex = $this->actingAs($adminUser)->get(route('admin.penilaian.index'));
        $responseAdminIndex->assertStatus(200);
        $responseAdminIndex->assertSee('85');
        $responseAdminIndex->assertSee('Rata-rata 2 mentor');

        // Admin dapat membuka lembar nilai individual penempatan
        $responseAdminShow = $this->actingAs($adminUser)->get(route('admin.penilaian.show', $penilaian1->id));
        $responseAdminShow->assertStatus(200);
        $responseAdminShow->assertSee('Akumulasi 2 Unit Penempatan');
        $responseAdminShow->assertSee('Pak Joko Unit A');
    }
}
