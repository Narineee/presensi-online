<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use App\Models\Presensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembimbingPresensiTest extends TestCase
{
    use RefreshDatabase;

    private function createPembimbingWithUser(string $username = 'pembimbing1'): array
    {
        $user = Pengguna::create([
            'username' => $username,
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $user->id,
            'nip' => '19800101'.rand(1000, 9999),
            'nama_lengkap' => 'Pembimbing '.ucfirst($username),
            'jabatan' => 'Pembimbing Lapangan',
            'no_hp' => '08123456789',
        ]);

        return [$user, $pembimbing];
    }

    private function createIntern(Pembimbing $pembimbing, string $name, string $username): array
    {
        $divisi = Divisi::firstOrCreate(
            ['nama_divisi' => 'Teknologi Informasi'],
            [
                'nama_pimpinan' => 'Kepala Divisi IT',
                'nip_pimpinan' => '197501012000031001',
                'jabatan_pimpinan' => 'Kadiv IT',
                'latitude' => -3.489,
                'longitude' => 114.825,
                'radius_meter' => 50,
            ]
        );

        $user = Pengguna::create([
            'username' => $username,
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM'.rand(1000, 9999),
            'nama_lengkap' => $name,
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
        ]);

        return [$user, $magang];
    }

    public function test_pembimbing_can_access_presensi_index_and_see_supervised_interns_attendance(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_a');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Peserta Binaan A', 'magang_a');

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '07:55:00',
            'jam_keluar' => '16:05:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'lokasi_masuk' => '-3.489, 114.825',
            'keterangan' => 'Hadir tepat waktu',
        ]);

        $response = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pembimbing.presensi.index');
        $response->assertSee('Peserta Binaan A');
        $response->assertSee('Hadir tepat waktu');
        $response->assertSee('07:55');
    }

    public function test_pembimbing_cannot_see_attendance_of_other_pembimbing_interns(): void
    {
        [$pembimbingAUser, $pembimbingA] = $this->createPembimbingWithUser('pembimbing_a');
        [$pembimbingBUser, $pembimbingB] = $this->createPembimbingWithUser('pembimbing_b');

        [$internAUser, $internA] = $this->createIntern($pembimbingA, 'Anak Binaan A', 'magang_a');
        [$internBUser, $internB] = $this->createIntern($pembimbingB, 'Anak Binaan B Lain', 'magang_b');

        Presensi::create([
            'pengguna_id' => $internAUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        Presensi::create([
            'pengguna_id' => $internBUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:15:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // Pembimbing A mengakses riwayat presensi
        $response = $this->actingAs($pembimbingAUser)->get(route('pembimbing.presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Anak Binaan A');
        $response->assertDontSee('Anak Binaan B Lain');
    }

    public function test_pembimbing_can_filter_by_specific_intern(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_multi');

        [$intern1User, $intern1] = $this->createIntern($pembimbing, 'Peserta Satu', 'magang_1');
        [$intern2User, $intern2] = $this->createIntern($pembimbing, 'Peserta Dua', 'magang_2');

        Presensi::create([
            'pengguna_id' => $intern1User->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'keterangan' => 'Kehadiran Intern Satu Unik',
        ]);

        Presensi::create([
            'pengguna_id' => $intern2User->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:10:00',
            'status' => 'hadir',
            'mode_kerja' => 'wfh',
            'keterangan' => 'Kehadiran Intern Dua Unik',
        ]);

        $response = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.index', [
            'magang_id' => $intern1->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kehadiran Intern Satu Unik');
        $response->assertDontSee('Kehadiran Intern Dua Unik');
        $response->assertViewHas('presensi', function ($presensi) use ($intern1User, $intern2User) {
            return $presensi->contains('pengguna_id', $intern1User->id)
                && ! $presensi->contains('pengguna_id', $intern2User->id);
        });
    }

    public function test_pembimbing_can_filter_by_date_and_mode_kerja(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_filter');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Peserta Filter', 'magang_f');

        // Presensi kemarin onsite
        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => '2026-09-25',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
            'keterangan' => 'Presensi Onsite 25 Sep',
        ]);

        // Presensi hari ini wfh
        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'wfh',
            'keterangan' => 'Presensi WFH 28 Sep',
        ]);

        // Filter mode WFH
        $response = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.index', [
            'mode_kerja' => 'wfh',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Presensi WFH 28 Sep');
        $response->assertDontSee('Presensi Onsite 25 Sep');
    }

    public function test_pembimbing_can_access_presensi_cetak(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pembimbing_cetak');
        [$internUser, $intern] = $this->createIntern($pembimbing, 'Peserta Cetak', 'magang_c');

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => '2026-09-28',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '16:00:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        $response = $this->actingAs($pembimbingUser)->get(route('pembimbing.presensi.cetak', [
            'tanggal' => '2026-09-28',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('pembimbing.presensi.cetak');
        $response->assertSee('Rekapitulasi Presensi Kehadiran Peserta Magang Binaan');
        $response->assertSee('Peserta Cetak');
        $response->assertSee($pembimbing->nama_lengkap);
    }

    public function test_unauthorized_user_cannot_access_pembimbing_presensi(): void
    {
        // Tamu tidak login -> dialihkan ke login
        $guestResponse = $this->get(route('pembimbing.presensi.index'));
        $guestResponse->assertRedirect(route('login'));

        // Akun magang -> 403 Forbidden
        $magangUser = Pengguna::create([
            'username' => 'magang_biasa',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $forbiddenResponse = $this->actingAs($magangUser)->get(route('pembimbing.presensi.index'));
        $forbiddenResponse->assertStatus(403);
    }
}
