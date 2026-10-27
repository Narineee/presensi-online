<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PenempatanMagang;
use App\Models\PengajuanIzin;
use App\Models\PengajuanTugasLuar;
use App\Models\Pengguna;
use App\Models\RencanaKerja;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerubahanPembimbingIsolasiRiwayatTest extends TestCase
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
            'no_induk' => 'NIM'.rand(1000, 9999),
            'nama_lengkap' => $nama,
            'instansi_pendidikan' => 'Universitas Indonesia',
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-31',
            'status' => 'aktif',
        ]);

        return [$user, $magang];
    }

    public function test_riwayat_validasi_aktivitas_lama_tidak_masuk_ke_pembimbing_baru(): void
    {
        $divisiA = Divisi::create(['nama_divisi' => 'Divisi TI']);
        $divisiB = Divisi::create(['nama_divisi' => 'Divisi Humas']);

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

        // Aktivitas saat masih di Unit A yang divalidasi oleh Mentor A
        $aktivitasA = Aktivitas::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-10',
            'judul' => 'Perbaikan bug login web dinas',
            'isi' => 'Perbaikan bug login web dinas',
            'progress' => 100,
            'status' => 'approve',
            'validated_by' => $userA->id,
            'validated_at' => '2026-03-10 16:30:00',
        ]);

        // Aktivitas di Unit B yang divalidasi oleh Mentor B
        $aktivitasB = Aktivitas::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-20',
            'judul' => 'Rilis press release kegiatan pemkot',
            'isi' => 'Rilis press release kegiatan pemkot',
            'progress' => 100,
            'status' => 'approve',
            'validated_by' => $userB->id,
            'validated_at' => '2026-03-20 16:30:00',
        ]);

        // Mentor A mengecek riwayat keputusan validasi: hanya melihat aktivitasA
        $responseA = $this->actingAs($userA)->get(route('pembimbing.aktivitas.index', ['status' => 'approve']));
        $responseA->assertOk();
        $responseA->assertSee('Perbaikan bug login web dinas');
        $responseA->assertDontSee('Rilis press release kegiatan pemkot');

        // Mentor B mengecek riwayat keputusan validasi: hanya melihat aktivitasB, tidak melihat aktivitasA
        $responseB = $this->actingAs($userB)->get(route('pembimbing.aktivitas.index', ['status' => 'approve']));
        $responseB->assertOk();
        $responseB->assertSee('Rilis press release kegiatan pemkot');
        $responseB->assertDontSee('Perbaikan bug login web dinas');
    }

    public function test_riwayat_rencana_kerja_lama_tidak_masuk_ke_pembimbing_baru(): void
    {
        $divisiA = Divisi::create(['nama_divisi' => 'Divisi TI']);
        $divisiB = Divisi::create(['nama_divisi' => 'Divisi Kepegawaian']);

        [$userA, $mentorA] = $this->createMentor('mentor_a2', 'Budi Santoso', '19800102');
        [$userB, $mentorB] = $this->createMentor('mentor_b2', 'Dewi Lestari', '19850203');

        [$userIntern, $intern] = $this->createIntern('intern_2', 'Rizky Pratama', $mentorA, $divisiA);

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

        // Rencana kerja dibuat di bawah Mentor A
        $rkA = RencanaKerja::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $mentorA->id,
            'judul' => 'Pengembangan Backend API Presensi',
            'deskripsi' => 'Modul REST API',
            'status' => 'aktif',
        ]);

        // Rencana kerja dibuat di bawah Mentor B
        $rkB = RencanaKerja::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $mentorB->id,
            'judul' => 'Digitalisasi Berkas Mutasi Pegawai',
            'deskripsi' => 'Pengarsipan berkas PDF',
            'status' => 'aktif',
        ]);

        // Mentor B monitoring rencana kerja: TIDAK boleh melihat rkA, HANYA rkB
        $respRencanaB = $this->actingAs($userB)->get(route('pembimbing.rencana.index'));
        $respRencanaB->assertOk();
        $respRencanaB->assertSee('Digitalisasi Berkas Mutasi Pegawai');
        $respRencanaB->assertDontSee('Pengembangan Backend API Presensi');

        // Mentor B mencoba akses detail rkA -> harus 404
        $respShowRkA = $this->actingAs($userB)->get(route('pembimbing.rencana.show', $rkA->id));
        $respShowRkA->assertNotFound();

        // Mentor A monitoring rencana kerja: HANYA melihat rkA
        $respRencanaA = $this->actingAs($userA)->get(route('pembimbing.rencana.index'));
        $respRencanaA->assertOk();
        $respRencanaA->assertSee('Pengembangan Backend API Presensi');
        $respRencanaA->assertDontSee('Digitalisasi Berkas Mutasi Pegawai');

        // Detail binaan pembimbing B: HANYA menampilkan rkB pada rencana kerja aktif
        $respBinaanB = $this->actingAs($userB)->get(route('pembimbing.binaan.show', $intern->id));
        $respBinaanB->assertOk();
        $respBinaanB->assertSee('Digitalisasi Berkas Mutasi Pegawai');
        $respBinaanB->assertDontSee('Pengembangan Backend API Presensi');
    }

    public function test_riwayat_validasi_izin_dan_tugas_luar_terisolasi_per_pembimbing(): void
    {
        $divisiA = Divisi::create(['nama_divisi' => 'Divisi TI']);
        $divisiB = Divisi::create(['nama_divisi' => 'Divisi Pengawasan']);

        [$userA, $mentorA] = $this->createMentor('mentor_a3', 'Budi Santoso', '19800103');
        [$userB, $mentorB] = $this->createMentor('mentor_b3', 'Dewi Lestari', '19850204');

        [$userIntern, $intern] = $this->createIntern('intern_3', 'Rizky Pratama', $mentorA, $divisiA);

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

        // Izin masa pembimbing A (disetujui pembimbing A)
        $izinA = PengajuanIzin::create([
            'pengguna_id' => $userIntern->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-03-05',
            'tanggal_selesai' => '2026-03-05',
            'alasan' => 'Demam tinggi unit TI',
            'status_approval' => 'disetujui',
            'validated_by' => $userA->id,
            'validated_at' => '2026-03-05 08:00:00',
        ]);

        // Izin masa pembimbing B (disetujui pembimbing B)
        $izinB = PengajuanIzin::create([
            'pengguna_id' => $userIntern->id,
            'jenis_izin' => 'izin',
            'tanggal_mulai' => '2026-03-22',
            'tanggal_selesai' => '2026-03-22',
            'alasan' => 'Keperluan keluarga unit Pengawasan',
            'status_approval' => 'disetujui',
            'validated_by' => $userB->id,
            'validated_at' => '2026-03-22 08:00:00',
        ]);

        // Tugas luar masa pembimbing A
        $tlA = PengajuanTugasLuar::create([
            'magang_id' => $intern->id,
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-08',
            'tujuan' => 'Instalasi Jaringan Kecamatan',
            'keperluan' => 'Pemasangan kabel FO',
            'status_verifikasi' => 'disetujui',
            'verified_by' => $userA->id,
            'verified_at' => '2026-03-08 09:00:00',
        ]);

        // Tugas luar masa pembimbing B
        $tlB = PengajuanTugasLuar::create([
            'magang_id' => $intern->id,
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-25',
            'tujuan' => 'Inspeksi Lapangan Pasar',
            'keperluan' => 'Monitoring pengawasan harga',
            'status_verifikasi' => 'disetujui',
            'verified_by' => $userB->id,
            'verified_at' => '2026-03-25 09:00:00',
        ]);

        // Pembimbing B mengecek riwayat keputusan izin: HANYA melihat izinB
        $respIzinB = $this->actingAs($userB)->get(route('pembimbing.izin.index', ['status' => 'disetujui']));
        $respIzinB->assertOk();
        $respIzinB->assertSee('Keperluan keluarga unit Pengawasan');
        $respIzinB->assertDontSee('Demam tinggi unit TI');

        // Pembimbing B mengecek riwayat keputusan tugas luar: HANYA melihat tlB
        $respTlB = $this->actingAs($userB)->get(route('pembimbing.tugas-luar.index', ['status' => 'disetujui']));
        $respTlB->assertOk();
        $respTlB->assertSee('Inspeksi Lapangan Pasar');
        $respTlB->assertDontSee('Instalasi Jaringan Kecamatan');

        // Pembimbing A mengecek riwayat keputusan izin & TL: HANYA melihat izinA dan tlA
        $respIzinA = $this->actingAs($userA)->get(route('pembimbing.izin.index', ['status' => 'disetujui']));
        $respIzinA->assertOk();
        $respIzinA->assertSee('Demam tinggi unit TI');
        $respIzinA->assertDontSee('Keperluan keluarga unit Pengawasan');

        $respTlA = $this->actingAs($userA)->get(route('pembimbing.tugas-luar.index', ['status' => 'disetujui']));
        $respTlA->assertOk();
        $respTlA->assertSee('Instalasi Jaringan Kecamatan');
        $respTlA->assertDontSee('Inspeksi Lapangan Pasar');
    }

    public function test_magang_form_aktivitas_hanya_menampilkan_rencana_kerja_pembimbing_aktif(): void
    {
        $divisiA = Divisi::create(['nama_divisi' => 'Divisi TI']);
        $divisiB = Divisi::create(['nama_divisi' => 'Divisi Humas']);

        [$userA, $mentorA] = $this->createMentor('mentor_a4', 'Budi Santoso', '19800104');
        [$userB, $mentorB] = $this->createMentor('mentor_b4', 'Dewi Lestari', '19850205');

        [$userIntern, $intern] = $this->createIntern('intern_4', 'Rizky Pratama', $mentorA, $divisiA);

        // Penempatan unit 1: 1 - 15 Maret 2026 dengan Mentor A
        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiA->id,
            'pembimbing_id' => $mentorA->id,
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-15',
            'status' => 'selesai',
        ]);

        // Penempatan unit 2 (rotasi aktif): mencakup hari ini dengan Mentor B
        $today = Carbon::today()->toDateString();
        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiB->id,
            'pembimbing_id' => $mentorB->id,
            'tanggal_mulai' => Carbon::today()->subDays(2)->toDateString(),
            'tanggal_selesai' => Carbon::today()->addDays(10)->toDateString(),
            'status' => 'aktif',
        ]);

        // Rencana kerja lama Mentor A
        RencanaKerja::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $mentorA->id,
            'judul' => 'Pemeliharaan Server Database',
            'status' => 'aktif',
        ]);

        // Rencana kerja baru Mentor B
        RencanaKerja::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $mentorB->id,
            'judul' => 'Pengelolaan Akun Media Sosial Dinas',
            'status' => 'aktif',
        ]);

        // Form aktivitas magang hari ini: hanya boleh melihat RK Mentor B
        $response = $this->actingAs($userIntern)->get(route('aktivitas.create'));
        $response->assertOk();
        $response->assertSee('Pengelolaan Akun Media Sosial Dinas');
        $response->assertDontSee('Pemeliharaan Server Database');
    }

    public function test_dashboard_pembimbing_baru_hanya_menampilkan_tindak_lanjut_dan_rencana_periodenya(): void
    {
        $divisiA = Divisi::create(['nama_divisi' => 'Divisi TI']);
        $divisiB = Divisi::create(['nama_divisi' => 'Divisi Humas']);

        [$userA, $mentorA] = $this->createMentor('mentor_a5', 'Budi Santoso', '19800105');
        [$userB, $mentorB] = $this->createMentor('mentor_b5', 'Dewi Lestari', '19850206');

        [$userIntern, $intern] = $this->createIntern('intern_5', 'Rizky Pratama', $mentorA, $divisiA);

        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiA->id,
            'pembimbing_id' => $mentorA->id,
            'tanggal_mulai' => '2026-03-01',
            'tanggal_selesai' => '2026-03-15',
            'status' => 'selesai',
        ]);

        $today = Carbon::today()->toDateString();
        PenempatanMagang::create([
            'magang_id' => $intern->id,
            'divisi_id' => $divisiB->id,
            'pembimbing_id' => $mentorB->id,
            'tanggal_mulai' => Carbon::today()->subDays(2)->toDateString(),
            'tanggal_selesai' => Carbon::today()->addDays(10)->toDateString(),
            'status' => 'aktif',
        ]);

        // Aktivitas pending masa Mentor A (tanggal 2026-03-10)
        Aktivitas::create([
            'pengguna_id' => $userIntern->id,
            'tanggal' => '2026-03-10',
            'judul' => 'Pending Aktivitas TI',
            'isi' => 'Pending Aktivitas TI',
            'status' => 'pending',
        ]);

        // Rencana kerja Mentor A
        RencanaKerja::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $mentorA->id,
            'judul' => 'Rencana Kerja Mentor A Saja',
            'status' => 'aktif',
        ]);

        // Rencana kerja Mentor B
        RencanaKerja::create([
            'magang_id' => $intern->id,
            'pembimbing_id' => $mentorB->id,
            'judul' => 'Rencana Kerja Khusus Mentor B',
            'status' => 'aktif',
        ]);

        // Dashboard Mentor B:
        // - Tidak ada notifikasi validasi aktivitas pending dari masa Mentor A (jumlah = 0)
        // - Rencana kerja terbaru hanya menampilkan milik Mentor B
        $respDashB = $this->actingAs($userB)->get(route('pembimbing.dashboard'));
        $respDashB->assertOk();
        $respDashB->assertSee('Rencana Kerja Khusus Mentor B');
        $respDashB->assertDontSee('Rencana Kerja Mentor A Saja');

        // Dashboard Mentor A:
        // - Memiliki 1 aktivitas pending untuk masa 2026-03-10
        // - Rencana kerja terbaru hanya menampilkan milik Mentor A
        $respDashA = $this->actingAs($userA)->get(route('pembimbing.dashboard'));
        $respDashA->assertOk();
        $respDashA->assertSee('Rencana Kerja Mentor A Saja');
        $respDashA->assertDontSee('Rencana Kerja Khusus Mentor B');
    }
}
