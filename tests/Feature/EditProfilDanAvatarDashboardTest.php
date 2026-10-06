<?php

namespace Tests\Feature;

use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditProfilDanAvatarDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Pengguna $userMagang;

    protected Magang $magang;

    protected function setUp(): void
    {
        parent::setUp();

        $pembimbingUser = Pengguna::create([
            'username' => 'pembimbing_1',
            'password' => bcrypt('password123'),
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $pembimbingUser->id,
            'nip' => '198801012015011002',
            'nama_lengkap' => 'Pembimbing Lapangan Uji',
        ]);

        $this->userMagang = Pengguna::create([
            'username' => 'hasnia_intern',
            'password' => bcrypt('password123'),
            'role' => 'magang',
            'is_active' => true,
        ]);

        $this->magang = Magang::create([
            'pengguna_id' => $this->userMagang->id,
            'pembimbing_id' => $pembimbing->id,
            'nama_lengkap' => 'Hasnia Nur',
            'no_induk' => '202611005',
            'jenis_kelamin' => 'P',
            'instansi_pendidikan' => 'Politeknik Negeri Banjarmasin',
            'jurusan' => 'Teknik Informatika',
            'no_hp' => '081234567890',
            'foto' => null,
            'tanggal_mulai' => now()->subMonth(),
            'tanggal_selesai' => now()->addMonth(),
            'status' => 'aktif',
            'face_registered_at' => now(),
        ]);
    }

    public function test_dashboard_renders_initials_when_no_foto(): void
    {
        $response = $this->actingAs($this->userMagang)->get(route('presensi.index'));

        $response->assertStatus(200);
        // Hasnia Nur initials are HN
        $response->assertSee('HN');
        $response->assertSee(route('profil.edit'));
    }

    public function test_edit_profil_view_renders_wireframe_components_and_logout_button(): void
    {
        $response = $this->actingAs($this->userMagang)->get(route('profil.edit'));

        $response->assertStatus(200);
        $response->assertSee('Edit Profil');
        $response->assertSee('Perbarui informasi pribadi');
        $response->assertSee('PASFOTO RESMI PESERTA');
        $response->assertSee('Choose File');
        $response->assertSee('Disarankan pasfoto format setengah badan dengan latar rapi');
        $response->assertSee('NAMA LENGKAP');
        $response->assertSee('ASAL KAMPUS / SEKOLAH');
        $response->assertSee('PROGRAM STUDI / JURUSAN');
        $response->assertSee('NOMOR HP / WHATSAPP');
        $response->assertSee('JENIS KELAMIN');
        $response->assertSee('Simpan Perubahan');
        $response->assertSee('Keluar dari Akun (Logout)');
        $response->assertSee(route('logout'));
    }

    public function test_magang_can_upload_profile_photo_and_see_it_on_dashboard(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('hasnia.png', 400, 400);

        $updateResponse = $this->actingAs($this->userMagang)->put(route('profil.update'), [
            'nama_lengkap' => 'Hasnia Nur',
            'instansi_pendidikan' => 'Politeknik Negeri Banjarmasin',
            'jurusan' => 'Teknik Informatika',
            'no_hp' => '081234567890',
            'jenis_kelamin' => 'P',
            'foto' => $file,
        ]);

        $updateResponse->assertRedirect(route('profil.edit'));
        $updateResponse->assertSessionHas('success');

        $this->magang->refresh();
        $this->assertNotNull($this->magang->foto);
        Storage::disk('public')->assertExists($this->magang->foto);

        // Dashboard sekarang harus menampilkan URL foto bukan inisial saja
        $dashboardResponse = $this->actingAs($this->userMagang)->get(route('presensi.index'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($this->magang->foto_url);
    }
}
