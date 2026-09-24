<?php

namespace Tests\Feature;

use App\Models\Aktivitas;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiPulangAktivitasTest extends TestCase
{
    use RefreshDatabase;

    private function createMagangUser(): Pengguna
    {
        return Pengguna::create([
            'username' => 'magang_test',
            'password' => 'password123',
            'role' => 'magang',
            'is_active' => true,
        ]);
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

        $response = $this->actingAs($user)->get(route('presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Presensi Pulang Terkunci');
        $response->assertSee('Wajib Mengisi Aktivitas Harian');
        $response->assertSee(route('aktivitas.create', ['redirect_to' => 'presensi']));
        $response->assertDontSee('id="form-presensi-keluar"', false);
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
                'foto_keluar' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_keluar' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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

        $response = $this->actingAs($user)->get(route('presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Aktivitas harian hari ini telah diisi');
        $response->assertSee('id="form-presensi-keluar"', false);
        $response->assertDontSee('Presensi Pulang Terkunci');
    }

    public function test_creating_activity_with_redirect_to_presensi_redirects_back_to_presensi_page(): void
    {
        $user = $this->createMagangUser();
        $today = Carbon::today()->toDateString();

        $response = $this->actingAs($user)->post(route('aktivitas.store'), [
            'tanggal' => $today,
            'isi' => 'Menyelesaikan dokumentasi teknis harian.',
            'progress' => 100,
            'redirect_to' => 'presensi',
        ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success', 'Aktivitas harian berhasil dicatat! Sekarang Anda dapat melakukan presensi pulang.');

        $this->assertDatabaseHas('aktivitas', [
            'pengguna_id' => $user->id,
            'progress' => 100,
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
                'foto_masuk' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_masuk' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_masuk' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_masuk' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_keluar' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_keluar' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_masuk' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('Tercatat Terlambat 25 menit', session('success'));

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
                'foto_masuk' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_masuk' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_masuk' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_keluar' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_keluar' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
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
                'foto_keluar' => 'data:image/jpeg;base64,'.base64_encode('fake_image_content'),
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $presensi->refresh();
        $this->assertNotNull($presensi->jam_keluar);
        $this->assertEquals('16:30:00', $presensi->jam_keluar);
    }
}
