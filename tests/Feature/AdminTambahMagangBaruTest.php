<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PenempatanMagang;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTambahMagangBaruTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    private Pembimbing $pembimbing;

    private Divisi $divisi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'username' => 'admin_sistem',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing_budi',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $this->pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198501012010011002',
            'nama_lengkap' => 'Budi Santoso, S.Kom',
            'jabatan' => 'Pranata Komputer Ahli Muda',
            'no_hp' => '081234567890',
        ]);

        $this->divisi = Divisi::create([
            'nama_divisi' => 'Tata Kelola E-Government',
            'nama_pimpinan' => 'H. Pimpinan Dinas',
            'nip_pimpinan' => '197501012000011001',
            'jabatan_pimpinan' => 'Kepala Bidang E-Gov',
        ]);
    }

    public function test_admin_create_page_shows_simplified_input(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.magang.create'));

        $response->assertStatus(200);
        $response->assertSee('Data Pokok Peserta Magang');
        $response->assertSee('Nama Lengkap');
        $response->assertSee('Nomor Induk (NIM / NIS)');
        $response->assertSee('Penempatan Divisi');
        $response->assertSee('Periode Pelaksanaan');
        $response->assertSee('Akun Login Peserta Magang');

        // Form tidak lagi meminta input kampus/jurusan/no_hp dari admin
        $response->assertDontSee('name="instansi_pendidikan"', false);
        $response->assertDontSee('name="jurusan"', false);
        $response->assertDontSee('name="no_hp"', false);
    }

    public function test_admin_can_store_magang_with_only_nama_and_no_induk(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.magang.store'), [
            // Data pokok yang diinput admin
            'nama_lengkap' => 'Siti Nurhaliza',
            'no_induk' => '202611002',

            // Pembimbing & Divisi
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,

            // Periode magang & status
            'tanggal_mulai' => '2027-02-01',
            'tanggal_selesai' => '2027-07-31',
            'status' => 'aktif',

            // Akun login
            'username' => 'siti_magang',
            'password' => 'password123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.magang.index'));

        // Cek data tersimpan di tabel magang dengan sisa data kosong untuk diisi mandiri
        $magang = Magang::where('nama_lengkap', 'Siti Nurhaliza')->first();
        $this->assertNotNull($magang);
        $this->assertEquals('202611002', $magang->no_induk);
        $this->assertEquals($this->pembimbing->id, $magang->pembimbing_id);
        $this->assertEquals($this->divisi->id, $magang->divisi_id);
        $this->assertNull($magang->instansi_pendidikan);
        $this->assertNull($magang->jurusan);
        $this->assertNull($magang->no_hp);
        $this->assertNull($magang->foto);

        // Cek riwayat penempatan awal otomatis tercatat
        $penempatan = PenempatanMagang::where('magang_id', $magang->id)->first();
        $this->assertNotNull($penempatan);
        $this->assertEquals($this->divisi->id, $penempatan->divisi_id);
    }

    public function test_magang_user_can_subsequently_fill_their_own_profile(): void
    {
        // 1. Admin membuat peserta
        $this->actingAs($this->admin)->post(route('admin.magang.store'), [
            'nama_lengkap' => 'Ahmad Fauzi',
            'no_induk' => '202611003',
            'pembimbing_id' => $this->pembimbing->id,
            'divisi_id' => $this->divisi->id,
            'tanggal_mulai' => '2027-02-01',
            'tanggal_selesai' => '2027-07-31',
            'status' => 'aktif',
            'username' => 'ahmad_fauzi',
            'password' => 'password123',
            'is_active' => '1',
        ]);

        $userMagang = Pengguna::where('username', 'ahmad_fauzi')->first();
        $this->assertNotNull($userMagang);

        // 2. Peserta login dan melengkapi profilnya sendiri
        $response = $this->actingAs($userMagang)->put(route('profil.update'), [
            'nama_lengkap' => 'Ahmad Fauzi',
            'jenis_kelamin' => 'L',
            'instansi_pendidikan' => 'Universitas Lambung Mangkurat',
            'jurusan' => 'Teknologi Informasi',
            'no_hp' => '085299887766',
        ]);

        $response->assertRedirect(route('profil.edit'));

        $magang = $userMagang->fresh()->magang;
        $this->assertEquals('Universitas Lambung Mangkurat', $magang->instansi_pendidikan);
        $this->assertEquals('Teknologi Informasi', $magang->jurusan);
        $this->assertEquals('L', $magang->jenis_kelamin);
        $this->assertEquals('085299887766', $magang->no_hp);
    }
}
