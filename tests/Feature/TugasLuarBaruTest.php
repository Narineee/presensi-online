<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\HariLibur;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanTugasLuar;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TugasLuarBaruTest extends TestCase
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

    public function test_form_component_renders_tanggal_selesai_and_mulai_and_no_time_inputs(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_ui');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta UI', 'magang_ui');

        $response = $this->actingAs($internUser)->get(route('presensi.masuk.form'));

        $response->assertStatus(200);
        $response->assertSee('name="tanggal_selesai"', false);
        $response->assertSee('name="tanggal_mulai"', false);
        $response->assertSee('Pilih tanggal selesai terlebih dahulu untuk mengaktifkan tanggal mulai.');
        $response->assertDontSee('name="waktu_mulai"', false);
        $response->assertDontSee('name="waktu_selesai"', false);
    }

    public function test_can_submit_tugas_luar_with_dates_and_without_time_inputs(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_sub');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Sub', 'magang_sub');

        Carbon::setTestNow(Carbon::parse('2026-10-13 09:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        // Check in onsite first
        $presensi = Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:45:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($internUser)
            ->post(route('presensi.tugas-luar'), [
                'tanggal_selesai' => '2026-10-13',
                'tanggal_mulai' => '2026-10-12',
                'tujuan' => 'Kantor Gubernur Kalsel',
                'keperluan' => 'Rapat koordinasi magang antar instansi',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $tl = PengajuanTugasLuar::where('presensi_id', $presensi->id)->first();
        $this->assertNotNull($tl);
        $this->assertEquals($internUser->id, $tl->pengguna_id);
        $this->assertEquals('2026-10-13', $tl->tanggal_selesai->format('Y-m-d'));
        $this->assertEquals('2026-10-12', $tl->tanggal_mulai->format('Y-m-d'));
        $this->assertEquals('Kantor Gubernur Kalsel', $tl->tujuan);
        $this->assertEquals('menunggu', $tl->status_verifikasi);
    }

    public function test_cannot_submit_tanggal_selesai_older_than_3_working_days(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_old');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Old', 'magang_old');

        // Tuesday 13 October 2026
        // 3 working days back: Monday 12 Oct (1), Friday 09 Oct (2), Thursday 08 Oct (3)
        Carbon::setTestNow(Carbon::parse('2026-10-13 10:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:50:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        // Wednesday 07 October is 4 working days ago (older than 3 working days)
        $response = $this->actingAs($internUser)
            ->post(route('presensi.tugas-luar'), [
                'tanggal_selesai' => '2026-10-07',
                'tanggal_mulai' => '2026-10-05',
                'tujuan' => 'Lokasi Terlalu Lampau',
                'keperluan' => 'Mencoba submit yang sudah lewat batas',
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, PengajuanTugasLuar::count());
    }

    public function test_can_submit_tanggal_selesai_within_3_working_days_back(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_valid_back');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Valid Back', 'magang_valid_back');

        Carbon::setTestNow(Carbon::parse('2026-10-13 10:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:50:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        // Thursday 08 October is exactly 3 working days back
        $response = $this->actingAs($internUser)
            ->post(route('presensi.tugas-luar'), [
                'tanggal_selesai' => '2026-10-08',
                'tanggal_mulai' => '2026-10-08',
                'tujuan' => 'Lokasi Pas Batas',
                'keperluan' => 'Submit tepat pada batas 3 hari kerja',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(1, PengajuanTugasLuar::count());
    }

    public function test_can_submit_tanggal_selesai_in_the_future(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_fut');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Fut', 'magang_fut');

        Carbon::setTestNow(Carbon::parse('2026-10-13 10:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:50:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        // Tanggal selesai di masa depan (misal minggu depan: 20 Oktober 2026)
        $response = $this->actingAs($internUser)
            ->post(route('presensi.tugas-luar'), [
                'tanggal_selesai' => '2026-10-20',
                'tanggal_mulai' => '2026-10-15',
                'tujuan' => 'Kantor Kementerian Jakarta',
                'keperluan' => 'Tugas luar mendatang yang diajukan lebih awal',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $tl = PengajuanTugasLuar::where('pengguna_id', $internUser->id)->first();
        $this->assertNotNull($tl);
        $this->assertEquals('2026-10-20', $tl->tanggal_selesai->format('Y-m-d'));
        $this->assertEquals('2026-10-15', $tl->tanggal_mulai->format('Y-m-d'));
        $this->assertEquals('Kantor Kementerian Jakarta', $tl->tujuan);
    }

    public function test_tanggal_mulai_can_be_more_than_3_working_days_in_the_past_if_tanggal_selesai_is_valid(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_long');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Long', 'magang_long');

        Carbon::setTestNow(Carbon::parse('2026-10-13 10:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:50:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        // Tanggal selesai hari ini (13 Oct), tapi tanggal mulai tugas 3 minggu lalu (22 Sep)
        $response = $this->actingAs($internUser)
            ->post(route('presensi.tugas-luar'), [
                'tanggal_selesai' => '2026-10-13',
                'tanggal_mulai' => '2026-09-22',
                'tujuan' => 'Pusat Riset Lapangan Terpadu',
                'keperluan' => 'Pelaksanaan tugas riset lapangan jangka panjang',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');

        $tl = PengajuanTugasLuar::where('pengguna_id', $internUser->id)->first();
        $this->assertNotNull($tl);
        $this->assertEquals('2026-10-13', $tl->tanggal_selesai->format('Y-m-d'));
        $this->assertEquals('2026-09-22', $tl->tanggal_mulai->format('Y-m-d'));
    }

    public function test_cannot_submit_tanggal_mulai_after_tanggal_selesai(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_inv');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Inv', 'magang_inv');

        Carbon::setTestNow(Carbon::parse('2026-10-13 10:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:50:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        // Tanggal mulai (15 Oct) lebih besar daripada tanggal selesai (13 Oct)
        $response = $this->actingAs($internUser)
            ->post(route('presensi.tugas-luar'), [
                'tanggal_selesai' => '2026-10-13',
                'tanggal_mulai' => '2026-10-15',
                'tujuan' => 'Tujuan Invalid',
                'keperluan' => 'Tanggal mulai melewati tanggal selesai',
            ]);

        $response->assertSessionHasErrors(['tanggal_mulai']);
        $this->assertEquals(0, PengajuanTugasLuar::count());
    }

    public function test_holidays_are_accounted_for_in_3_working_days_calculation(): void
    {
        [$pembimbingUser, $pembimbing] = $this->createPembimbingWithUser('pem_hol');
        [$internUser, $intern] = $this->createInternWithPembimbing($pembimbing, 'Peserta Hol', 'magang_hol');

        Carbon::setTestNow(Carbon::parse('2026-10-13 10:00:00', 'Asia/Makassar'));
        $today = '2026-10-13';

        // Tambah tanggal merah pada Jumat 09 Oktober 2026
        HariLibur::create([
            'tanggal' => '2026-10-09',
            'nama' => 'Libur Khusus Uji Coba',
            'jenis' => 'nasional',
        ]);

        Presensi::create([
            'pengguna_id' => $internUser->id,
            'tanggal' => $today,
            'jam_masuk' => '07:50:00',
            'mode_kerja' => 'onsite',
            'status' => 'hadir',
        ]);

        // Dengan Jumat 09 Oct libur:
        // 1 working day back = Mon 12 Oct
        // 2 working days back = Thu 08 Oct (Fri 09 Oct dilompati karena libur)
        // 3 working days back = Wed 07 Oct
        // Jadi Rabu 07 Oct sekarang adalah hari kerja ke-3 dan harus diterima
        $response = $this->actingAs($internUser)
            ->post(route('presensi.tugas-luar'), [
                'tanggal_selesai' => '2026-10-07',
                'tanggal_mulai' => '2026-10-07',
                'tujuan' => 'Lokasi dengan Hari Libur',
                'keperluan' => 'Submit tanggal 7 yang sekarang valid karena tanggal 9 libur',
            ]);

        $response->assertRedirect(route('presensi.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(1, PengajuanTugasLuar::count());
    }
}
