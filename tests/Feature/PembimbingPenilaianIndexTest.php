<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use App\Models\Penilaian;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembimbingPenilaianIndexTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $userPembimbing;

    private Pembimbing $pembimbing;

    private Divisi $divisi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->divisi = Divisi::create([
            'nama_divisi' => 'Teknologi Informasi',
            'deskripsi' => 'Divisi IT',
        ]);

        $this->userPembimbing = Pengguna::create([
            'username' => 'pembimbing_test',
            'password' => bcrypt('password'),
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $this->pembimbing = Pembimbing::create([
            'pengguna_id' => $this->userPembimbing->id,
            'nip' => '198001012005011001',
            'nama_lengkap' => 'Bapak Pembimbing IT',
            'jabatan' => 'Senior Developer',
            'no_hp' => '081234567890',
        ]);

        KriteriaPenilaian::create([
            'nama' => 'Kedisiplinan',
            'bobot' => 20,
            'is_presensi' => true,
        ]);
    }

    public function test_pembimbing_can_view_penilaian_index_with_wireframe_components(): void
    {
        // 1. Magang perlu dinilai
        $userMagang1 = Pengguna::create([
            'username' => 'hasnia_magang',
            'password' => bcrypt('password'),
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang1 = Magang::create([
            'pengguna_id' => $userMagang1->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'nama_lengkap' => 'Hasnia',
            'no_induk' => 'MG001',
            'instansi_pendidikan' => 'SMK Telkom',
            'tanggal_mulai' => Carbon::parse('2026-09-30'),
            'tanggal_selesai' => Carbon::parse('2027-09-23'),
            'status' => 'aktif',
        ]);

        // 2. Magang sudah dinilai
        $userMagang2 = Pengguna::create([
            'username' => 'rahmah_magang',
            'password' => bcrypt('password'),
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang2 = Magang::create([
            'pengguna_id' => $userMagang2->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'nama_lengkap' => 'Rahmah',
            'no_induk' => 'MG002',
            'instansi_pendidikan' => 'SMK Telkom',
            'tanggal_mulai' => Carbon::parse('2026-09-30'),
            'tanggal_selesai' => Carbon::parse('2027-09-23'),
            'status' => 'aktif',
        ]);

        $penilaian = Penilaian::create([
            'magang_id' => $magang2->id,
            'pembimbing_id' => $this->pembimbing->id,
            'total_nilai' => 62,
        ]);

        $response = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.index'));

        $response->assertStatus(200);

        // Header & Wireframe elements
        $response->assertSee('Penilaian akhir magang');
        $response->assertSee('1 peserta perlu ditindaklanjuti');
        $response->assertSee('cari peserta');
        $response->assertSee('Terapkan Filter');
        $response->assertSee('Cetak Rekapitulasi');
        $response->assertSee('Skala contoh');

        // Section Perlu dinilai
        $response->assertSee('Perlu dinilai');
        $response->assertSee('Hasnia');
        $response->assertSee('Lanjutkan Penilaian');
        $response->assertSee('Belum dinilai');

        // Section Sudah dinilai
        $response->assertSee('Sudah dinilai');
        $response->assertSee('Rahmah');
        $response->assertSee('Nilai : 62/100');
        $response->assertSee('D'); // Predikat untuk nilai 62
        $response->assertSee(route('pembimbing.penilaian.show', $penilaian->id));
        $response->assertSee(route('pembimbing.penilaian.edit', $penilaian->id));
        $response->assertSee(route('pembimbing.penilaian.destroy', $penilaian->id));
    }

    public function test_pembimbing_can_filter_penilaian_by_status(): void
    {
        $userMagang1 = Pengguna::create([
            'username' => 'm1',
            'password' => bcrypt('password'),
            'role' => 'magang',
            'is_active' => true,
        ]);
        Magang::create([
            'pengguna_id' => $userMagang1->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'nama_lengkap' => 'Intern Belum Dinilai',
            'no_induk' => 'M01',
            'tanggal_mulai' => Carbon::parse('2026-09-01'),
            'tanggal_selesai' => Carbon::parse('2026-12-01'),
            'status' => 'aktif',
        ]);

        $userMagang2 = Pengguna::create([
            'username' => 'm2',
            'password' => bcrypt('password'),
            'role' => 'magang',
            'is_active' => true,
        ]);
        $magang2 = Magang::create([
            'pengguna_id' => $userMagang2->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'nama_lengkap' => 'Intern Sudah Dinilai',
            'no_induk' => 'M02',
            'tanggal_mulai' => Carbon::parse('2026-09-01'),
            'tanggal_selesai' => Carbon::parse('2026-12-01'),
            'status' => 'aktif',
        ]);
        Penilaian::create([
            'magang_id' => $magang2->id,
            'pembimbing_id' => $this->pembimbing->id,
            'total_nilai' => 85,
        ]);

        // Filter belum
        $respBelum = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.index', ['status_nilai' => 'belum']));
        $respBelum->assertSee('Intern Belum Dinilai');
        $respBelum->assertDontSee('Intern Sudah Dinilai');

        // Filter sudah
        $respSudah = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.index', ['status_nilai' => 'sudah']));
        $respSudah->assertSee('Intern Sudah Dinilai');
        $respSudah->assertDontSee('Intern Belum Dinilai');
    }

    public function test_pembimbing_can_open_cetak_rekapitulasi_penilaian(): void
    {
        $response = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.cetak'));

        $response->assertStatus(200);
        $response->assertSee('REKAPITULASI PENILAIAN AKHIR MAGANG BINAAN');
        $response->assertSee($this->pembimbing->nama_lengkap);
    }
}
