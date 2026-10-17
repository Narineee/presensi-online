<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PenempatanMagang;
use App\Models\Pengguna;
use Database\Seeders\DivisiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FloatingPesertaOtomatisUnitPembimbingTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $admin;

    protected Divisi $divisiAplikasi;

    protected Divisi $divisiKepegawaian;

    protected Pembimbing $pembimbingAplikasi;

    protected Pembimbing $pembimbingKepegawaian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'username' => 'admin_test',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->divisiAplikasi = Divisi::create([
            'nama_divisi' => 'Seksi Pengembangan Aplikasi',
            'nama_pimpinan' => 'Kasi Aplikasi',
            'nip_pimpinan' => '198001012005011001',
            'jabatan_pimpinan' => 'Kepala Seksi Pengembangan Aplikasi',
        ]);

        $this->divisiKepegawaian = Divisi::create([
            'nama_divisi' => 'Sub Bagian Umum & Kepegawaian',
            'nama_pimpinan' => 'Kasubbag Umum',
            'nip_pimpinan' => '198101012006011002',
            'jabatan_pimpinan' => 'Kepala Sub Bagian Umum & Kepegawaian',
        ]);

        $penggunaPem1 = Pengguna::create([
            'username' => 'pem_aplikasi',
            'password' => 'password123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $this->pembimbingAplikasi = Pembimbing::create([
            'pengguna_id' => $penggunaPem1->id,
            'divisi_id' => $this->divisiAplikasi->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Budi Santoso, S.Kom',
            'jabatan' => 'Senior Developer',
        ]);

        $penggunaPem2 = Pengguna::create([
            'username' => 'pem_kepegawaian',
            'password' => 'password123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $this->pembimbingKepegawaian = Pembimbing::create([
            'pengguna_id' => $penggunaPem2->id,
            'divisi_id' => $this->divisiKepegawaian->id,
            'nip' => '198601012011012001',
            'nama_lengkap' => 'Dewi Lestari, S.AP',
            'jabatan' => 'Analis Kepegawaian',
        ]);
    }

    public function test_divisi_seeder_seeds_five_bidang_and_fifteen_subbagian_seksi(): void
    {
        $this->seed(DivisiSeeder::class);

        // 5 Bidang Induk (parent_id = null)
        $expectedBidang = [
            'Sekretariat',
            'Bidang Informasi Publik & Statistik',
            'Bidang Komunikasi Publik',
            'Bidang Aplikasi Informatika',
            'Bidang Persandian & Keamanan Informasi',
        ];

        foreach ($expectedBidang as $namaBidang) {
            $this->assertDatabaseHas('divisi', [
                'nama_divisi' => $namaBidang,
                'parent_id' => null,
            ]);
        }

        // 15 Subbagian / Seksi berelasi dengan Bidang Induknya
        $expectedDivisi = [
            'Sub Bagian Umum & Kepegawaian',
            'Sub Bagian Keuangan & Aset',
            'Sub Bagian Perencanaan & Pelaporan',
            'Seksi Layanan Informasi Publik',
            'Seksi Pengelolaan Data Statistik',
            'Seksi Pengelolaan Informasi Publik & Media',
            'Seksi Kemitraan Komunikasi Publik & Hubungan Media',
            'Seksi Pengelolaan Opini Publik',
            'Seksi Sumber Daya Komunikasi Publik',
            'Seksi Pengembangan Aplikasi',
            'Seksi Tata Kelola Infrastruktur E-Goverment',
            'Seksi Tata Kelola & Ekosistem E-Goverment',
            'Seksi Pengembangan Kapasitas Tata Kelola Persandian & Keamanan Informasi',
            'Seksi Manajemen Integritas Data & Keamanan Informasi',
            'Seksi Monitoring Evaluasi & Audit Persandian & Keamanan Informasi',
        ];

        foreach ($expectedDivisi as $namaDivisi) {
            $unit = Divisi::where('nama_divisi', $namaDivisi)->first();
            $this->assertNotNull($unit);
            $this->assertNotNull($unit->parent_id);
            $this->assertNotNull($unit->parent);
            // Accessor bidang_induk dan pimpinan kepala bidang harus valid
            $this->assertEquals($unit->parent->nama_divisi, $unit->bidang_induk->nama_divisi);
            $this->assertEquals($unit->parent->nama_pimpinan, $unit->nama_kepala_bidang);
            $this->assertEquals($unit->parent->nip_pimpinan, $unit->nip_kepala_bidang);
        }
    }

    public function test_admin_can_create_divisi_with_parent_bidang(): void
    {
        $bidang = Divisi::create([
            'nama_divisi' => 'Bidang Teknologi Baru',
            'nama_pimpinan' => 'Dr. Kepala Bidang, M.Kom',
            'nip_pimpinan' => '197501012000011001',
            'jabatan_pimpinan' => 'Kepala Bidang Teknologi Baru',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.divisi.store'), [
            'nama_divisi' => 'Seksi AI & Machine Learning',
            'parent_id' => $bidang->id,
            'nama_pimpinan' => 'Kasi AI, S.Kom',
            'nip_pimpinan' => '198801012012011002',
            'jabatan_pimpinan' => 'Kepala Seksi AI',
        ]);

        $response->assertRedirect(route('admin.divisi.index'));

        $seksiBaru = Divisi::where('nama_divisi', 'Seksi AI & Machine Learning')->first();
        $this->assertNotNull($seksiBaru);
        $this->assertEquals($bidang->id, $seksiBaru->parent_id);
        $this->assertEquals('Dr. Kepala Bidang, M.Kom', $seksiBaru->nama_kepala_bidang);
    }

    public function test_admin_can_create_pembimbing_with_assigned_divisi(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.pembimbing.store'), [
            'nama_lengkap' => 'Eko Prasetyo, M.T.',
            'nip' => '199001012015011003',
            'divisi_id' => $this->divisiAplikasi->id,
            'jabatan' => 'Ahli Pertama',
            'no_hp' => '08123456789',
            'username' => 'eko_prasetyo',
            'password' => 'secret123',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.pembimbing.index'));

        $this->assertDatabaseHas('pembimbing', [
            'nama_lengkap' => 'Eko Prasetyo, M.T.',
            'nip' => '199001012015011003',
            'divisi_id' => $this->divisiAplikasi->id,
        ]);
    }

    public function test_admin_creates_magang_automatically_gets_divisi_from_selected_pembimbing(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.magang.store'), [
            'nama_lengkap' => 'Ahmad Magang',
            'no_induk' => '202611001',
            'pembimbing_id' => $this->pembimbingAplikasi->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonths(3)->toDateString(),
            'status' => 'aktif',
            'username' => 'ahmad_magang',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.magang.index'));

        $magang = Magang::where('nama_lengkap', 'Ahmad Magang')->first();
        $this->assertNotNull($magang);

        // Divisi magang otomatis mengikuti divisi pembimbing aplikasi
        $this->assertEquals($this->divisiAplikasi->id, $magang->divisi_id);

        // PenempatanMagang awal juga otomatis tercatat dengan divisi pembimbing
        $this->assertDatabaseHas('penempatan_magang', [
            'magang_id' => $magang->id,
            'pembimbing_id' => $this->pembimbingAplikasi->id,
            'divisi_id' => $this->divisiAplikasi->id,
        ]);
    }

    public function test_admin_creates_penempatan_rotation_automatically_inherits_divisi_from_selected_pembimbing(): void
    {
        // 1. Buat magang awal di unit aplikasi
        $penggunaMagang = Pengguna::create([
            'username' => 'magang_rotasi',
            'password' => 'password123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $penggunaMagang->id,
            'pembimbing_id' => $this->pembimbingAplikasi->id,
            'divisi_id' => $this->divisiAplikasi->id,
            'nama_lengkap' => 'Rina Rotasi',
            'no_induk' => '202611005',
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-06-30',
            'status' => 'aktif',
        ]);

        // Penempatan unit pertama
        PenempatanMagang::create([
            'magang_id' => $magang->id,
            'divisi_id' => $this->divisiAplikasi->id,
            'pembimbing_id' => $this->pembimbingAplikasi->id,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-03-31',
        ]);

        // 2. Admin floating / rotasi ke Pembimbing Kepegawaian tanpa perlu input divisi_id manual
        $response = $this->actingAs($this->admin)->post(route('admin.magang.penempatan.store', $magang->id), [
            'pembimbing_id' => $this->pembimbingKepegawaian->id,
            'tanggal_mulai' => '2027-04-01',
            'tanggal_selesai' => '2027-06-30',
        ]);

        $response->assertRedirect(route('admin.magang.show', ['magang' => $magang->id, 'tab' => 'penempatan']));

        // Penempatan baru otomatis tercatat pada divisi kepegawaian
        $penempatanBaru = PenempatanMagang::where('magang_id', $magang->id)
            ->where('pembimbing_id', $this->pembimbingKepegawaian->id)
            ->first();

        $this->assertNotNull($penempatanBaru);
        $this->assertEquals($this->divisiKepegawaian->id, $penempatanBaru->divisi_id);
        $this->assertEquals('2027-04-01', $penempatanBaru->tanggal_mulai->format('Y-m-d'));
        $this->assertEquals('2027-06-30', $penempatanBaru->tanggal_selesai->format('Y-m-d'));
    }
}
