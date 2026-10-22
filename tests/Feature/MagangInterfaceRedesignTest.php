<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\RencanaKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MagangInterfaceRedesignTest extends TestCase
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
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
            'face_registered_at' => now(),
        ]);
    }

    public function test_magang_can_access_rekap_hub_and_sees_metrics(): void
    {
        $response = $this->actingAs($this->userMagang)->get(route('magang.rekap'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi &amp; Cetak Dokumen', false);
        $response->assertSee('Kehadiran');
        $response->assertSee('Jam Magang');
        $response->assertSee('Rekap Presensi');
        $response->assertSee('Rekap Aktivitas');
        $response->assertSee('Lembar nilai magang');
    }

    public function test_magang_bottom_bar_navigation_renders_5_core_tabs(): void
    {
        // Buat pengajuan izin disetujui untuk memastikan query total_izin dengan status_approval berjalan tanpa error
        PengajuanIzin::create([
            'pengguna_id' => $this->userMagang->id,
            'jenis_izin' => 'izin',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'alasan' => 'Urusan kampus',
            'status_approval' => 'disetujui',
            'validated_by' => $this->pembimbing->pengguna_id,
            'validated_at' => now(),
        ]);

        $response = $this->actingAs($this->userMagang)->get(route('presensi.index'));

        $response->assertStatus(200);
        $response->assertSee(route('presensi.index'));
        $response->assertSee(route('aktivitas.index'));
        $response->assertSee(route('izin.index'));
        $response->assertSee(route('magang.rekap'));
        $response->assertSee(route('profil.edit'));
        $response->assertSee('Hasnia');
        $response->assertSee('1 hari');
    }

    public function test_magang_can_submit_aktivitas_with_custom_judul(): void
    {
        $rk = RencanaKerja::create([
            'magang_id' => $this->magang->id,
            'judul' => 'Pengembangan Sistem Presensi',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->userMagang)->post(route('aktivitas.store'), [
            'tanggal' => '2026-10-01',
            'items' => [
                [
                    'rencana_kerja_id' => $rk->id,
                    'progres' => 'Selesai',
                ],
            ],
        ]);

        $response->assertRedirect(route('presensi.index'));

        $this->assertDatabaseHas('aktivitas', [
            'pengguna_id' => $this->userMagang->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('aktivitas_detail', [
            'rencana_kerja_id' => $rk->id,
            'rencana_kerja_snapshot' => 'Pengembangan Sistem Presensi',
            'progres' => 'Selesai',
        ]);
    }

    public function test_presensi_page_renders_liveness_modal_and_face_id_scripts(): void
    {
        $response = $this->actingAs($this->userMagang)->get(route('presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('modal-presensi-flow');
        $response->assertSee('btn-final-submit');
        $response->assertSee('btn-retake');
        $response->assertSee('input_face_descriptor');
        $response->assertSee('Pemeriksaan liveness');
        $response->assertSee('FaceID.descriptorFrom');
        $response->assertSee('resetLivenessVerification');
    }
}
