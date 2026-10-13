<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\HariLibur;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapHubMetrikBaruTest extends TestCase
{
    use RefreshDatabase;

    private function createInternUserWithPeriod(string $startDate, string $endDate): array
    {
        $pembimbingUser = Pengguna::create([
            'username' => 'pem_rekap',
            'password' => 'secret123',
            'role' => 'pembimbing',
            'is_active' => true,
        ]);

        $pembimbing = Pembimbing::create([
            'pengguna_id' => $pembimbingUser->id,
            'nip' => '198001012000031001',
            'nama_lengkap' => 'Pembimbing Rekap',
            'jabatan' => 'Pembimbing',
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
            'username' => 'intern_rekap',
            'password' => 'secret123',
            'role' => 'magang',
            'is_active' => true,
        ]);

        $magang = Magang::create([
            'pengguna_id' => $internUser->id,
            'pembimbing_id' => $pembimbing->id,
            'divisi_id' => $divisi->id,
            'no_induk' => 'NIM12345',
            'nama_lengkap' => 'Intern Rekap',
            'tanggal_mulai' => $startDate,
            'tanggal_selesai' => $endDate,
            'status' => 'aktif',
            'face_registered_at' => now(),
        ]);

        return [$internUser, $magang];
    }

    public function test_rekap_hub_displays_prd_example_period_progress_attendance_and_hours(): void
    {
        // Sesuai contoh PRD:
        // Periode: 14 September 2026 – 14 November 2026 (45 hari kerja)
        // Hari ini: 13 Oktober 2026 (22 hari kerja berlalu)
        // Progres periode: 49%
        [$internUser, $magang] = $this->createInternUserWithPeriod('2026-09-14', '2026-11-14');

        Carbon::setTestNow(Carbon::parse('2026-10-13 10:00:00', 'Asia/Makassar'));

        // Hadir 20 hari kerja normal (08:00 - 16:00 = 8 jam per hari, total 160 jam)
        // Buat 20 catatan presensi
        $current = Carbon::parse('2026-09-14');
        $hadirCount = 0;
        while ($hadirCount < 20 && $current->lte(Carbon::parse('2026-10-13'))) {
            if (! $current->isWeekend()) {
                Presensi::create([
                    'pengguna_id' => $internUser->id,
                    'tanggal' => $current->toDateString(),
                    'jam_masuk' => '07:55:00',
                    'jam_keluar' => '16:05:00',
                    'mode_kerja' => 'onsite',
                    'status' => 'hadir',
                ]);
                $hadirCount++;
            }
            $current->addDay();
        }

        $response = $this->actingAs($internUser)->get(route('magang.rekap'));

        $response->assertStatus(200);

        // 1. Progres Periode (Atas)
        $response->assertSee('Progres periode');
        $response->assertSee('Selasa, 13 Oktober 2026');
        $response->assertSee('22 dari 45 hari kerja berlalu');
        $response->assertSee('49%'); // 22 / 45 = 48.88% -> 49%

        // 2. Kehadiran Berdasarkan Hari
        $response->assertSee('Kehadiran');
        $response->assertSee('20 dari 45 hari');
        $response->assertSee('44%'); // 20 / 45 = 44.44% -> 44%

        // 3. Jam Magang
        $response->assertSee('Jam Magang');
        $response->assertSee('360 jam'); // 45 * 8 = 360 jam
        $response->assertSee('160 dari 360 jam');

        // 4. Memastikan info aktivitas dihapus dari kartu ringkasan
        $response->assertDontSee('Semua terisi');
        $response->assertDontSee('Belum terisi');

        // 5. Memastikan 4 menu riwayat di bawahnya tetap ada
        $response->assertSee('Riwayat Presensi');
        $response->assertSee('Riwayat Aktivitas');
        $response->assertSee('Riwayat Permohonan Ketidakhadiran');
        $response->assertSee('Lembar nilai magang');
    }

    public function test_working_days_exclude_national_holidays_properly(): void
    {
        [$internUser, $magang] = $this->createInternUserWithPeriod('2026-10-05', '2026-10-16');

        // 2 minggu: Senin 5 Okt - Jumat 16 Okt = 10 hari kerja
        // Tambahkan 1 hari libur nasional di hari Rabu 7 Okt
        HariLibur::create([
            'tanggal' => '2026-10-07',
            'keterangan' => 'Libur Nasional Contoh',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', 'Asia/Makassar'));
        // Hari kerja berlalu dari 5 Okt s.d. 9 Okt = 5 hari minus 1 libur = 4 hari berlalu
        // Total target hari kerja = 10 hari minus 1 libur = 9 hari

        $response = $this->actingAs($internUser)->get(route('magang.rekap'));

        $response->assertStatus(200);
        $response->assertSee('4 dari 9 hari kerja berlalu');
        $response->assertSee('44%'); // 4 / 9 = 44.44% -> 44%
    }
}
