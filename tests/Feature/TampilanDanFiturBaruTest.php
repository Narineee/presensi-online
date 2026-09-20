<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Cs;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TampilanDanFiturBaruTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_displays_official_notice_that_accounts_are_created_by_admin(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Seluruh akun login (Magang, CS, Pembimbing, dan Admin) dibuatkan dan diterbitkan langsung oleh');
        $response->assertSee('Administrator Sistem');
    }

    public function test_pembimbing_dashboard_displays_supervised_interns_and_cs(): void
    {
        $divisi = Divisi::create([
            'nama_divisi' => 'Teknologi Informasi',
            'deskripsi' => 'Divisi IT',
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing1',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Dr. H. Pembimbing Teladan',
            'jabatan' => 'Koordinator Lapangan',
            'no_hp' => '081234567890',
        ]);

        $userMagang = Pengguna::create([
            'username' => 'magang_ani',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => '2023001',
            'nama_lengkap' => 'Ani Lestari',
            'jurusan' => 'Informatika',
            'instansi_pendidikan' => 'Universitas Indonesia',
            'no_hp' => '08987654321',
            'tanggal_mulai' => Carbon::now()->subMonth(),
            'tanggal_selesai' => Carbon::now()->addMonth(),
            'status' => 'aktif',
        ]);

        $userCs = Pengguna::create([
            'username' => 'cs_budi',
            'password' => 'secret123',
            'role' => 'cs',
            'is_active' => true,
        ]);

        $cs = Cs::create([
            'pengguna_id' => $userCs->id,
            'pembimbing_id' => $pembimbing->id,
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Budi CS Santoso',
            'jabatan' => 'Petugas Front Office CS',
            'no_hp' => '081122334455',
            'tanggal_bergabung' => Carbon::now()->subMonths(3),
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Ani Lestari');
        $response->assertSee('2023001');
        $response->assertSee('Budi CS Santoso');
        $response->assertSee('3201010101010001');
        $response->assertSee('Pantau Kehadiran Binaan Hari Ini');
    }

    public function test_pembimbing_aktivitas_validation_screen_shows_participant_information(): void
    {
        $divisi = Divisi::create([
            'nama_divisi' => 'Pengembangan Sistem',
            'deskripsi' => 'Divisi Sistem',
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing2',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198501012010011002',
            'nama_lengkap' => 'Ibu Pembimbing Utama',
            'jabatan' => 'Supervisor',
            'no_hp' => '081234567891',
        ]);

        $userMagang = Pengguna::create([
            'username' => 'magang_citra',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => '2023002',
            'nama_lengkap' => 'Citra Handayani',
            'jurusan' => 'Sistem Informasi',
            'instansi_pendidikan' => 'UGM',
            'no_hp' => '08987654322',
            'tanggal_mulai' => Carbon::now()->subMonth(),
            'tanggal_selesai' => Carbon::now()->addMonth(),
            'status' => 'aktif',
        ]);

        Aktivitas::create([
            'pengguna_id' => $userMagang->id,
            'nama_lengkap' => $magang->nama_lengkap,
            'tanggal' => Carbon::today(),
            'isi' => 'Mengembangkan modul validasi presensi dan filter rekapan.',
            'progress' => 85,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($userPembimbing)->get(route('pembimbing.aktivitas.index'));

        $response->assertStatus(200);
        $response->assertSee('Citra Handayani');
        $response->assertSee('Mengembangkan modul validasi presensi');
        $response->assertSee('Foto &amp; Peserta Binaan', false);
    }

    public function test_magang_can_view_and_update_their_profile(): void
    {
        Storage::fake('public');

        $divisi = Divisi::create([
            'nama_divisi' => 'Hukum & Organisasi',
            'deskripsi' => 'Divisi Hukum',
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing3',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198501012010011003',
            'nama_lengkap' => 'Pak Pembimbing Tiga',
            'jabatan' => 'Pembimbing',
            'no_hp' => '081234567892',
        ]);

        $userMagang = Pengguna::create([
            'username' => 'magang_doni',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => '2023003',
            'nama_lengkap' => 'Doni Saputra',
            'jurusan' => null,
            'instansi_pendidikan' => null,
            'no_hp' => null,
            'tanggal_mulai' => Carbon::now()->subMonth(),
            'tanggal_selesai' => Carbon::now()->addMonth(),
            'status' => 'aktif',
        ]);

        // Cek halaman profil tampil
        $responseGet = $this->actingAs($userMagang)->get(route('profil.edit'));
        $responseGet->assertStatus(200);
        $responseGet->assertSee('Kelola Profil Akun');
        $responseGet->assertSee('Lengkapi Profil Anda Terlebih Dahulu');

        // Simpan update profil
        $file = UploadedFile::fake()->image('pasfoto.jpg', 300, 400);

        $responsePost = $this->actingAs($userMagang)->put(route('profil.update'), [
            'nama_lengkap' => 'Doni Saputra Pratama',
            'instansi_pendidikan' => 'Politeknik Negeri Banjarmasin',
            'jurusan' => 'Teknik Komputer',
            'no_hp' => '085299887766',
            'foto' => $file,
        ]);

        $responsePost->assertRedirect(route('profil.edit'));
        $responsePost->assertSessionHas('success');

        $magang->refresh();
        $this->assertEquals('Doni Saputra Pratama', $magang->nama_lengkap);
        $this->assertEquals('Politeknik Negeri Banjarmasin', $magang->instansi_pendidikan);
        $this->assertEquals('Teknik Komputer', $magang->jurusan);
        $this->assertEquals('085299887766', $magang->no_hp);
        $this->assertNotNull($magang->foto);
        Storage::disk('public')->assertExists($magang->foto);
    }
}
