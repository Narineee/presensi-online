<?php

namespace Tests\Feature;

use App\Models\DetailPenilaian;
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
        // 1. Default request menghasilkan PDF stream langsung via mPDF
        $pdfResponse = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.cetak'));

        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        // 2. Format HTML untuk pratinjau / verifikasi template blade
        $response = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.cetak', ['format' => 'html']));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN PENILAIAN AKHIR MAGANG');
        $response->assertSee($this->pembimbing->nama_lengkap);
        $response->assertSee('Asal Lembaga');
        $response->assertDontSee('Divisi &amp; Asal Lembaga');
        $response->assertDontSee('Divisi & Asal Lembaga');
    }

    public function test_pembimbing_can_cetak_single_peserta_magang_sheet(): void
    {
        $userMagang = Pengguna::create([
            'username' => 'magang_single_cetak',
            'password' => bcrypt('password'),
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'nama_lengkap' => 'Budi Prakoso',
            'no_induk' => 'NIS12345',
            'instansi_pendidikan' => 'SMK Negeri 1 Martapura',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'tanggal_mulai' => Carbon::parse('2026-09-01'),
            'tanggal_selesai' => Carbon::parse('2026-12-01'),
            'status' => 'aktif',
        ]);

        $kriteria = KriteriaPenilaian::first();

        $penilaian = Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'total_nilai' => 92,
        ]);

        DetailPenilaian::create([
            'penilaian_id' => $penilaian->id,
            'kriteria_id' => $kriteria->id,
            'nilai' => 92,
        ]);

        // 1. PDF stream per peserta
        $pdfResponse = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.cetak', ['penilaian_id' => $penilaian->id]));

        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        // 2. HTML format per peserta
        $response = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.cetak', ['penilaian_id' => $penilaian->id, 'format' => 'html']));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN PENILAIAN AKHIR MAGANG');
        $response->assertSee('Budi Prakoso');
        $response->assertSee('NIS12345');
        $response->assertSee('SMK Negeri 1 Martapura');
        $response->assertSee('Unsur / Kriteria Penilaian');
        $response->assertSee('Nilai Angka');
        $response->assertSee('TOTAL NILAI AKHIR');
        $response->assertSee('PREDIKAT KELULUSAN');
        $response->assertSee('Standar Predikat:');
        $response->assertSee($this->pembimbing->nama_lengkap);
    }

    public function test_pembimbing_can_view_penilaian_create_page(): void
    {
        $userMagang = Pengguna::create([
            'username' => 'magang_create_test',
            'password' => bcrypt('password'),
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'nama_lengkap' => 'Peserta Uji Create',
            'no_induk' => 'MGC001',
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat',
            'tanggal_mulai' => Carbon::parse('2026-09-01'),
            'tanggal_selesai' => Carbon::parse('2026-12-01'),
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.create', ['magang_id' => $magang->id]));

        $response->assertStatus(200);
        $response->assertSee('Input Penilaian Magang');
        $response->assertSee('Peserta Uji Create');
        $response->assertSee('Kalkulasi Nilai Akhir');
        $response->assertSee('Form Skor Kriteria');
        $response->assertSee('Simpan Nilai');
    }

    public function test_pembimbing_can_view_penilaian_edit_and_show_pages(): void
    {
        $userMagang = Pengguna::create([
            'username' => 'magang_edit_test',
            'password' => bcrypt('password'),
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'nama_lengkap' => 'Peserta Uji Edit',
            'no_induk' => 'MGE001',
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat',
            'tanggal_mulai' => Carbon::parse('2026-09-01'),
            'tanggal_selesai' => Carbon::parse('2026-12-01'),
            'status' => 'aktif',
        ]);

        $penilaian = Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $this->pembimbing->id,
            'total_nilai' => 88,
        ]);

        // Edit page
        $responseEdit = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.edit', $penilaian->id));

        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Ubah Penilaian Magang');
        $responseEdit->assertSee('Peserta Uji Edit');
        $responseEdit->assertSee('Perbarui Nilai');

        // Show page
        $responseShow = $this->actingAs($this->userPembimbing)
            ->get(route('pembimbing.penilaian.show', $penilaian->id));

        $responseShow->assertStatus(200);
        $responseShow->assertSee('Laporan Penilaian Akhir Magang');
        $responseShow->assertSee('Peserta Uji Edit');
        $responseShow->assertSee('Cetak Laporan Penilaian (PDF)');
        $responseShow->assertSee(route('pembimbing.penilaian.cetak', ['penilaian_id' => $penilaian->id]));
        $responseShow->assertSee('LAPORAN PENILAIAN AKHIR MAGANG');
        $responseShow->assertSee('Unsur / Kriteria Penilaian');
        $responseShow->assertSee('Nilai Angka');
    }
}
