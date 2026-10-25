<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use App\Models\Penilaian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SertifikatMagangTest extends TestCase
{
    use RefreshDatabase;

    private function createInternUser(): array
    {
        $pembimbingUser = Pengguna::create([
            'username' => 'pem_sertif',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $pembimbingUser->id,
            'nip' => '198001012000031001',
            'nama_lengkap' => 'Pembimbing Sertif',
            'jabatan' => 'Pembimbing Lapangan',
            'no_hp' => '08123456789',
        ]);

        $divisi = Divisi::create([
            'nama_divisi' => 'Teknologi Informasi',
            'nama_pimpinan' => 'Kadiv IT',
            'nip_pimpinan' => '197501012000031001',
            'jabatan_pimpinan' => 'Kadiv IT',
            'latitude' => -3.4893886,
            'longitude' => 114.8252584,
            'radius_meter' => 40,
        ]);

        $internUser = Pengguna::create([
            'username' => 'intern_sertif',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $internUser->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM998877',
            'nama_lengkap' => 'Intern Sertif Testing',
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat',
            'jurusan' => 'Ilmu Komputer',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
            'face_registered_at' => now(),
        ]);

        return [$internUser, $magang, $pembimbing];
    }

    public function test_rekap_page_displays_cetak_sertifikat_menu(): void
    {
        [$internUser, $magang] = $this->createInternUser();

        $response = $this->actingAs($internUser)->get(route('magang.rekap'));

        $response->assertStatus(200);
        $response->assertSee('Cetak sertifikat');
        $response->assertSee(route('magang.sertifikat.index'));
    }

    public function test_sertifikat_page_shows_belum_diterbitkan_when_penilaian_does_not_exist(): void
    {
        [$internUser, $magang] = $this->createInternUser();

        $response = $this->actingAs($internUser)->get(route('magang.sertifikat.index'));

        $response->assertStatus(200);
        $response->assertSee('Sertifikat Belum Diterbitkan');
        $response->assertSee('Pembimbing lapangan belum menginput evaluasi nilai akhir');
        $response->assertSee('Kembali ke Rekap');
        $response->assertDontSee('Predikat Kelulusan');
    }

    public function test_sertifikat_page_displays_details_and_grade_when_penilaian_exists(): void
    {
        [$internUser, $magang, $pembimbing] = $this->createInternUser();

        Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $pembimbing->id,
            'total_nilai' => 95, // Predikat A (Sangat Baik)
        ]);

        $response = $this->actingAs($internUser)->get(route('magang.sertifikat.index'));

        $response->assertStatus(200);
        $response->assertSee('SERTIFIKAT');
        $response->assertSee('Diberikan kepada:');
        $response->assertSee('Intern Sertif Testing');
        $response->assertSee('<u>Intern Sertif Testing</u>', false);
        $response->assertSee('Nomor Induk NIM998877');
        $response->assertSee('Jurusan: Ilmu Komputer');
        $response->assertSee('Lembaga Pendidikan: Universitas Lambung Mangkurat');
        $response->assertSee('Dinas Komunikasi dan Informatika');
        $response->assertSee('memperoleh nilai akhir dengan kualifikasi');
        $response->assertSee('Sangat Baik');
        $response->assertDontSee('[A (Sangat Baik)]');
        $response->assertSee('30 November 2026'); // Tanggal sertif dibuat = tanggal terakhir periode magang
        $response->assertSee('Cetak Sertifikat');
        $response->assertDontSee('Sertifikat Belum Diterbitkan');

        // Uji juga pada tampilan cetak / PDF format html
        $cetakResponse = $this->actingAs($internUser)->get(route('magang.sertifikat.cetak', ['format' => 'html']));
        $cetakResponse->assertStatus(200);
        $cetakResponse->assertSee('<u>Intern Sertif Testing</u>', false);
        $cetakResponse->assertSee('Nomor Induk NIM998877');
        $cetakResponse->assertSee('Jurusan: Ilmu Komputer');
        $cetakResponse->assertSee('Lembaga Pendidikan: Universitas Lambung Mangkurat');
        $cetakResponse->assertSee('memperoleh nilai akhir dengan kualifikasi');
        $cetakResponse->assertSee('Sangat Baik');
        $cetakResponse->assertDontSee('[A (Sangat Baik)]');
        $cetakResponse->assertSee('30 November 2026');
    }

    public function test_sertifikat_pdf_is_single_page(): void
    {
        [$internUser, $magang, $pembimbing] = $this->createInternUser();

        Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $pembimbing->id,
            'total_nilai' => 95,
        ]);

        $pdfResponse = $this->actingAs($internUser)->get(route('magang.sertifikat.cetak'));
        $pdfResponse->assertStatus(200);
        $content = $pdfResponse->getContent();
        $this->assertStringStartsWith('%PDF-', $content);

        // Memastikan PDF sertifikat hanya 1 halaman (tidak overflow ke halaman kedua)
        $pageMatches = preg_match_all('/\/Type\s*\/Page\b/', $content);
        $this->assertEquals(1, $pageMatches);
    }

    public function test_sertifikat_pdf_remains_single_page_with_long_participant_data(): void
    {
        [$internUser, $magang, $pembimbing] = $this->createInternUser();

        $magang->update([
            'nama_lengkap' => 'Muhammad Rizky Ramadhan Al-Fattah Ash-Shiddiq',
            'no_induk' => '20261100998877665544',
            'jurusan' => 'Pendidikan Teknik Informatika dan Komputer Terapan',
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat Banjarmasin Kalimantan Selatan',
        ]);

        Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $pembimbing->id,
            'total_nilai' => 90,
        ]);

        $pdfResponse = $this->actingAs($internUser)->get(route('magang.sertifikat.cetak'));
        $pdfResponse->assertStatus(200);
        $content = $pdfResponse->getContent();
        $this->assertStringStartsWith('%PDF-', $content);

        $pageMatches = preg_match_all('/\/Type\s*\/Page\b/', $content);
        $this->assertEquals(1, $pageMatches);
    }
}
