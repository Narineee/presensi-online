<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RingkasanBulanPresensiMagangTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $userMagang;

    private Magang $magang;

    private Divisi $divisi;

    private Pembimbing $pembimbing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->divisi = Divisi::create([
            'nama_divisi' => 'Bidang Komunikasi Publik',
            'nama_pimpinan' => 'Drs. Pimpinan',
            'nip_pimpinan' => '197001011995031001',
            'jabatan_pimpinan' => 'Kepala Bidang',
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing_1',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $this->pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198001012005011001',
            'nama_lengkap' => 'Pembimbing Lapangan',
            'jabatan' => 'Pranata Komputer',
            'no_hp' => '08123456789',
        ]);

        $this->userMagang = Pengguna::create([
            'username' => 'hasnia_magang',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $this->magang = Magang::create([
            'pengguna_id' => $this->userMagang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'nama_lengkap' => 'Hasnia Putri',
            'no_induk' => '2026001',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
            'face_registered_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_ringkasan_bulan_displays_hadir_tepat_waktu_izin_sakit_dan_alfa(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-14 10:00:00'));

        $response = $this->actingAs($this->userMagang)->get(route('presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Oktober');
        $response->assertSee('Hadir');
        $response->assertSee('Tepat Waktu');
        $response->assertSee('Izin');
        $response->assertSee('Sakit');
        $response->assertSee('Alfa');
    }

    public function test_ringkasan_bulan_calculates_all_attendance_metrics_accurately(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-14 10:00:00'));

        // 1. Hadir Tepat Waktu (07:45)
        Presensi::create([
            'pengguna_id' => $this->userMagang->id,
            'tanggal' => '2026-10-01',
            'jam_masuk' => '07:45:00',
            'jam_keluar' => '16:05:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // 2. Hadir Tepat Waktu (07:55)
        Presensi::create([
            'pengguna_id' => $this->userMagang->id,
            'tanggal' => '2026-10-02',
            'jam_masuk' => '07:55:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // 3. Hadir Terlambat (08:20)
        Presensi::create([
            'pengguna_id' => $this->userMagang->id,
            'tanggal' => '2026-10-05',
            'jam_masuk' => '08:20:00',
            'jam_keluar' => '16:10:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // 4. Izin resmi 1 hari (2026-10-06)
        PengajuanIzin::create([
            'pengguna_id' => $this->userMagang->id,
            'jenis_izin' => 'izin',
            'tanggal_mulai' => '2026-10-06',
            'tanggal_selesai' => '2026-10-06',
            'alasan' => 'Urusan akademik kampus',
            'status_approval' => 'disetujui',
            'validated_by' => $this->pembimbing->pengguna_id,
            'validated_at' => '2026-10-06 08:00:00',
        ]);

        // 5. Sakit resmi 2 hari (2026-10-07 s/d 2026-10-08)
        PengajuanIzin::create([
            'pengguna_id' => $this->userMagang->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-10-07',
            'tanggal_selesai' => '2026-10-08',
            'alasan' => 'Demam dan flu',
            'status_approval' => 'disetujui',
            'validated_by' => $this->pembimbing->pengguna_id,
            'validated_at' => '2026-10-07 08:00:00',
        ]);

        // Hari kerja yang tidak dihadiri tanpa izin/sakit sampai 2026-10-13:
        // 2026-10-09 (Jumat), 2026-10-12 (Senin), 2026-10-13 (Selasa) => 3 hari Alfa.
        // Hari ini 2026-10-14 jam 10:00 (masih sebelum jam 16:00, jadi belum alfa).

        $response = $this->actingAs($this->userMagang)->get(route('presensi.index'));

        $response->assertStatus(200);

        // Verifikasi stats yang di-pass ke view
        $stats = $response->viewData('stats');
        $this->assertNotNull($stats);
        $this->assertEquals(3, $stats['total_hadir']);
        $this->assertEquals(2, $stats['tepat_waktu']);
        $this->assertEquals(1, $stats['total_izin']);
        $this->assertEquals(2, $stats['total_sakit']);
        $this->assertEquals(3, $stats['total_alfa']);
    }

    public function test_ringkasan_bulan_counts_explicit_alpa_presensi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-05 10:00:00'));

        Presensi::create([
            'pengguna_id' => $this->userMagang->id,
            'tanggal' => '2026-10-01',
            'status' => 'alpa',
            'mode_kerja' => 'onsite',
        ]);

        Presensi::create([
            'pengguna_id' => $this->userMagang->id,
            'tanggal' => '2026-10-02',
            'jam_masuk' => '07:45:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        $response = $this->actingAs($this->userMagang)->get(route('presensi.index'));

        $response->assertStatus(200);
        $stats = $response->viewData('stats');
        $this->assertEquals(1, $stats['total_hadir']);
        $this->assertEquals(1, $stats['tepat_waktu']);
        $this->assertEquals(1, $stats['total_alfa']);
    }
}
