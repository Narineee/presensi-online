<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Models\RencanaKerja;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RencanaKerjaAktivitasBaruTest extends TestCase
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
                'nama_pimpinan' => 'Kadiv IT',
                'nip_pimpinan' => '197501012000031001',
                'jabatan_pimpinan' => 'Kadiv',
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
            'face_registered_at' => Carbon::now(),
        ]);

        return [$user, $magang];
    }

    public function test_intern_can_create_and_edit_rencana_kerja(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem1');
        [$user, $intern] = $this->createIntern($pembimbing, 'Peserta A', 'peserta_a');

        // 1. Create Rencana Kerja
        $response = $this->actingAs($user)->post(route('rencana.store'), [
            'judul' => 'Melayani pegawai',
            'deskripsi' => 'Membantu pelayanan administrasi kepada pegawai internal dinas',
        ]);

        $response->assertRedirect(route('rencana.index'));
        $this->assertDatabaseHas('rencana_kerja', [
            'magang_id' => $intern->id,
            'judul' => 'Melayani pegawai',
            'status' => 'aktif',
        ]);

        $rencana = RencanaKerja::where('magang_id', $intern->id)->first();

        // 2. Edit Rencana Kerja
        $updateResp = $this->actingAs($user)->put(route('rencana.update', $rencana->id), [
            'judul' => 'Melayani pegawai & publik',
            'deskripsi' => 'Diperluas untuk pelayanan publik',
            'status' => 'aktif',
        ]);

        $updateResp->assertRedirect(route('rencana.index'));
        $this->assertDatabaseHas('rencana_kerja', [
            'id' => $rencana->id,
            'judul' => 'Melayani pegawai & publik',
        ]);
    }

    public function test_intern_can_presensi_without_rencana_kerja(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem2');
        [$user, $intern] = $this->createIntern($pembimbing, 'Peserta B', 'peserta_b');

        // Peserta belum memiliki rencana kerja sama sekali
        $this->assertEquals(0, RencanaKerja::where('magang_id', $intern->id)->count());

        // Halaman presensi tetap dapat diakses tanpa hambatan
        $response = $this->actingAs($user)->get(route('presensi.index'));
        $response->assertStatus(200);
        $response->assertSee('Presensi Kehadiran');
    }

    public function test_intern_can_submit_aktivitas_with_multiple_rencana_kerja_and_distinct_progres(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem3');
        [$user, $intern] = $this->createIntern($pembimbing, 'Peserta C', 'peserta_c');

        $rk1 = RencanaKerja::create([
            'magang_id' => $intern->id,
            'judul' => 'Melayani pegawai',
            'status' => 'aktif',
        ]);

        $rk2 = RencanaKerja::create([
            'magang_id' => $intern->id,
            'judul' => 'Membuat desain UI',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->post(route('aktivitas.store'), [
            'tanggal' => '2026-10-13',
            'items' => [
                [
                    'rencana_kerja_id' => $rk1->id,
                    'progres' => 'Membantu pelayanan administrasi kepada lima pegawai dinas.',
                ],
                [
                    'rencana_kerja_id' => $rk2->id,
                    'progres' => 'Menyelesaikan rancangan halaman dashboard sebesar 60%.',
                ],
            ],
        ]);

        $response->assertRedirect(route('presensi.index'));

        $this->assertDatabaseHas('aktivitas', [
            'pengguna_id' => $user->id,
            'status' => 'pending',
        ]);

        $aktivitas = Aktivitas::where('pengguna_id', $user->id)->first();
        $this->assertNotNull($aktivitas);
        $this->assertEquals('2026-10-13', $aktivitas->tanggal->format('Y-m-d'));
        $this->assertCount(2, $aktivitas->details);

        $this->assertDatabaseHas('aktivitas_detail', [
            'aktivitas_id' => $aktivitas->id,
            'rencana_kerja_id' => $rk1->id,
            'rencana_kerja_snapshot' => 'Melayani pegawai',
        ]);

        $this->assertDatabaseHas('aktivitas_detail', [
            'aktivitas_id' => $aktivitas->id,
            'rencana_kerja_id' => $rk2->id,
            'rencana_kerja_snapshot' => 'Membuat desain UI',
        ]);
    }

    public function test_cannot_select_same_rencana_kerja_twice_in_single_submission(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem4');
        [$user, $intern] = $this->createIntern($pembimbing, 'Peserta D', 'peserta_d');

        $rk = RencanaKerja::create([
            'magang_id' => $intern->id,
            'judul' => 'Melayani pegawai',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->post(route('aktivitas.store'), [
            'tanggal' => '2026-10-13',
            'items' => [
                ['rencana_kerja_id' => $rk->id, 'progres' => 'Progres bagian pertama'],
                ['rencana_kerja_id' => $rk->id, 'progres' => 'Progres bagian kedua'],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Aktivitas::count());
    }

    public function test_cannot_select_rencana_kerja_belonging_to_another_intern(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem5');
        [$userA, $internA] = $this->createIntern($pembimbing, 'Peserta E1', 'peserta_e1');
        [$userB, $internB] = $this->createIntern($pembimbing, 'Peserta E2', 'peserta_e2');

        $rkMilikB = RencanaKerja::create([
            'magang_id' => $internB->id,
            'judul' => 'Rencana Rahasia B',
            'status' => 'aktif',
        ]);

        // User A mencoba menggunakan rencana kerja milik User B
        $response = $this->actingAs($userA)->post(route('aktivitas.store'), [
            'tanggal' => '2026-10-13',
            'items' => [
                ['rencana_kerja_id' => $rkMilikB->id, 'progres' => 'Mencoba mengisi punya orang'],
            ],
        ]);

        $response->assertSessionHasErrors(['items.0.rencana_kerja_id']);
        $this->assertEquals(0, Aktivitas::count());
    }

    public function test_same_rencana_kerja_can_be_used_on_different_dates(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem6');
        [$user, $intern] = $this->createIntern($pembimbing, 'Peserta F', 'peserta_f');

        $rk = RencanaKerja::create([
            'magang_id' => $intern->id,
            'judul' => 'Membantu administrasi',
            'status' => 'aktif',
        ]);

        // Hari 1: 13 Oktober 2026
        $this->actingAs($user)->post(route('aktivitas.store'), [
            'tanggal' => '2026-10-13',
            'items' => [
                ['rencana_kerja_id' => $rk->id, 'progres' => 'Merapikan arsip berkas pendaftaran.'],
            ],
        ]);

        // Hari 2: 14 Oktober 2026 (menggunakan rencana kerja yang sama)
        $this->actingAs($user)->post(route('aktivitas.store'), [
            'tanggal' => '2026-10-14',
            'items' => [
                ['rencana_kerja_id' => $rk->id, 'progres' => 'Memindai dokumen permohonan ke format PDF.'],
            ],
        ]);

        $this->assertEquals(2, Aktivitas::where('pengguna_id', $user->id)->count());
        $this->assertEquals(2, $rk->aktivitasDetails()->count());
    }

    public function test_rencana_with_activity_cannot_be_deleted(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem7');
        [$user, $intern] = $this->createIntern($pembimbing, 'Peserta G', 'peserta_g');

        $rk = RencanaKerja::create([
            'magang_id' => $intern->id,
            'judul' => 'Rencana dengan aktivitas',
            'status' => 'aktif',
        ]);

        $this->actingAs($user)->post(route('aktivitas.store'), [
            'tanggal' => '2026-10-13',
            'items' => [
                ['rencana_kerja_id' => $rk->id, 'progres' => 'Sudah ada catatan aktivitasnya.'],
            ],
        ]);

        // Mencoba menghapus rencana kerja yang sudah memiliki aktivitas
        $resp = $this->actingAs($user)->delete(route('rencana.destroy', $rk->id));
        $resp->assertSessionHas('error');

        // Pastikan rencana tidak terhapus
        $this->assertDatabaseHas('rencana_kerja', ['id' => $rk->id]);
    }

    public function test_pembimbing_can_view_intern_rencana_kerja_read_only(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem8');
        [$user, $intern] = $this->createIntern($pembimbing, 'Peserta H', 'peserta_h');

        RencanaKerja::create([
            'magang_id' => $intern->id,
            'judul' => 'Mendesain Poster Sosialisasi',
            'deskripsi' => 'Format A3 untuk papan pengumuman',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($pUser)->get(route('pembimbing.rencana.index'));
        $response->assertStatus(200);
        $response->assertSee('Mendesain Poster Sosialisasi');
        $response->assertSee('Peserta H');
    }

    public function test_pembimbing_can_approve_or_request_revision_on_activity(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem9');
        [$user, $intern] = $this->createIntern($pembimbing, 'Peserta I', 'peserta_i');

        $rk = RencanaKerja::create([
            'magang_id' => $intern->id,
            'judul' => 'Dokumentasi kegiatan',
            'status' => 'aktif',
        ]);

        $this->actingAs($user)->post(route('aktivitas.store'), [
            'tanggal' => '2026-10-13',
            'items' => [
                ['rencana_kerja_id' => $rk->id, 'progres' => 'Mengambil foto dokumentasi rapat koordinasi.'],
            ],
        ]);

        $aktivitas = Aktivitas::where('pengguna_id', $user->id)->first();

        // 1. Pembimbing meminta revisi
        $respRevisi = $this->actingAs($pUser)->post(route('pembimbing.aktivitas.validasi', $aktivitas->id), [
            'status' => 'revisi',
            'catatan_validasi' => 'Tolong lampirkan jumlah foto yang diunggah ke drive.',
        ]);

        $respRevisi->assertSessionHas('success');
        $aktivitas->refresh();
        $this->assertEquals('revisi', $aktivitas->status);
        $this->assertEquals('Tolong lampirkan jumlah foto yang diunggah ke drive.', $aktivitas->catatan_validasi);

        // 2. Pembimbing menyetujui aktivitas
        $respApprove = $this->actingAs($pUser)->post(route('pembimbing.aktivitas.validasi', $aktivitas->id), [
            'status' => 'approve',
        ]);

        $respApprove->assertSessionHas('success');
        $aktivitas->refresh();
        $this->assertEquals('approve', $aktivitas->status);
    }

    public function test_rencana_kerja_index_shows_back_button(): void
    {
        [$pUser, $pembimbing] = $this->createPembimbingWithUser('pem10');
        [$user, $intern] = $this->createIntern($pembimbing, 'Peserta J', 'peserta_j');

        $response = $this->actingAs($user)->get(route('rencana.index'));
        $response->assertStatus(200);
        $response->assertSee(route('presensi.index'));
        $response->assertSee('Kembali ke Presensi');
    }
}
