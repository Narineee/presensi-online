<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Models\RencanaKerja;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PresensiPulangAktivitasTest extends TestCase
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

    private function createMagangUser(): Pengguna
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

        $pembimbingUser = Pengguna::create([
            'username' => 'pem_'.uniqid(),
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $pembimbingUser->id,
            'nip' => '19800101'.rand(1000, 9999),
            'nama_lengkap' => 'Pembimbing Lapangan',
            'jabatan' => 'Pranata Komputer',
            'no_hp' => '08123456789',
        ]);

        $user = Pengguna::create([
            'username' => 'magang_'.uniqid(),
            'password' => 'password123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        Magang::create([
            'pengguna_id' => $user->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM'.rand(1000, 9999),
            'nama_lengkap' => 'Peserta Test',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
            'face_descriptors' => [$this->fakeFaceDescriptor],
            'face_registered_at' => Carbon::now(),
        ]);

        return $user;
    }

    public function test_presensi_page_shows_locked_notice_when_daily_activity_is_not_filled(): void
    {
        $user = $this->createMagangUser();
        $today = Carbon::today()->toDateString();

        Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        $responseIndex = $this->actingAs($user)->get(route('presensi.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Isi aktivitas agar presensi pulang tersedia.');

        $responsePulang = $this->actingAs($user)->get(route('presensi.pulang.form'));
        $responsePulang->assertStatus(200);
        $responsePulang->assertSee('Presensi Pulang Terkunci');
        $responsePulang->assertSee('Wajib mengisi aktivitas harian');
        $responsePulang->assertSee(route('aktivitas.create'));
    }

    public function test_cannot_submit_presensi_pulang_without_filling_daily_activity(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 16:30:00', 'Asia/Makassar'));
        $today = '2026-09-19';

        $presensi = Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.4893886, 114.8252584',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
                'keterangan_keluar' => 'Selesai tugas',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('error', 'Anda belum mengisi aktivitas harian hari ini. Silakan isi aktivitas harian terlebih dahulu sebelum melakukan presensi pulang.');

        $presensi->refresh();
        $this->assertNull($presensi->jam_keluar);
    }

    public function test_can_submit_presensi_pulang_after_filling_daily_activity(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 16:30:00', 'Asia/Makassar'));
        $today = '2026-09-19';

        $presensi = Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'isi' => 'Menyelesaikan modul rekap dan fitur presensi pulang.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.4893886, 114.8252584',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
                'keterangan_keluar' => 'Pulang tepat waktu',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertNotNull($presensi->jam_keluar);
    }

    public function test_presensi_page_unlocks_presensi_pulang_form_when_activity_is_filled(): void
    {
        $user = $this->createMagangUser();
        $today = Carbon::today()->toDateString();

        Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'isi' => 'Mengerjakan tugas harian sistem presensi.',
            'progress' => 80,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('presensi.pulang.form'));

        $response->assertStatus(200);
        $response->assertDontSee('Presensi Pulang Terkunci');
        $response->assertSee(route('presensi.keluar'));
    }

    public function test_creating_activity_with_redirect_to_presensi_redirects_back_to_presensi_page(): void
    {
        $user = $this->createMagangUser();
        $today = Carbon::today()->toDateString();
        $rk = RencanaKerja::create([
            'magang_id' => $user->magang->id,
            'judul' => 'Menyelesaikan modul rekap',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->post(route('aktivitas.store'), [
            'tanggal' => $today,
            'items' => [
                [
                    'rencana_kerja_id' => $rk->id,
                    'progres' => 'Selesai',
                ],
            ],
            'redirect_to' => 'presensi',
        ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success', 'Aktivitas harian berhasil dicatat! Sekarang Anda dapat melakukan presensi pulang.');

        $this->assertDatabaseHas('aktivitas', [
            'pengguna_id' => $user->id,
        ]);

        $aktivitas = Aktivitas::where('pengguna_id', $user->id)->first();
        $this->assertNotNull($aktivitas);
        $this->assertEquals($today, $aktivitas->tanggal->toDateString());
    }

    public function test_cannot_submit_presensi_masuk_onsite_outside_office_radius(): void
    {
        $user = $this->createMagangUser();

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.masuk'), [
                'mode_kerja' => 'onsite',
                // Titik lokasi berjarak > 1 km dari kantor (-3.4893886, 114.8252584)
                'lokasi_masuk' => '-3.500000, 114.850000',
                'foto_masuk' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('di luar radius kantor', session('error'));
        $this->assertStringContainsString('40 meter', session('error'));

        $this->assertDatabaseMissing('presensi', [
            'pengguna_id' => $user->id,
        ]);
    }

    public function test_onsite_presensi_masuk_at_55m_fails_under_40m_radius(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 07:45:00', 'Asia/Makassar'));

        // Titik lokasi ~56 meter dari kantor (di luar 40m)
        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.masuk'), [
                'mode_kerja' => 'onsite',
                'lokasi_masuk' => '-3.4888886, 114.8252584',
                'foto_masuk' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('di luar radius kantor', session('error'));
        $this->assertStringContainsString('batas maksimal: 40 meter', session('error'));

        $this->assertDatabaseMissing('presensi', [
            'pengguna_id' => $user->id,
        ]);
    }

    public function test_onsite_presensi_masuk_within_40m_succeeds(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 07:45:00', 'Asia/Makassar'));

        // Titik lokasi di area kantor (< 40m)
        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.masuk'), [
                'mode_kerja' => 'onsite',
                'lokasi_masuk' => '-3.4893886, 114.8252584',
                'foto_masuk' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensi', [
            'pengguna_id' => $user->id,
            'mode_kerja' => 'onsite',
        ]);
    }

    public function test_can_submit_presensi_masuk_wfh_outside_office_radius(): void
    {
        $user = $this->createMagangUser();

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.masuk'), [
                'mode_kerja' => 'wfh',
                'lokasi_masuk' => '-3.500000, 114.850000',
                'foto_masuk' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('presensi', [
            'pengguna_id' => $user->id,
            'mode_kerja' => 'wfh',
        ]);
    }

    public function test_cannot_submit_presensi_pulang_onsite_outside_office_radius(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 16:30:00', 'Asia/Makassar'));
        $today = '2026-09-19';

        $presensi = Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'isi' => 'Menyelesaikan modul rekap.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.keluar'), [
                // Di luar radius 40m
                'lokasi_keluar' => '-3.500000, 114.850000',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('di luar radius kantor', session('error'));

        $presensi->refresh();
        $this->assertNull($presensi->jam_keluar);
    }

    public function test_can_submit_presensi_pulang_wfh_outside_office_radius(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 16:30:00', 'Asia/Makassar'));
        $today = '2026-09-19';

        $presensi = Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'mode_kerja' => 'wfh',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'isi' => 'Menyelesaikan tugas WFH.',
            'progress' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.500000, 114.850000',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertNotNull($presensi->jam_keluar);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_presensi_masuk_after_0800_wita_is_recorded_as_terlambat_for_magang(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 08:25:00', 'Asia/Makassar'));

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.masuk'), [
                'mode_kerja' => 'wfh',
                'lokasi_masuk' => '-3.500000, 114.850000',
                'foto_masuk' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsStringIgnoringCase('Tercatat terlambat 25 menit', session('success'));

        $presensi = Presensi::where('pengguna_id', $user->id)->first();
        $this->assertNotNull($presensi);
        $this->assertStringContainsString('[Terlambat 25 menit]', $presensi->keterangan);
    }

    public function test_presensi_masuk_at_or_before_0800_wita_is_not_recorded_as_terlambat(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 07:55:00', 'Asia/Makassar'));

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.masuk'), [
                'mode_kerja' => 'wfh',
                'lokasi_masuk' => '-3.500000, 114.850000',
                'foto_masuk' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');
        $this->assertStringNotContainsString('Terlambat', session('success'));

        $presensi = Presensi::where('pengguna_id', $user->id)->first();
        $this->assertNotNull($presensi);
        $this->assertStringNotContainsString('Terlambat', (string) $presensi->keterangan);
    }

    public function test_cannot_presensi_masuk_before_0730_wita(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 07:15:00', 'Asia/Makassar'));

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.masuk'), [
                'mode_kerja' => 'wfh',
                'lokasi_masuk' => '-3.500000, 114.850000',
                'foto_masuk' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Presensi masuk belum dibuka', session('error'));
        $this->assertStringContainsString('07.30 WITA', session('error'));

        $this->assertDatabaseMissing('presensi', [
            'pengguna_id' => $user->id,
        ]);
    }

    public function test_cannot_presensi_masuk_after_1800_wita(): void
    {
        $user = $this->createMagangUser();
        Carbon::setTestNow(Carbon::parse('2026-09-19 18:05:00', 'Asia/Makassar'));

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.masuk'), [
                'mode_kerja' => 'wfh',
                'lokasi_masuk' => '-3.500000, 114.850000',
                'foto_masuk' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Waktu presensi untuk hari ini telah berakhir', session('error'));

        $this->assertDatabaseMissing('presensi', [
            'pengguna_id' => $user->id,
        ]);
    }

    public function test_cannot_presensi_pulang_before_1600_wita(): void
    {
        $user = $this->createMagangUser();
        $today = '2026-09-19';

        Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'mode_kerja' => 'wfh',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'isi' => 'Selesai tugas harian',
            'progress' => 100,
            'status' => 'pending',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-19 15:30:00', 'Asia/Makassar'));

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.500000, 114.850000',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Presensi pulang belum dibuka', session('error'));
        $this->assertStringContainsString('16.00 WITA', session('error'));

        $presensi = Presensi::where('pengguna_id', $user->id)->first();
        $this->assertNull($presensi->jam_keluar);
    }

    public function test_cannot_presensi_pulang_after_1800_wita(): void
    {
        $user = $this->createMagangUser();
        $today = '2026-09-19';

        Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'mode_kerja' => 'wfh',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'isi' => 'Selesai tugas harian',
            'progress' => 100,
            'status' => 'pending',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-19 18:05:00', 'Asia/Makassar'));

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.500000, 114.850000',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Batas waktu presensi pulang telah berakhir', session('error'));

        $presensi = Presensi::where('pengguna_id', $user->id)->first();
        $this->assertNull($presensi->jam_keluar);
    }

    public function test_can_presensi_pulang_between_1600_and_1800_wita(): void
    {
        $user = $this->createMagangUser();
        $today = '2026-09-19';

        $presensi = Presensi::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'mode_kerja' => 'wfh',
            'status' => 'hadir',
        ]);

        Aktivitas::create([
            'pengguna_id' => $user->id,
            'tanggal' => $today,
            'isi' => 'Selesai tugas harian',
            'progress' => 100,
            'status' => 'pending',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-19 16:30:00', 'Asia/Makassar'));

        $response = $this->actingAs($user)
            ->from(route('presensi.index'))
            ->post(route('presensi.keluar'), [
                'lokasi_keluar' => '-3.500000, 114.850000',
                'foto_keluar' => $this->createValidBase64Image(),
                'face_descriptor' => json_encode($this->fakeFaceDescriptor),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertNotNull($presensi->jam_keluar);
        $this->assertEquals('16:30:00', $presensi->jam_keluar);
    }
}
