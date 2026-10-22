<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanTugasLuar;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LayananPengajuanTest extends TestCase
{
    use RefreshDatabase;

    private function createInternUser(): array
    {
        Storage::fake('public');

        $pembimbingUser = Pengguna::create([
            'username' => 'pem_layanan',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $pembimbingUser->id,
            'nip' => '198001012000031001',
            'nama_lengkap' => 'Pembimbing Layanan',
            'jabatan' => 'Pembimbing Lapangan',
            'no_hp' => '08123456789',
        ]);

        $divisi = Divisi::create([
            'nama_divisi' => 'Teknologi Informasi',
            'nama_pimpinan' => 'Kadiv IT',
            'nip_pimpinan' => '197501012000031001',
            'jabatan_pimpinan' => 'Kadiv IT',
            'latitude' => -3.4893886,
            'longitude' => 114.8252584,
            'radius_meter' => 40,
        ]);

        $internUser = Pengguna::create([
            'username' => 'intern_layanan',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $internUser->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM889900',
            'nama_lengkap' => 'Intern Layanan Test',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
            'face_descriptors' => [array_fill(0, 128, 0.1)],
            'face_registered_at' => now(),
        ]);

        return [$internUser, $magang, $pembimbing];
    }

    public function test_layanan_pengajuan_renders_segmented_tabs_for_tugas_luar_and_izin(): void
    {
        [$internUser] = $this->createInternUser();

        $response = $this->actingAs($internUser)->get(route('izin.create'));

        $response->assertStatus(200);
        $response->assertSee('Layanan Pengajuan');
        $response->assertSee('Ajukan penugasan dinas luar atau ketidakhadiran');
        $response->assertSee('Tugas Luar (TL)');
        $response->assertSee('Izin / Sakit');
        $response->assertSee('Status: Tetap Dihitung Hadir');
        $response->assertSee(route('presensi.tugas-luar'));
        $response->assertSee(route('izin.store'));
    }

    public function test_submitting_tl_for_yesterday_does_not_block_todays_check_in(): void
    {
        [$internUser] = $this->createInternUser();

        // Misal hari ini adalah Selasa 13 Oktober 2026
        Carbon::setTestNow(Carbon::parse('2026-10-13 08:30:00', 'Asia/Makassar'));
        $today = '2026-10-13';
        $yesterday = '2026-10-12';

        // Intern belum check-in hari ini, lalu mengajukan TL untuk kemarin (Senin 12 Oktober)
        $response = $this->actingAs($internUser)->post(route('presensi.tugas-luar'), [
            'tanggal_selesai' => $yesterday,
            'tanggal_mulai' => $yesterday,
            'tujuan' => 'Kantor Dinas Pendidikan',
            'keperluan' => 'Mendampingi kunjungan kerja kemarin',
        ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        // Pastikan TL kemarin tersimpan
        $tl = PengajuanTugasLuar::where('pengguna_id', $internUser->id)->first();
        $this->assertNotNull($tl);
        $this->assertEquals($yesterday, $tl->tanggal_selesai->format('Y-m-d'));

        // PENTING: Presensi HARI INI (13 Oktober) TIDAK TERBUAT / TIDAK TERKUNCI!
        $todayPresensi = Presensi::where('pengguna_id', $internUser->id)
            ->whereDate('tanggal', $today)
            ->first();
        $this->assertNull($todayPresensi, 'Presensi hari ini tidak boleh dibuat otomatis jika mengajukan TL kemarin.');

        // Intern tetap dapat mengakses form presensi masuk hari ini tanpa terblokir
        $resFormMasuk = $this->actingAs($internUser)->get(route('presensi.masuk.form'));
        $resFormMasuk->assertStatus(200);
    }

    public function test_submitting_tl_for_tomorrow_does_not_touch_todays_presensi(): void
    {
        [$internUser] = $this->createInternUser();

        // Hari ini Selasa 13 Oktober 2026
        Carbon::setTestNow(Carbon::parse('2026-10-13 14:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';
        $tomorrow = '2026-10-14';

        // Misal intern sudah absen masuk onsite hari ini pukul 07:45
        $presensiHariIni = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'status' => 'hadir',
            'mode_kerja' => 'onsite',
        ]);

        // Siang ini dia mengajukan TL untuk BESOK (Rabu 14 Oktober)
        $response = $this->actingAs($internUser)->post(route('presensi.tugas-luar'), [
            'tanggal_selesai' => $tomorrow,
            'tanggal_mulai' => $tomorrow,
            'tujuan' => 'Kementerian Kominfo Jakarta',
            'keperluan' => 'Rapat koordinasi teknis besok',
        ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        // TL besok tersimpan
        $tl = PengajuanTugasLuar::where('pengguna_id', $internUser->id)->first();
        $this->assertNotNull($tl);
        $this->assertEquals($tomorrow, $tl->tanggal_selesai->format('Y-m-d'));

        // Presensi hari ini tetap mode onsite dan jam masuk 07:45 (tidak tertimpa)
        $presensiHariIni->refresh();
        $this->assertEquals('onsite', $presensiHariIni->mode_kerja);
        $this->assertEquals('07:45:00', $presensiHariIni->jam_masuk);
    }
}
