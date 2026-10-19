<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\AktivitasDetail;
use App\Models\DetailPenilaian;
use App\Models\Divisi;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PenempatanMagang;
use App\Models\Pengguna;
use App\Models\Penilaian;
use App\Models\Presensi;
use App\Models\RencanaKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapPembimbingPeriodeTest extends TestCase
{
    use RefreshDatabase;

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

    private function createAdmin(): Pengguna
    {
        return Pengguna::create([
            'username' => 'admin_test',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createIntern(string $username, string $nama, Pembimbing $initialMentor, Divisi $divisi): array
    {
        $user = Pengguna::create([
            'username' => $username,
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'divisi_id' => $divisi->id,
            'pembimbing_id' => $initialMentor->id,
            'no_induk' => 'NIM12345',
            'nama_lengkap' => $nama,
            'instansi_pendidikan' => 'Universitas Indonesia',
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-31',
            'status' => 'aktif',
        ]);

        return [$user, $magang];
    }

    public function test_pembimbing_rekap_presensi_only_includes_dates_within_placement_period(): void
    {
        $divisiA = Divisi::create(['nama_divisi' => 'Divisi TI']);
        $divisiB = Divisi::create(['nama_divisi' => 'Divisi HR']);

        [$userA, $mentorA] = $this->createMentor('mentor_a', 'Budi Santoso', '19800101');
        [$userB, $mentorB] = $this->createMentor('mentor_b', 'Dewi Lestari', '19850202');

        [$userIntern, $intern] = $this->createIntern('intern_1', 'Rizky Pratama', $mentorA, $divisiA);

        // Penempatan unit 1: 1 - 15 Maret 2026 dengan Mentor A
        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiA->id,
            'pembimbing_id' => $mentorA->id,
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-15',
            'status' => 'selesai',
        ]);

        // Penempatan unit 2 (rotasi): 16 - 31 Maret 2026 dengan Mentor B
        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiB->id,
            'pembimbing_id' => $mentorB->id,
            'tanggal_mulai' => '2026-03-16',
            'tanggal_selesai' => '2026-03-31',
            'status' => 'aktif',
        ]);

        // Presensi saat masih di Unit A
        $presensiA = Presensi::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-10',
            'jam_masuk' => '07:55:00',
            'jam_keluar' => '17:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // Presensi setelah pindah ke Unit B
        $presensiB = Presensi::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-20',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '17:05:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // Mentor A index & cetak: hanya boleh melihat presensiA, tidak boleh melihat presensiB
        $responseA = $this->actingAs($userA)->get(route('pembimbing.presensi.index'));
        $responseA->assertOk();
        $responseA->assertSee('10 Mar 2026');
        $responseA->assertDontSee('20 Mar 2026');

        $responseACetak = $this->actingAs($userA)->get(route('pembimbing.presensi.cetak', [
            'tanggal_mulai' => '2026-03-01',
            'tanggal_akhir' => '2026-03-31',
            'format' => 'html',
        ]));
        $responseACetak->assertOk();
        $responseACetak->assertSee('LAPORAN PRESENSI MAGANG');
        $responseACetak->assertSee('1 Mar 2026 - 15 Mar 2026');
        $responseACetak->assertDontSee('16 Mar 2026 - 31 Mar 2026');

        // Mentor B index & cetak: hanya boleh melihat presensiB, tidak boleh melihat presensiA
        $responseB = $this->actingAs($userB)->get(route('pembimbing.presensi.index'));
        $responseB->assertOk();
        $responseB->assertSee('20 Mar 2026');
        $responseB->assertDontSee('10 Mar 2026');

        $responseBCetak = $this->actingAs($userB)->get(route('pembimbing.presensi.cetak', [
            'tanggal_mulai' => '2026-03-01',
            'tanggal_akhir' => '2026-03-31',
            'format' => 'html',
        ]));
        $responseBCetak->assertOk();
        $responseBCetak->assertSee('LAPORAN PRESENSI MAGANG');
        $responseBCetak->assertSee('16 Mar 2026 - 31 Mar 2026');
        $responseBCetak->assertDontSee('1 Mar 2026 - 15 Mar 2026');
    }

    public function test_pembimbing_rekap_aktivitas_only_includes_dates_within_placement_period(): void
    {
        $divisiA = Divisi::create(['nama_divisi' => 'Divisi TI']);
        $divisiB = Divisi::create(['nama_divisi' => 'Divisi HR']);

        [$userA, $mentorA] = $this->createMentor('mentor_a', 'Budi Santoso', '19800101');
        [$userB, $mentorB] = $this->createMentor('mentor_b', 'Dewi Lestari', '19850202');

        [$userIntern, $intern] = $this->createIntern('intern_1', 'Rizky Pratama', $mentorA, $divisiA);

        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiA->id,
            'pembimbing_id' => $mentorA->id,
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-15',
            'status' => 'selesai',
        ]);

        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiB->id,
            'pembimbing_id' => $mentorB->id,
            'tanggal_mulai' => '2026-03-16',
            'tanggal_selesai' => '2026-03-31',
            'status' => 'aktif',
        ]);

        $rencanaA = RencanaKerja::create([
            'magang_id' => $intern->id,
            'judul' => 'Pembuatan Modul Auth',
            'status' => 'aktif',
        ]);

        $rencanaB = RencanaKerja::create([
            'magang_id' => $intern->id,
            'judul' => 'Penyusunan SOP Karyawan',
            'status' => 'aktif',
        ]);

        // Aktivitas saat masih di Unit A
        $aktivitasA = Aktivitas::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-10',
            'isi' => 'Mengembangkan fitur login',
            'progress' => 50,
            'status' => 'pending',
        ]);
        AktivitasDetail::create([
            'aktivitas_id' => $aktivitasA->id,
            'rencana_kerja_id' => $rencanaA->id,
            'progres' => 'Coding autentikasi session',
        ]);

        // Aktivitas setelah pindah ke Unit B
        $aktivitasB = Aktivitas::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-20',
            'isi' => 'Menyusun draf SOP cuti',
            'progress' => 30,
            'status' => 'pending',
        ]);
        AktivitasDetail::create([
            'aktivitas_id' => $aktivitasB->id,
            'rencana_kerja_id' => $rencanaB->id,
            'progres' => 'Wawancara dengan HR staff',
        ]);

        // Mentor A index & cetak: hanya boleh melihat aktivitasA
        $responseA = $this->actingAs($userA)->get(route('pembimbing.aktivitas.index'));
        $responseA->assertOk();
        $responseA->assertSee('Coding autentikasi session');
        $responseA->assertDontSee('Wawancara dengan HR staff');

        $responseACetak = $this->actingAs($userA)->get(route('pembimbing.aktivitas.cetak', [
            'tanggal_mulai' => '2026-03-01',
            'tanggal_akhir' => '2026-03-31',
            'magang_id' => $intern->id,
            'format' => 'html',
        ]));
        $responseACetak->assertOk();
        $responseACetak->assertSee('Pembuatan Modul Auth');
        $responseACetak->assertSee('Coding autentikasi session');
        $responseACetak->assertDontSee('Penyusunan SOP Karyawan');

        // Mentor A mencoba memvalidasi aktivitas B (setelah rotasi) -> harus 404
        $responseVal = $this->actingAs($userA)->post(route('pembimbing.aktivitas.validasi', $aktivitasB->id), [
            'status' => 'approve',
        ]);
        $responseVal->assertNotFound();

        // Mentor B index & cetak: hanya boleh melihat aktivitasB
        $responseB = $this->actingAs($userB)->get(route('pembimbing.aktivitas.index'));
        $responseB->assertOk();
        $responseB->assertSee('Wawancara dengan HR staff');
        $responseB->assertDontSee('Coding autentikasi session');

        $responseBCetak = $this->actingAs($userB)->get(route('pembimbing.aktivitas.cetak', [
            'tanggal_mulai' => '2026-03-01',
            'tanggal_akhir' => '2026-03-31',
            'magang_id' => $intern->id,
            'format' => 'html',
        ]));
        $responseBCetak->assertOk();
        $responseBCetak->assertSee('Penyusunan SOP Karyawan');
        $responseBCetak->assertSee('Wawancara dengan HR staff');
        $responseBCetak->assertDontSee('Pembuatan Modul Auth');
    }

    public function test_cetak_penilaian_pembimbing_renders_criteria_breakdown_columns_and_legend(): void
    {
        $divisi = Divisi::create(['nama_divisi' => 'Divisi TI']);
        [$userMentor, $mentor] = $this->createMentor('mentor_c', 'Hendra Wijaya', '19790303');
        [$userIntern, $intern] = $this->createIntern('intern_c', 'Siti Rahma', $mentor, $divisi);

        $k1 = KriteriaPenilaian::create([
            'nama' => 'Presensi Kehadiran',
            'deskripsi' => 'Ketepatan hadir',
            'bobot' => 50,
            'is_presensi' => true,
        ]);
        $k2 = KriteriaPenilaian::create([
            'nama' => 'Inisiatif Kerja',
            'deskripsi' => 'Kreativitas dan inisiatif',
            'bobot' => 50,
            'is_presensi' => false,
        ]);

        $penilaian = Penilaian::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $mentor->id,
            'total_nilai' => 88,
        ]);

        DetailPenilaian::create([
            'penilaian_id' => $penilaian->id,
            'kriteria_id' => $k1->id,
            'nilai' => 90,
        ]);
        DetailPenilaian::create([
            'penilaian_id' => $penilaian->id,
            'kriteria_id' => $k2->id,
            'nilai' => 86,
        ]);

        $response = $this->actingAs($userMentor)->get(route('pembimbing.penilaian.cetak', ['format' => 'html']));
        $response->assertOk();
        // Kolom A & B
        $response->assertSee('A');
        $response->assertSee('B');
        $response->assertSee('90');
        $response->assertSee('86');
        // Legend rincian penilaian
        $response->assertSee('Rincian / Keterangan Unsur Penilaian:');
        $response->assertSee('Presensi Kehadiran');
        $response->assertSee('Inisiatif Kerja');
    }

    public function test_admin_rekap_displays_pembimbing_lapangan_on_each_date(): void
    {
        $divisiA = Divisi::create(['nama_divisi' => 'Divisi TI']);
        $divisiB = Divisi::create(['nama_divisi' => 'Divisi HR']);

        [$userA, $mentorA] = $this->createMentor('mentor_d1', 'Ahmad Pembina', '19810101');
        [$userB, $mentorB] = $this->createMentor('mentor_d2', 'Rina Pembina', '19860202');

        [$userIntern, $intern] = $this->createIntern('intern_d', 'Bagus Ramadhan', $mentorA, $divisiA);

        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiA->id,
            'pembimbing_id' => $mentorA->id,
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-15',
            'status' => 'selesai',
        ]);

        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiB->id,
            'pembimbing_id' => $mentorB->id,
            'tanggal_mulai' => '2026-03-16',
            'tanggal_selesai' => '2026-03-31',
            'status' => 'aktif',
        ]);

        // Presensi di 2 periode
        Presensi::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-10',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '17:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        Presensi::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-20',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '17:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // Aktivitas di 2 periode
        Aktivitas::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-10',
            'isi' => 'Tugas TI',
            'progress' => 100,
            'status' => 'approve',
        ]);

        Aktivitas::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-20',
            'isi' => 'Tugas HR',
            'progress' => 100,
            'status' => 'approve',
        ]);

        $admin = $this->createAdmin();

        // Admin Presensi Cetak
        $respPresensi = $this->actingAs($admin)->get(route('admin.presensi.cetak', [
            'tanggal_mulai' => '2026-03-01',
            'tanggal_akhir' => '2026-03-31',
            'format' => 'html',
        ]));
        $respPresensi->assertOk();
        $respPresensi->assertSee('Ahmad Pembina');
        $respPresensi->assertSee('Rina Pembina');

        // Admin Aktivitas Cetak
        $respAktivitas = $this->actingAs($admin)->get(route('admin.aktivitas.cetak', [
            'tanggal_mulai' => '2026-03-01',
            'tanggal_akhir' => '2026-03-31',
            'format' => 'html',
        ]));
        $respAktivitas->assertOk();
        $respAktivitas->assertSee('Ahmad Pembina');
        $respAktivitas->assertSee('Rina Pembina');
    }
}
