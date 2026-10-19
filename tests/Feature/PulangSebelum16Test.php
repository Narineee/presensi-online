<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PulangSebelum16Test extends TestCase
{
    use RefreshDatabase;

    private array $fakeFaceDescriptor;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->fakeFaceDescriptor = array_fill(0, 128, 0.1);
    }

    private function createValidBase64Image(): string
    {
        $im = imagecreatetruecolor(10, 10);
        ob_start();
        imagejpeg($im);
        $binary = ob_get_clean();
        imagedestroy($im);

        return 'data:image/jpeg;base64,'.base64_encode($binary);
    }

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

    private function createInternWithPembimbing(Pembimbing $pembimbing, string $name = 'Budi Intern', string $username = 'budi_intern'): array
    {
        $divisi = Divisi::firstOrCreate(
            ['nama_divisi' => 'Teknologi Informasi'],
            [
                'nama_pimpinan' => 'Kadiv IT',
                'nip_pimpinan' => '197501012000031001',
                'jabatan_pimpinan' => 'Kadiv IT',
                'latitude' => -3.4893886,
                'longitude' => 114.8252584,
                'radius_meter' => 40,
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
            'face_descriptors' => [$this->fakeFaceDescriptor],
            'face_registered_at' => now(),
        ]);

        return [$user, $magang];
    }

    public function test_form_pulang_before_1600_displays_reason_options_for_sakit_and_izin(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_ui');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta UI', 'magang_ui');

        Carbon::setTestNow(Carbon::parse('2026-10-13 13:30:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'isi' => 'Menyelesaikan modul presensi kepulangan.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($internUser)->get(route('presensi.pulang.form'));

        $response->assertStatus(200);
        $response->assertSee('Pulang Sebelum 16.00');
        $response->assertSee('name="alasan_pulang"', false);
        $response->assertSee('value="Sakit"', false);
        $response->assertSee('value="Izin"', false);
        $response->assertSee('Lanjut verifikasi wajah');
    }

    public function test_cannot_checkout_before_1600_without_selecting_reason(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_val');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Val', 'magang_val');

        Carbon::setTestNow(Carbon::parse('2026-10-13 14:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'isi' => 'Mengerjakan tugas harian sistem.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($internUser)
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.4893886, 114.8252584',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Presensi pulang belum dibuka', session('error'));
        $this->assertStringContainsString('16.00 WITA', session('error'));
    }

    public function test_can_checkout_before_1600_with_alasan_sakit_and_stores_catatan(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_sakit');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Sakit', 'magang_sakit');

        Carbon::setTestNow(Carbon::parse('2026-10-13 14:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:50:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'isi' => 'Menyelesaikan input berkas dinas pagi hari.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($internUser)
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.4893886, 114.8252584',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
                'alasan_pulang' => 'Sakit',
                'keterangan_keluar' => 'Demam tinggi',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertEquals('14:00:00', $presensi->jam_keluar);
        $this->assertEquals('hadir', $presensi->status); // Status TETAP hadir
        $this->assertStringContainsString('Sakit', $presensi->keterangan);
        $this->assertStringContainsString('Demam tinggi', $presensi->keterangan);
    }

    public function test_can_checkout_before_1600_with_alasan_izin_and_stores_catatan(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_izin');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Izin', 'magang_izin');

        Carbon::setTestNow(Carbon::parse('2026-10-13 13:30:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:55:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'isi' => 'Membantu pelayanan berkas pemohon.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($internUser)
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.4893886, 114.8252584',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
                'alasan_pulang' => 'Izin',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertEquals('13:30:00', $presensi->jam_keluar);
        $this->assertEquals('hadir', $presensi->status);
        $this->assertEquals('Izin', $presensi->keterangan);
    }

    public function test_status_remains_hadir_when_checked_out_early(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_tetap');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Hadir', 'magang_tetap');

        Carbon::setTestNow(Carbon::parse('2026-10-13 12:45:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:40:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'isi' => 'Rapat internal divisi IT.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $this->actingAs($internUser)
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.4893886, 114.8252584',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
                'alasan_pulang' => 'Sakit',
            ]);

        $presensi->refresh();
        $this->assertEquals('hadir', $presensi->status);
        $this->assertNotEquals('sakit', $presensi->status);
        $this->assertNotEquals('izin', $presensi->status);
    }

    public function test_normal_checkout_at_1630_succeeds_without_early_reason(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_normal');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Normal', 'magang_normal');

        Carbon::setTestNow(Carbon::parse('2026-10-13 16:30:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'isi' => 'Selesai tugas harian penuh.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($internUser)
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.4893886, 114.8252584',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertEquals('16:30:00', $presensi->jam_keluar);
        $this->assertEquals('hadir', $presensi->status);
    }

    public function test_dashboard_displays_pulang_sakit_izin_button_before_1600_when_activity_is_filled(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_dash');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Dash', 'magang_dash');

        Carbon::setTestNow(Carbon::parse('2026-10-13 13:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'isi' => 'Aktivitas sudah lengkap.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($internUser)->get(route('presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Pulang (Sakit / Izin)');
        $response->assertSee('Sebelum pukul 16.00');
        $response->assertSee(route('presensi.pulang.form'));
    }

    public function test_catatan_appears_in_riwayat_and_cetak(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_cetak');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Cetak', 'magang_cetak');

        $today = '2026-10-13';

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'jam_keluar' => '13:30:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
            'keterangan' => 'Sakit',
        ]);

        $riwayatResponse = $this->actingAs($internUser)->get(route('presensi.riwayat'));
        $riwayatResponse->assertStatus(200);
        $riwayatResponse->assertSee('Catatan:');
        $riwayatResponse->assertSee('Sakit');

        $pdfResponse = $this->actingAs($internUser)->get(route('presensi.cetak'));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');

        $cetakResponse = $this->actingAs($internUser)->get(route('presensi.cetak', ['format' => 'html']));
        $cetakResponse->assertStatus(200);
        $cetakResponse->assertSee('Sakit');
    }

    public function test_alur_hari_ini_shows_terlambat_on_masuk_and_early_note_on_pulang(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_alur');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Alur', 'magang_alur');

        Carbon::setTestNow(Carbon::parse('2026-10-13 17:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        // Case 1: Masuk terlambat (12:41), pulang normal (17:00)
        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '12:41:00',
            'jam_keluar' => '17:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
            'keterangan' => '[Terlambat 281 menit]',
        ]);

        Aktivitas::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'isi' => 'Aktivitas hari ini.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $res1 = $this->actingAs($internUser)->get(route('presensi.index'));
        $res1->assertStatus(200);
        $res1->assertSee('12:41 (Terlambat 281 menit)');
        $res1->assertSee('17:00');
        $res1->assertDontSee('17:00 ([Terlambat');

        // Case 2: Masuk terlambat (12:41), pulang awal karena Sakit (14:00)
        $presensi->update([
            'jam_keluar' => '14:00:00',
            'keterangan' => '[Terlambat 281 menit] | Pulang: Sakit',
        ]);

        $res2 = $this->actingAs($internUser)->get(route('presensi.index'));
        $res2->assertStatus(200);
        $res2->assertSee('12:41 (Terlambat 281 menit)');
        $res2->assertSee('14:00 (Pulang: Sakit)');
        $res2->assertDontSee('14:00 ([Terlambat');

        // Case 3: Masuk tepat waktu (07:45), pulang awal karena Izin (13:30)
        $presensi->update([
            'jam_masuk' => '07:45:00',
            'jam_keluar' => '13:30:00',
            'keterangan' => 'Izin',
        ]);

        $res3 = $this->actingAs($internUser)->get(route('presensi.index'));
        $res3->assertStatus(200);
        $res3->assertSee('07:45 (Tepat Waktu)');
        $res3->assertSee('13:30 (Pulang: Izin)');
    }
}
