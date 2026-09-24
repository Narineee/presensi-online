<?php

namespace Tests\Feature;

use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MagangJenisKelaminTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    private Pembimbing $pembimbing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::create([
            'username' => 'admin_test',
            'password' => 'secret123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing_test',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $this->pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198701012015011001',
            'nama_lengkap' => 'Pembimbing Uji',
            'jabatan' => 'Supervisor',
            'no_hp' => '08123456789',
        ]);
    }

    public function test_admin_can_create_magang_with_jenis_kelamin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.magang.store'), [
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'no_induk' => '2026001',
            'pembimbing_id' => $this->pembimbing->id,
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
            'username' => 'budi_santoso',
            'password' => 'password123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.magang.index'));

        $this->assertDatabaseHas('magang', [
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'no_induk' => '2026001',
        ]);

        $magang = Magang::where('nama_lengkap', 'Budi Santoso')->first();
        $this->assertNotNull($magang);
        $this->assertEquals('L', $magang->jenis_kelamin);
        $this->assertEquals('Laki-laki', $magang->jenis_kelamin_teks);
    }

    public function test_admin_can_create_magang_without_jenis_kelamin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.magang.store'), [
            'nama_lengkap' => 'Siti Nurhaliza',
            'no_induk' => '2026002',
            'pembimbing_id' => $this->pembimbing->id,
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
            'username' => 'siti_nur',
            'password' => 'password123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.magang.index'));

        $magang = Magang::where('nama_lengkap', 'Siti Nurhaliza')->first();
        $this->assertNotNull($magang);
        $this->assertNull($magang->jenis_kelamin);
        $this->assertNull($magang->jenis_kelamin_teks);
    }

    public function test_admin_cannot_create_magang_with_invalid_jenis_kelamin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.magang.store'), [
            'nama_lengkap' => 'Invalid Gender User',
            'jenis_kelamin' => 'X',
            'pembimbing_id' => $this->pembimbing->id,
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
            'username' => 'invalid_user',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('jenis_kelamin');
        $this->assertDatabaseMissing('pengguna', ['username' => 'invalid_user']);
    }

    public function test_admin_can_update_magang_jenis_kelamin(): void
    {
        $user = Pengguna::create([
            'username' => 'citra_ayu',
            'password' => 'password123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $this->pembimbing->id,
            'nama_lengkap' => 'Citra Ayu',
            'jenis_kelamin' => null,
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.magang.update', $magang->id), [
            'nama_lengkap' => 'Citra Ayu Permata',
            'jenis_kelamin' => 'P',
            'pembimbing_id' => $this->pembimbing->id,
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
            'username' => 'citra_ayu',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.magang.index'));

        $magang->refresh();
        $this->assertEquals('P', $magang->jenis_kelamin);
        $this->assertEquals('Perempuan', $magang->jenis_kelamin_teks);
    }

    public function test_magang_user_can_update_own_jenis_kelamin_via_profile(): void
    {
        $user = Pengguna::create([
            'username' => 'dewi_lestari',
            'password' => 'password123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $this->pembimbing->id,
            'nama_lengkap' => 'Dewi Lestari',
            'jenis_kelamin' => 'L', // initially wrong
            'instansi_pendidikan' => 'Universitas Indonesia',
            'jurusan' => 'Sistem Informasi',
            'no_hp' => '08129876543',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->put(route('profil.update'), [
            'nama_lengkap' => 'Dewi Lestari',
            'jenis_kelamin' => 'P',
            'instansi_pendidikan' => 'Universitas Indonesia',
            'jurusan' => 'Sistem Informasi',
            'no_hp' => '08129876543',
        ]);

        $response->assertRedirect(route('profil.edit'));
        $magang->refresh();
        $this->assertEquals('P', $magang->jenis_kelamin);
    }

    public function test_admin_magang_views_render_jenis_kelamin_components(): void
    {
        $user = Pengguna::create([
            'username' => 'eko_patrio',
            'password' => 'password123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $this->pembimbing->id,
            'nama_lengkap' => 'Eko Patrio',
            'jenis_kelamin' => 'L',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
        ]);

        $responseCreate = $this->actingAs($this->admin)->get(route('admin.magang.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Jenis Kelamin');

        $responseEdit = $this->actingAs($this->admin)->get(route('admin.magang.edit', $magang->id));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Jenis Kelamin');
        $responseEdit->assertSee('selected>Laki-laki (L)</option>', false);

        $responseIndex = $this->actingAs($this->admin)->get(route('admin.magang.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Eko Patrio');
        $responseIndex->assertSee('title="Laki-laki">L<', false);
    }
}
