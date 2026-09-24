<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterMonitoringRentangTanggalTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): Pengguna
    {
        return Pengguna::create([
            'username' => 'admin_range',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function setupPesertaMagang(): array
    {
        $divisi = Divisi::create([
            'nama_divisi' => 'Teknologi Informasi',
            'nama_pimpinan' => 'Pimpinan IT',
            'nip_pimpinan' => '198001012005011001',
            'jabatan_pimpinan' => 'Kepala IT',
            'latitude' => -3.4893886,
            'longitude' => 114.8252584,
            'radius_meter' => 100,
        ]);

        $userPembimbing = Pengguna::create([
            'username' => 'pembimbing_range',
            'password' => 'password123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $userPembimbing->id,
            'nip' => '198801012015011001',
            'nama_lengkap' => 'Pembimbing Range',
            'jabatan' => 'Senior Developer',
            'no_hp' => '08123456789',
        ]);

        $userMagang = Pengguna::create([
            'username' => 'magang_range',
            'password' => 'password123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $userMagang->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'MG-RANGE-01',
            'nama_lengkap' => 'Rian Mahasiswa Range',
            'jurusan' => 'Informatika',
            'instansi_pendidikan' => 'Universitas Indonesia',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'aktif',
        ]);

        return [$userMagang, $magang];
    }

    public function test_monitoring_presensi_renders_date_inputs_and_filters_by_date_range(): void
    {
        $admin = $this->createAdmin();
        [$userMagang] = $this->setupPesertaMagang();

        Presensi::create([
            'pengguna_id' => $userMagang->id,
            'tanggal' => '2026-09-15',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '17:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
            'keterangan' => 'Presensi September 15',
        ]);

        Presensi::create([
            'pengguna_id' => $userMagang->id,
            'tanggal' => '2026-08-10',
            'jam_masuk' => '08:05:00',
            'jam_keluar' => '17:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
            'keterangan' => 'Presensi Agustus 10',
        ]);

        // Assert view contains date picker inputs for Tanggal Awal and Tanggal Selesai
        $responseIndex = $this->actingAs($admin)->get(route('admin.presensi.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Tanggal Awal');
        $responseIndex->assertSee('Tanggal Selesai');
        $responseIndex->assertSee('name="tanggal_mulai"', false);
        $responseIndex->assertSee('name="tanggal_akhir"', false);
        $responseIndex->assertSee('type="date"', false);

        // Filter range September
        $responseRange = $this->actingAs($admin)->get(route('admin.presensi.index', [
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
        ]));
        $responseRange->assertStatus(200);
        $responseRange->assertSee('Presensi September 15');
        $responseRange->assertDontSee('Presensi Agustus 10');

        // Filter range Agustus
        $responseAgustus = $this->actingAs($admin)->get(route('admin.presensi.index', [
            'tanggal_mulai' => '2026-08-01',
            'tanggal_akhir' => '2026-08-31',
        ]));
        $responseAgustus->assertStatus(200);
        $responseAgustus->assertSee('Presensi Agustus 10');
        $responseAgustus->assertDontSee('Presensi September 15');
    }

    public function test_monitoring_aktivitas_renders_date_inputs_and_filters_by_date_range(): void
    {
        $admin = $this->createAdmin();
        [$userMagang] = $this->setupPesertaMagang();

        Aktivitas::create([
            'pengguna_id' => $userMagang->id,
            'tanggal' => '2026-09-20',
            'isi' => 'Aktivitas Khusus September 20',
            'progress' => 80,
            'status' => 'approve',
        ]);

        Aktivitas::create([
            'pengguna_id' => $userMagang->id,
            'tanggal' => '2026-08-20',
            'isi' => 'Aktivitas Khusus Agustus 20',
            'progress' => 100,
            'status' => 'approve',
        ]);

        // Assert view contains date picker inputs for Tanggal Awal and Tanggal Selesai
        $responseIndex = $this->actingAs($admin)->get(route('admin.aktivitas.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Tanggal Awal');
        $responseIndex->assertSee('Tanggal Selesai');
        $responseIndex->assertSee('name="tanggal_mulai"', false);
        $responseIndex->assertSee('name="tanggal_akhir"', false);
        $responseIndex->assertSee('type="date"', false);

        // Filter range September
        $resSeptember = $this->actingAs($admin)->get(route('admin.aktivitas.index', [
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
        ]));
        $resSeptember->assertStatus(200);
        $resSeptember->assertSee('Aktivitas Khusus September 20');
        $resSeptember->assertDontSee('Aktivitas Khusus Agustus 20');

        // Filter range Agustus
        $resAgustus = $this->actingAs($admin)->get(route('admin.aktivitas.index', [
            'tanggal_mulai' => '2026-08-01',
            'tanggal_akhir' => '2026-08-31',
        ]));
        $resAgustus->assertStatus(200);
        $resAgustus->assertSee('Aktivitas Khusus Agustus 20');
        $resAgustus->assertDontSee('Aktivitas Khusus September 20');
    }

    public function test_monitoring_izin_renders_date_inputs_and_filters_by_date_range(): void
    {
        $admin = $this->createAdmin();
        [$userMagang] = $this->setupPesertaMagang();

        PengajuanIzin::create([
            'pengguna_id' => $userMagang->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-09-05',
            'tanggal_selesai' => '2026-09-06',
            'alasan' => 'Izin Sakit September 05',
            'status_approval' => 'pending',
        ]);

        PengajuanIzin::create([
            'pengguna_id' => $userMagang->id,
            'jenis_izin' => 'izin',
            'tanggal_mulai' => '2026-07-05',
            'tanggal_selesai' => '2026-07-06',
            'alasan' => 'Izin Keperluan Juli 05',
            'status_approval' => 'pending',
        ]);

        // Assert view contains date picker inputs for Tanggal Awal and Tanggal Selesai
        $responseIndex = $this->actingAs($admin)->get(route('admin.izin.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Tanggal Awal');
        $responseIndex->assertSee('Tanggal Selesai');
        $responseIndex->assertSee('name="tanggal_mulai"', false);
        $responseIndex->assertSee('name="tanggal_akhir"', false);
        $responseIndex->assertSee('type="date"', false);

        // Filter range September
        $resSeptember = $this->actingAs($admin)->get(route('admin.izin.index', [
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
        ]));
        $resSeptember->assertStatus(200);
        $resSeptember->assertSee('Izin Sakit September 05');
        $resSeptember->assertDontSee('Izin Keperluan Juli 05');

        // Filter range Juli
        $resJuli = $this->actingAs($admin)->get(route('admin.izin.index', [
            'tanggal_mulai' => '2026-07-01',
            'tanggal_akhir' => '2026-07-31',
        ]));
        $resJuli->assertStatus(200);
        $resJuli->assertSee('Izin Keperluan Juli 05');
        $resJuli->assertDontSee('Izin Sakit September 05');
    }
}
