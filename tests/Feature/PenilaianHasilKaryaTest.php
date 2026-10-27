<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use App\Models\Penilaian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenilaianHasilKaryaTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(): array
    {
        $k1 = KriteriaPenilaian::create([
            'nama' => 'Kedisiplinan',
            'bobot' => 50,
            'is_presensi' => true,
        ]);

        $k2 = KriteriaPenilaian::create([
            'nama' => 'Kualitas Kerja',
            'bobot' => 50,
            'is_presensi' => false,
        ]);

        $divisi = Divisi::create([
            'nama_divisi' => 'Divisi Kominfo',
            'nama_pimpinan' => 'Kadis Kominfo',
            'nip_pimpinan' => '197001011995011001',
            'jabatan_pimpinan' => 'Kepala Dinas',
            'latitude' => -3.44,
            'longitude' => 114.83,
            'radius_meter' => 100,
        ]);

        $pembimbingUser = Pengguna::create([
            'username' => 'mentor1',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $pembimbingUser->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Pak Pembimbing Utama',
            'jabatan' => 'Pranata Komputer',
            'divisi_id' => $divisi->id,
        ]);

        $magangUser = Pengguna::create([
            'username' => 'intern1',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $magangUser->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => '2026001',
            'nama_lengkap' => 'Siti Magang',
            'jenis_kelamin' => 'P',
            'jurusan' => 'Teknik Informatika',
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat',
            'tanggal_mulai' => now()->subMonths(3)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'aktif',
        ]);

        return compact('k1', 'k2', 'divisi', 'pembimbingUser', 'pembimbing', 'magangUser', 'magang');
    }

    public function test_pembimbing_can_create_penilaian_with_hasil_karya_points(): void
    {
        $data = $this->setupData();

        $response = $this->actingAs($data['pembimbingUser'])->post(route('pembimbing.penilaian.store'), [
            'magang_id' => $data['magang']->id,
            'nilai' => [
                $data['k1']->id => 90,
                $data['k2']->id => 85,
            ],
            'hasil_karya' => [
                'Modul Presensi GPS Berbasis Mobile',
                'Desain Mockup UI Sistem Pengaduan',
                '   ', // should be trimmed and filtered out
            ],
        ]);

        $response->assertRedirect();

        $penilaian = Penilaian::where('magang_id', $data['magang']->id)->first();
        $this->assertNotNull($penilaian);
        $this->assertEquals([
            'Modul Presensi GPS Berbasis Mobile',
            'Desain Mockup UI Sistem Pengaduan',
        ], $penilaian->hasil_karya);
    }

    public function test_pembimbing_can_create_penilaian_without_hasil_karya_as_optional(): void
    {
        $data = $this->setupData();

        $response = $this->actingAs($data['pembimbingUser'])->post(route('pembimbing.penilaian.store'), [
            'magang_id' => $data['magang']->id,
            'nilai' => [
                $data['k1']->id => 88,
                $data['k2']->id => 92,
            ],
            'hasil_karya' => ['', '  '],
        ]);

        $response->assertRedirect();

        $penilaian = Penilaian::where('magang_id', $data['magang']->id)->first();
        $this->assertNotNull($penilaian);
        $this->assertNull($penilaian->hasil_karya);
    }

    public function test_pembimbing_can_update_penilaian_hasil_karya(): void
    {
        $data = $this->setupData();

        $penilaian = Penilaian::create([
            'magang_id' => $data['magang']->id,
            'pembimbing_id' => $data['pembimbing']->id,
            'total_nilai' => 88,
            'hasil_karya' => ['Karya Lama'],
        ]);

        $response = $this->actingAs($data['pembimbingUser'])->put(route('pembimbing.penilaian.update', $penilaian->id), [
            'nilai' => [
                $data['k1']->id => 90,
                $data['k2']->id => 95,
            ],
            'hasil_karya' => [
                'Aplikasi E-Arsip Digital',
                'Buku Manual Pengguna',
            ],
        ]);

        $response->assertRedirect();

        $penilaian->refresh();
        $this->assertEquals([
            'Aplikasi E-Arsip Digital',
            'Buku Manual Pengguna',
        ], $penilaian->hasil_karya);
    }

    public function test_hasil_karya_is_displayed_below_rekap_table_when_filled(): void
    {
        $data = $this->setupData();

        $penilaian = Penilaian::create([
            'magang_id' => $data['magang']->id,
            'pembimbing_id' => $data['pembimbing']->id,
            'total_nilai' => 90,
            'hasil_karya' => [
                'Dashboard Analytics Presensi',
                'Laporan Evaluasi Jaringan',
            ],
        ]);

        // 1. Pembimbing Show
        $resPembimbingShow = $this->actingAs($data['pembimbingUser'])->get(route('pembimbing.penilaian.show', $penilaian->id));
        $resPembimbingShow->assertOk();
        $resPembimbingShow->assertSee('Hasil Karya Peserta Magang');
        $resPembimbingShow->assertSee('Dashboard Analytics Presensi');
        $resPembimbingShow->assertSee('Laporan Evaluasi Jaringan');

        // 2. Pembimbing Cetak Single (format=html)
        $resPembimbingCetak = $this->actingAs($data['pembimbingUser'])->get(route('pembimbing.penilaian.cetak', [
            'magang_id' => $data['magang']->id,
            'format' => 'html',
        ]));
        $resPembimbingCetak->assertOk();
        $resPembimbingCetak->assertSee('Hasil Karya Peserta Magang');
        $resPembimbingCetak->assertSee('Dashboard Analytics Presensi');

        // 3. Magang Index
        $resMagangIndex = $this->actingAs($data['magangUser'])->get(route('magang.penilaian.index'));
        $resMagangIndex->assertOk();
        $resMagangIndex->assertSee('Hasil Karya Peserta Magang');
        $resMagangIndex->assertSee('Dashboard Analytics Presensi');

        // 4. Magang Cetak (format=html)
        $resMagangCetak = $this->actingAs($data['magangUser'])->get(route('magang.penilaian.cetak', ['format' => 'html']));
        $resMagangCetak->assertOk();
        $resMagangCetak->assertSee('Hasil Karya Peserta Magang');
        $resMagangCetak->assertSee('Dashboard Analytics Presensi');

        // 5. Admin Show
        $adminUser = Pengguna::create([
            'username' => 'admin_test',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $resAdminShow = $this->actingAs($adminUser)->get(route('admin.penilaian.show', $penilaian->id));
        $resAdminShow->assertOk();
        $resAdminShow->assertSee('Hasil Karya Peserta Magang');
        $resAdminShow->assertSee('Dashboard Analytics Presensi');
    }

    public function test_hasil_karya_is_hidden_when_null_or_empty(): void
    {
        $data = $this->setupData();

        $penilaian = Penilaian::create([
            'magang_id' => $data['magang']->id,
            'pembimbing_id' => $data['pembimbing']->id,
            'total_nilai' => 85,
            'hasil_karya' => null,
        ]);

        $resPembimbingShow = $this->actingAs($data['pembimbingUser'])->get(route('pembimbing.penilaian.show', $penilaian->id));
        $resPembimbingShow->assertOk();
        $resPembimbingShow->assertDontSee('Hasil Karya Peserta Magang');

        $resMagangIndex = $this->actingAs($data['magangUser'])->get(route('magang.penilaian.index'));
        $resMagangIndex->assertOk();
        $resMagangIndex->assertDontSee('Hasil Karya Peserta Magang');
    }

    public function test_multi_pembimbing_akumulasi_merges_hasil_karya_points(): void
    {
        $data = $this->setupData();

        $mentor2User = Pengguna::create([
            'username' => 'mentor2',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $mentor2 = Pembimbing::create([
            'pengguna_id' => $mentor2User->id,
            'nip' => '198602022011021002',
            'nama_lengkap' => 'Bu Pembimbing Kedua',
            'jabatan' => 'Analisis Sistem',
            'divisi_id' => $data['divisi']->id,
        ]);

        // Assessment 1
        Penilaian::create([
            'magang_id' => $data['magang']->id,
            'pembimbing_id' => $data['pembimbing']->id,
            'total_nilai' => 90,
            'hasil_karya' => ['Aplikasi Absensi', 'Desain Poster'],
        ]);

        // Assessment 2
        Penilaian::create([
            'magang_id' => $data['magang']->id,
            'pembimbing_id' => $mentor2->id,
            'total_nilai' => 86,
            'hasil_karya' => ['Dokumentasi API', 'Aplikasi Absensi'], // Duplicate 'Aplikasi Absensi'
        ]);

        $akumulasi = $data['magang']->getPenilaianAkumulasi();

        $this->assertNotNull($akumulasi);
        $this->assertTrue($akumulasi->is_akumulasi);
        $this->assertCount(3, $akumulasi->hasil_karya);
        $this->assertEquals(['Aplikasi Absensi', 'Desain Poster', 'Dokumentasi API'], $akumulasi->hasil_karya);

        // Test tampilan index magang: menampilkan rincian per pembimbing dan gabungan
        $resMagangIndex = $this->actingAs($data['magangUser'])->get(route('magang.penilaian.index'));
        $resMagangIndex->assertOk();
        $resMagangIndex->assertSee('Riwayat Nilai per Pembimbing &amp; Unit', false);
        $resMagangIndex->assertSee('Hasil Karya pada Unit / Pembimbing Ini:');
        $resMagangIndex->assertSee('Desain Poster');
        $resMagangIndex->assertSee('Dokumentasi API');
        $resMagangIndex->assertSee('Hasil Karya Gabungan (Seluruh Unit / Pembimbing)');
    }
}
