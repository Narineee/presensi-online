<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pekerjaan;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PekerjaanAktivitasTest extends TestCase
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

    public function test_pembimbing_can_create_pekerjaan_for_supervised_intern(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_a');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Hasnia', 'hasnia');

        $response = $this->actingAs($pembimbingUser)
            ->post(route('pembimbing.pekerjaan.store'), [
                'magang_id' => $intern->id,
                'judul' => 'Membuat Aplikasi Presensi Magang',
                'deskripsi' => 'Membuat aplikasi presensi dan aktivitas untuk peserta magang.',
                'jenis' => 'proyek',
                'progress' => 0,
                'tanggal_mulai' => '2026-09-29',
                'target_selesai' => '2026-10-10',
            ]);

        $response->assertRedirect(route('pembimbing.pekerjaan.index', ['magang_id' => $intern->id]));
        $this->assertDatabaseHas('pekerjaan', [
            'magang_id' => $intern->id,
            'pembimbing_id' => $pembimbing->id,
            'judul' => 'Membuat Aplikasi Presensi Magang',
            'jenis' => 'proyek',
            'progress' => 0,
            'status' => 'aktif',
        ]);
    }

    public function test_pembimbing_cannot_create_pekerjaan_for_intern_of_another_pembimbing(): void
    {
        [$pembimbingUserA, $pembimbingA] = $this->createPembimbingWithUser('pembimbing_a');
        [$pembimbingUserB, $pembimbingB] = $this->createPembimbingWithUser('pembimbing_b');
        [$internUserB, $internB] = $this->createIntern($pembimbingB, 'Budi', 'budi');

        $response = $this->actingAs($pembimbingUserA)
            ->post(route('pembimbing.pekerjaan.store'), [
                'magang_id' => $internB->id,
                'judul' => 'Tugas Ilegal',
                'jenis' => 'proyek',
            ]);

        $response->assertSessionHasErrors('magang_id');
        $this->assertDatabaseMissing('pekerjaan', [
            'judul' => 'Tugas Ilegal',
        ]);
    }

    public function test_peserta_only_sees_their_own_active_pekerjaan_and_submits_aktivitas_without_progress_input(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_a');
        [$userHasnia, $hasnia] = $this->createIntern($pembimbing, 'Hasnia', 'hasnia');
        [$userSiti, $siti] = $this->createIntern($pembimbing, 'Siti', 'siti');

        // Pekerjaan milik Hasnia
        $pekerjaanHasnia = Pekerjaan::create([
            'magang_id' => $hasnia->id,
            'pembimbing_id' => $pembimbing->id,
            'judul' => 'Aplikasi Presensi',
            'jenis' => 'proyek',
            'progress' => 20,
            'status' => 'aktif',
        ]);

        // Pekerjaan milik Siti
        $pekerjaanSiti = Pekerjaan::create([
            'magang_id' => $siti->id,
            'pembimbing_id' => $pembimbing->id,
            'judul' => 'Membuat Video Profil',
            'jenis' => 'proyek',
            'progress' => 10,
            'status' => 'aktif',
        ]);

        // Hasnia membuka form create aktivitas
        $createPage = $this->actingAs($userHasnia)->get(route('aktivitas.create'));
        $createPage->assertOk();
        $createPage->assertSee('Aplikasi Presensi');
        $createPage->assertDontSee('Membuat Video Profil');

        // Hasnia submit aktivitas
        $response = $this->actingAs($userHasnia)->post(route('aktivitas.store'), [
            'pekerjaan_id' => $pekerjaanHasnia->id,
            'tanggal' => '2026-09-30',
            'isi' => 'Mengerjakan validasi form login dan dashboard.',
        ]);

        $response->assertRedirect(route('aktivitas.index'));
        $this->assertDatabaseHas('aktivitas', [
            'pengguna_id' => $userHasnia->id,
            'pekerjaan_id' => $pekerjaanHasnia->id,
            'status' => 'pending',
            'isi' => 'Mengerjakan validasi form login dan dashboard.',
        ]);

        // Hasnia mencoba submit aktivitas menggunakan pekerjaan milik Siti (manipulasi ID) -> ditolak
        $responseForbidden = $this->actingAs($userHasnia)->post(route('aktivitas.store'), [
            'pekerjaan_id' => $pekerjaanSiti->id,
            'tanggal' => '2026-09-30',
            'isi' => 'Mencoba submit pekerjaan orang lain.',
        ]);
        $responseForbidden->assertSessionHasErrors('pekerjaan_id');
    }

    public function test_pembimbing_can_approve_proyek_and_update_progress(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_a');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Hasnia', 'hasnia');

        $pekerjaan = Pekerjaan::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $pembimbing->id,
            'judul' => 'Membuat Aplikasi Presensi',
            'jenis' => 'proyek',
            'progress' => 40,
            'status' => 'aktif',
        ]);

        $aktivitas = Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'pekerjaan_id' => $pekerjaan->id,
            'tanggal' => '2026-09-30',
            'isi' => 'Membuat fitur validasi lokasi GPS.',
            'progress' => 40,
            'status' => 'pending',
        ]);

        // Pembimbing approve dan update progress dari 40% ke 50%
        $response = $this->actingAs($pembimbingUser)
            ->post(route('pembimbing.aktivitas.validasi', $aktivitas->id), [
                'status' => 'approve',
                'progress' => 50,
                'catatan_validasi' => 'Validasi lokasi sudah sesuai, lanjutkan pengujian.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aktivitas', [
            'id' => $aktivitas->id,
            'status' => 'approve',
            'progress' => 50,
            'catatan_validasi' => 'Validasi lokasi sudah sesuai, lanjutkan pengujian.',
        ]);

        $pekerjaan->refresh();
        $this->assertEquals(50, $pekerjaan->progress);
        $this->assertEquals('aktif', $pekerjaan->status);

        // Jika progress di-update ke 100%, status pekerjaan otomatis menjadi 'selesai'
        $aktivitas2 = Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'pekerjaan_id' => $pekerjaan->id,
            'tanggal' => '2026-10-01',
            'isi' => 'Testing selesai dan deploy ke server.',
            'status' => 'pending',
        ]);

        $this->actingAs($pembimbingUser)
            ->post(route('pembimbing.aktivitas.validasi', $aktivitas2->id), [
                'status' => 'approve',
                'progress' => 100,
            ]);

        $pekerjaan->refresh();
        $this->assertEquals(100, $pekerjaan->progress);
        $this->assertEquals('selesai', $pekerjaan->status);
    }

    public function test_pembimbing_can_approve_rutin_without_progress(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_a');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Hasnia', 'hasnia');

        $pekerjaanRutin = Pekerjaan::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $pembimbing->id,
            'judul' => 'Melayani Tamu',
            'jenis' => 'rutin',
            'progress' => null,
            'status' => 'aktif',
        ]);

        $aktivitas = Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'pekerjaan_id' => $pekerjaanRutin->id,
            'tanggal' => '2026-09-30',
            'isi' => 'Melayani tamu dinas untuk administrasi persuratan.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($pembimbingUser)
            ->post(route('pembimbing.aktivitas.validasi', $aktivitas->id), [
                'status' => 'approve',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aktivitas', [
            'id' => $aktivitas->id,
            'status' => 'approve',
        ]);

        $pekerjaanRutin->refresh();
        $this->assertNull($pekerjaanRutin->progress);
        $this->assertEquals('aktif', $pekerjaanRutin->status);
    }

    public function test_pembimbing_revision_requires_note_and_does_not_change_progress(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_a');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Hasnia', 'hasnia');

        $pekerjaan = Pekerjaan::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $pembimbing->id,
            'judul' => 'Membuat Aplikasi Presensi',
            'jenis' => 'proyek',
            'progress' => 40,
            'status' => 'aktif',
        ]);

        $aktivitas = Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'pekerjaan_id' => $pekerjaan->id,
            'tanggal' => '2026-09-30',
            'isi' => 'Data deskripsi kurang lengkap.',
            'progress' => 40,
            'status' => 'pending',
        ]);

        // Revisi tanpa catatan -> gagal validasi
        $failResponse = $this->actingAs($pembimbingUser)
            ->post(route('pembimbing.aktivitas.validasi', $aktivitas->id), [
                'status' => 'revisi',
                'catatan_validasi' => '',
            ]);
        $failResponse->assertSessionHasErrors('catatan_validasi');

        // Revisi dengan catatan -> berhasil, status jadi revisi, progres tidak bertambah
        $successResponse = $this->actingAs($pembimbingUser)
            ->post(route('pembimbing.aktivitas.validasi', $aktivitas->id), [
                'status' => 'revisi',
                'catatan_validasi' => 'Data yang dimasukkan belum lengkap, silakan lengkapi.',
            ]);

        $successResponse->assertRedirect();
        $this->assertDatabaseHas('aktivitas', [
            'id' => $aktivitas->id,
            'status' => 'revisi',
            'catatan_validasi' => 'Data yang dimasukkan belum lengkap, silakan lengkapi.',
        ]);

        $pekerjaan->refresh();
        $this->assertEquals(40, $pekerjaan->progress);
    }
}
