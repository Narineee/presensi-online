<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\HariLibur;
use App\Models\Magang;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BinaanController extends Controller
{
    /**
     * Menampilkan daftar peserta magang binaan beserta rekapitulasi kehadiran lengkap.
     */
    public function index(Request $request): View
    {
        $pembimbing = Auth::user()->pembimbing;

        if (! $pembimbing) {
            return view('pembimbing.binaan.index', [
                'pembimbing' => null,
                'binaanList' => collect(),
                'divisiList' => collect(),
                'summaryToday' => [
                    'binaan_aktif' => 0,
                    'binaan_selesai' => 0,
                    'total_binaan' => 0,
                    'hadir_hari_ini' => 0,
                    'sakit_hari_ini' => 0,
                    'izin_hari_ini' => 0,
                    'cuti_hari_ini' => 0,
                    'tugas_luar_hari_ini' => 0,
                    'alpa_hari_ini' => 0,
                    'is_workday' => true,
                    'today_label' => Carbon::today()->translatedFormat('l, d F Y'),
                ],
                'summaryKumulatif' => [
                    'total_peserta' => 0,
                    'total_hari_magang' => 0,
                    'total_hadir' => 0,
                    'total_sakit' => 0,
                    'total_izin' => 0,
                    'total_cuti' => 0,
                    'total_tugas_luar' => 0,
                    'total_alpa' => 0,
                    'rata_rata_kehadiran' => 0,
                ],
            ]);
        }

        $query = Magang::where('pembimbing_id', $pembimbing->id)
            ->with(['divisi', 'pengguna', 'penempatanMagang.divisi']);

        // Filter pencarian (Nama, NIM, atau Asal Instansi)
        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', $keyword)
                    ->orWhere('no_induk', 'like', $keyword)
                    ->orWhere('instansi_pendidikan', 'like', $keyword);
            });
        }

        // Filter Divisi
        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        // Filter Status Magang (Aktif / Selesai)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $magangList = $query->orderBy('nama_lengkap', 'asc')->get();

        // Siapkan set hari libur nasional untuk pengecekan cepat O(1)
        $allHolidays = $this->getHolidaysLookup();

        $todayStr = Carbon::today()->toDateString();
        $isTodayWeekend = Carbon::today()->isWeekend();
        $isTodayHoliday = isset($allHolidays[$todayStr]);
        $isTodayWorkday = ! $isTodayWeekend && ! $isTodayHoliday;

        // Batch pre-load data presensi dan izin untuk seluruh anak magang binaan
        $userIds = $magangList->pluck('pengguna_id')->filter()->values();
        $presensiGrouped = Presensi::whereIn('pengguna_id', $userIds)
            ->get()
            ->groupBy('pengguna_id');

        $approvedIzinGrouped = PengajuanIzin::whereIn('pengguna_id', $userIds)
            ->where('status_approval', 'disetujui')
            ->get()
            ->groupBy('pengguna_id');

        // Kalkulasi rekapitulasi kehadiran untuk tiap peserta binaan
        $binaanList = $magangList->map(function ($magang) use ($allHolidays, $presensiGrouped, $approvedIzinGrouped, $todayStr, $isTodayWorkday, $isTodayWeekend) {
            $userPresensi = $presensiGrouped->get($magang->pengguna_id, collect())
                ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

            $userApprovedIzin = $approvedIzinGrouped->get($magang->pengguna_id, collect());

            // 1. Cek apakah binaan masih aktif di divisi
            $isStatusAktif = $magang->status === 'aktif';
            $isPeriodValid = $magang->tanggal_selesai
                ? Carbon::parse($magang->tanggal_selesai)->gte(Carbon::today())
                : true;

            $penempatanAktif = $magang->getPenempatanAt(Carbon::today());
            $hasPenempatan = $magang->penempatanMagang->isNotEmpty();
            $isPenempatanAktif = ! $hasPenempatan || ($penempatanAktif !== null);

            $isBinaanAktif = $isStatusAktif && $isPeriodValid && $isPenempatanAktif;
            $magang->is_binaan_aktif = $isBinaanAktif;

            // 2. Evaluasi Status Kehadiran Khusus HARI INI
            $presensiToday = $userPresensi->get($todayStr);
            $izinToday = $userApprovedIzin->first(function ($item) use ($todayStr) {
                return Carbon::parse($todayStr)->betweenIncluded(
                    Carbon::parse($item->tanggal_mulai)->startOfDay(),
                    Carbon::parse($item->tanggal_selesai)->startOfDay()
                );
            });

            $todayStatus = 'belum_absen';
            if (! $isTodayWorkday) {
                $todayStatus = $isTodayWeekend ? 'weekend' : 'libur';
            } elseif ($izinToday) {
                $todayStatus = $izinToday->jenis_izin; // 'sakit', 'izin', atau 'cuti'
            } elseif ($presensiToday) {
                $isTugasLuar = ($presensiToday->status === 'tugas_luar')
                    || ($presensiToday->mode_kerja === 'tugas_luar')
                    || (stripos((string) $presensiToday->keterangan, 'tugas luar') !== false)
                    || (stripos((string) $presensiToday->keterangan, 'dinas luar') !== false);

                if ($isTugasLuar) {
                    $todayStatus = 'tugas_luar';
                } else {
                    $todayStatus = $presensiToday->status ?: 'hadir';
                }
            } else {
                if (Carbon::now()->hour >= 16) {
                    $todayStatus = 'alpa';
                } else {
                    $todayStatus = 'belum_absen';
                }
            }

            $magang->today_status = $todayStatus;
            $magang->presensi_today = $presensiToday;

            // 3. Rekapitulasi Kumulatif Periode Magang Keseluruhan
            $rekap = $this->calculateRekapPresensi($magang, $allHolidays, $userPresensi, $userApprovedIzin);
            $magang->rekap = $rekap;

            return $magang;
        });

        // Hitung Metrik HARI INI (khusus binaan aktif)
        $activeBinaanList = $binaanList->filter(fn ($m) => $m->is_binaan_aktif);
        $binaanSelesaiCount = $binaanList->filter(fn ($m) => ! $m->is_binaan_aktif)->count();

        $hadirHariIni = $activeBinaanList->filter(fn ($m) => in_array($m->today_status, ['hadir', 'tugas_luar']))->count();
        $sakitHariIni = $activeBinaanList->filter(fn ($m) => $m->today_status === 'sakit')->count();
        $izinHariIni = $activeBinaanList->filter(fn ($m) => $m->today_status === 'izin')->count();
        $cutiHariIni = $activeBinaanList->filter(fn ($m) => $m->today_status === 'cuti')->count();
        $tugasLuarHariIni = $activeBinaanList->filter(fn ($m) => $m->today_status === 'tugas_luar')->count();
        $alpaHariIni = $activeBinaanList->filter(fn ($m) => in_array($m->today_status, ['alpa', 'belum_absen']))->count();

        $summaryToday = [
            'binaan_aktif' => $activeBinaanList->count(),
            'binaan_selesai' => $binaanSelesaiCount,
            'total_binaan' => $binaanList->count(),
            'hadir_hari_ini' => $hadirHariIni,
            'sakit_hari_ini' => $sakitHariIni,
            'izin_hari_ini' => $izinHariIni,
            'cuti_hari_ini' => $cutiHariIni,
            'tugas_luar_hari_ini' => $tugasLuarHariIni,
            'alpa_hari_ini' => $alpaHariIni,
            'is_workday' => $isTodayWorkday,
            'today_label' => Carbon::today()->translatedFormat('l, d F Y'),
        ];

        // Hitung Penghitungan KESELURUHAN (Kumulatif di Bagian Bawah)
        $summaryKumulatif = [
            'total_peserta' => $binaanList->count(),
            'total_hari_magang' => $binaanList->sum(fn ($m) => $m->rekap['total_hari_magang']),
            'total_hadir' => $binaanList->sum(fn ($m) => $m->rekap['total_hadir']),
            'total_sakit' => $binaanList->sum(fn ($m) => $m->rekap['total_sakit']),
            'total_izin' => $binaanList->sum(fn ($m) => $m->rekap['total_izin']),
            'total_cuti' => $binaanList->sum(fn ($m) => $m->rekap['total_cuti']),
            'total_tugas_luar' => $binaanList->sum(fn ($m) => $m->rekap['total_tugas_luar']),
            'total_alpa' => $binaanList->sum(fn ($m) => $m->rekap['total_alpa']),
            'rata_rata_kehadiran' => $binaanList->count() > 0
                ? round($binaanList->avg(fn ($m) => $m->rekap['persentase_kehadiran']), 1)
                : 0,
        ];

        $divisiList = Divisi::orderBy('nama_divisi', 'asc')->get();

        return view('pembimbing.binaan.index', compact(
            'pembimbing',
            'binaanList',
            'divisiList',
            'summaryToday',
            'summaryKumulatif'
        ));
    }

    /**
     * Menampilkan detail riwayat dan rekapitulasi lengkap seorang peserta binaan.
     */
    public function show(int $id): View
    {
        $pembimbing = Auth::user()->pembimbing;

        if (! $pembimbing) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $magang = Magang::where('pembimbing_id', $pembimbing->id)
            ->with(['divisi', 'pengguna', 'penempatanMagang.divisi'])
            ->findOrFail($id);

        $allHolidays = $this->getHolidaysLookup();

        $userPresensi = Presensi::where('pengguna_id', $magang->pengguna_id)
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

        $userApprovedIzin = PengajuanIzin::where('pengguna_id', $magang->pengguna_id)
            ->where('status_approval', 'disetujui')
            ->get();

        $rekap = $this->calculateRekapPresensi($magang, $allHolidays, $userPresensi, $userApprovedIzin);

        // Susun rincian log harian dari tanggal mulai s/d hari ini
        $logHarian = $this->buildDailyLogTimeline($magang, $allHolidays, $userPresensi, $userApprovedIzin);

        return view('pembimbing.binaan.show', compact(
            'pembimbing',
            'magang',
            'rekap',
            'logHarian'
        ));
    }

    /**
     * Mendapatkan lookup array tanggal merah dari database dan kalender resmi nasional.
     *
     * @return array<string, string>
     */
    private function getHolidaysLookup(): array
    {
        $dbHolidays = HariLibur::all()->mapWithKeys(function ($item) {
            $tgl = Carbon::parse($item->tanggal)->toDateString();
            $nama = $item->nama ?: ($item->keterangan ?: 'Hari Libur Nasional');

            return [$tgl => $nama];
        })->toArray();

        $defaultHolidays = HariLibur::getDaftarLiburNasionalBawaan();

        return array_merge($defaultHolidays, $dbHolidays);
    }

    /**
     * Menghitung total hari kerja magang dan rekapitulasi kehadiran sampai hari ini.
     *
     * @param  array<string, string>  $allHolidays
     * @return array{
     *     total_hari_magang: int,
     *     hari_kerja_berjalan: int,
     *     total_hadir: int,
     *     total_sakit: int,
     *     total_izin: int,
     *     total_cuti: int,
     *     total_tugas_luar: int,
     *     total_alpa: int,
     *     persentase_kehadiran: float,
     *     sisa_hari_kerja: int,
     *     status_hari_ini: string,
     * }
     */
    private function calculateRekapPresensi(
        Magang $magang,
        array $allHolidays,
        Collection $userPresensi,
        Collection $userApprovedIzin
    ): array {
        $startDate = $magang->tanggal_mulai
            ? Carbon::parse($magang->tanggal_mulai)->startOfDay()
            : Carbon::parse($magang->created_at)->startOfDay();

        $endDate = $magang->tanggal_selesai
            ? Carbon::parse($magang->tanggal_selesai)->startOfDay()
            : Carbon::today()->startOfDay();

        if ($startDate->gt($endDate)) {
            $temp = $startDate->copy();
            $startDate = $endDate->copy();
            $endDate = $temp;
        }

        // 1. Hitung Total Hari Magang Resmi (Hanya Hari Kerja, Libur / Tanggal Merah Tidak Dihitung)
        $totalHariMagang = 0;
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            if (! $current->isWeekend() && ! isset($allHolidays[$dateStr])) {
                $totalHariMagang++;
            }
            $current->addDay();
        }

        // 2. Evaluasi Kehadiran Sampai Hari Ini Dulu
        $today = Carbon::today()->startOfDay();
        $effectiveEnd = $endDate->lt($today) ? $endDate->copy() : $today->copy();

        $hariKerjaBerjalan = 0;
        $totalHadir = 0;
        $totalSakit = 0;
        $totalIzin = 0;
        $totalCuti = 0;
        $totalTugasLuar = 0;
        $totalAlpa = 0;
        $statusHariIni = 'Belum Dimulai';

        if ($startDate->lte($effectiveEnd)) {
            $cur = $startDate->copy();
            while ($cur->lte($effectiveEnd)) {
                $dateStr = $cur->toDateString();
                $isWeekend = $cur->isWeekend();
                $isHoliday = isset($allHolidays[$dateStr]);

                // Lewati akhir pekan dan tanggal merah
                if ($isWeekend || $isHoliday) {
                    $cur->addDay();

                    continue;
                }

                $hariKerjaBerjalan++;
                $isToday = $cur->equalTo($today);

                // Cek surat permohonan izin/sakit yang telah disetujui pembimbing
                $izinResmi = $userApprovedIzin->first(function ($item) use ($cur) {
                    return $cur->betweenIncluded(
                        Carbon::parse($item->tanggal_mulai)->startOfDay(),
                        Carbon::parse($item->tanggal_selesai)->startOfDay()
                    );
                });

                $presensi = $userPresensi->get($dateStr);

                if ($izinResmi) {
                    if ($izinResmi->jenis_izin === 'sakit') {
                        $totalSakit++;
                        if ($isToday) {
                            $statusHariIni = 'Sakit (Izin Resmi)';
                        }
                    } elseif ($izinResmi->jenis_izin === 'cuti') {
                        $totalCuti++;
                        if ($isToday) {
                            $statusHariIni = 'Cuti Resmi';
                        }
                    } else {
                        $totalIzin++;
                        if ($isToday) {
                            $statusHariIni = 'Izin Resmi';
                        }
                    }
                } elseif ($presensi) {
                    $isTugasLuar = ($presensi->status === 'tugas_luar')
                        || ($presensi->mode_kerja === 'tugas_luar')
                        || (stripos((string) $presensi->keterangan, 'tugas luar') !== false)
                        || (stripos((string) $presensi->keterangan, 'dinas luar') !== false);

                    if ($isTugasLuar) {
                        $totalTugasLuar++;
                        $totalHadir++;
                        if ($isToday) {
                            $statusHariIni = 'Tugas Luar';
                        }
                    } elseif ($presensi->status === 'sakit') {
                        $totalSakit++;
                        if ($isToday) {
                            $statusHariIni = 'Sakit';
                        }
                    } elseif ($presensi->status === 'cuti') {
                        $totalCuti++;
                        if ($isToday) {
                            $statusHariIni = 'Cuti';
                        }
                    } elseif ($presensi->status === 'izin') {
                        $totalIzin++;
                        if ($isToday) {
                            $statusHariIni = 'Izin';
                        }
                    } elseif ($presensi->status === 'alpa') {
                        $totalAlpa++;
                        if ($isToday) {
                            $statusHariIni = 'Alpa / Tidak Hadir';
                        }
                    } elseif ($presensi->status === 'hadir' || ! empty($presensi->jam_masuk)) {
                        $totalHadir++;
                        if ($isToday) {
                            $jamMasukFmt = $presensi->jam_masuk ? substr($presensi->jam_masuk, 0, 5) : 'Masuk';
                            $statusHariIni = 'Hadir ('.$jamMasukFmt.')';
                        }
                    } else {
                        $totalAlpa++;
                        if ($isToday) {
                            $statusHariIni = 'Tidak Hadir';
                        }
                    }
                } else {
                    // Hari kerja tanpa presensi dan tanpa izin
                    if ($cur->lt($today)) {
                        $totalAlpa++;
                    } elseif ($isToday) {
                        // Hari ini sedang berlangsung
                        if (Carbon::now()->hour >= 16) {
                            $totalAlpa++;
                            $statusHariIni = 'Alpa (Tidak Presensi)';
                        } else {
                            $statusHariIni = 'Belum Presensi Hari Ini';
                        }
                    }
                }

                $cur->addDay();
            }
        }

        $persentase = $hariKerjaBerjalan > 0
            ? round(($totalHadir / $hariKerjaBerjalan) * 100, 1)
            : 0.0;

        $sisaHariKerja = max(0, $totalHariMagang - $hariKerjaBerjalan);

        return [
            'total_hari_magang' => $totalHariMagang,
            'hari_kerja_berjalan' => $hariKerjaBerjalan,
            'total_hadir' => $totalHadir,
            'total_sakit' => $totalSakit,
            'total_izin' => $totalIzin,
            'total_cuti' => $totalCuti,
            'total_tugas_luar' => $totalTugasLuar,
            'total_alpa' => $totalAlpa,
            'persentase_kehadiran' => $persentase,
            'sisa_hari_kerja' => $sisaHariKerja,
            'status_hari_ini' => $statusHariIni,
        ];
    }

    /**
     * Menyusun riwayat log harian peserta dari awal magang hingga hari ini.
     *
     * @param  array<string, string>  $allHolidays
     */
    private function buildDailyLogTimeline(
        Magang $magang,
        array $allHolidays,
        Collection $userPresensi,
        Collection $userApprovedIzin
    ): Collection {
        $startDate = $magang->tanggal_mulai
            ? Carbon::parse($magang->tanggal_mulai)->startOfDay()
            : Carbon::parse($magang->created_at)->startOfDay();

        $endDate = $magang->tanggal_selesai
            ? Carbon::parse($magang->tanggal_selesai)->startOfDay()
            : Carbon::today()->startOfDay();

        if ($startDate->gt($endDate)) {
            $temp = $startDate->copy();
            $startDate = $endDate->copy();
            $endDate = $temp;
        }

        $today = Carbon::today()->startOfDay();
        $effectiveEnd = $endDate->lt($today) ? $endDate->copy() : $today->copy();

        $logs = collect();

        if ($startDate->gt($effectiveEnd)) {
            return $logs;
        }

        $cur = $effectiveEnd->copy();
        // Iterasi terbalik dari hari terbaru ke terlama
        while ($cur->gte($startDate)) {
            $dateStr = $cur->toDateString();
            $isWeekend = $cur->isWeekend();
            $isHoliday = isset($allHolidays[$dateStr]);
            $holidayName = $allHolidays[$dateStr] ?? null;

            $presensi = $userPresensi->get($dateStr);

            $izinResmi = $userApprovedIzin->first(function ($item) use ($cur) {
                return $cur->betweenIncluded(
                    Carbon::parse($item->tanggal_mulai)->startOfDay(),
                    Carbon::parse($item->tanggal_selesai)->startOfDay()
                );
            });

            if ($isWeekend) {
                $status = 'weekend';
                $keterangan = 'Akhir Pekan (Sabtu/Minggu)';
            } elseif ($isHoliday) {
                $status = 'libur';
                $keterangan = 'Hari Libur Nasional: '.$holidayName;
            } elseif ($izinResmi) {
                $status = $izinResmi->jenis_izin;
                $keterangan = 'Permohonan '.ucfirst($izinResmi->jenis_izin).' disetujui: '.$izinResmi->alasan;
            } elseif ($presensi) {
                $isTugasLuar = ($presensi->status === 'tugas_luar')
                    || ($presensi->mode_kerja === 'tugas_luar')
                    || (stripos((string) $presensi->keterangan, 'tugas luar') !== false)
                    || (stripos((string) $presensi->keterangan, 'dinas luar') !== false);

                if ($isTugasLuar) {
                    $status = 'tugas_luar';
                    $keterangan = $presensi->keterangan ?: 'Tugas Luar Kantor / Lapangan';
                } else {
                    $status = $presensi->status;
                    $keterangan = $presensi->keterangan ?: 'Presensi '.ucfirst($presensi->status);
                }
            } else {
                if ($cur->lt($today)) {
                    $status = 'alpa';
                    $keterangan = 'Tidak hadir tanpa keterangan (Alpa)';
                } else {
                    $status = Carbon::now()->hour >= 16 ? 'alpa' : 'belum_absen';
                    $keterangan = Carbon::now()->hour >= 16 ? 'Tidak hadir tanpa keterangan' : 'Belum melakukan presensi hari ini';
                }
            }

            $logs->push([
                'tanggal' => $cur->copy(),
                'tanggal_str' => $dateStr,
                'hari' => $cur->translatedFormat('l'),
                'is_hari_kerja' => ! $isWeekend && ! $isHoliday,
                'status' => $status,
                'jam_masuk' => $presensi && $presensi->jam_masuk ? substr($presensi->jam_masuk, 0, 5) : null,
                'jam_keluar' => $presensi && $presensi->jam_keluar ? substr($presensi->jam_keluar, 0, 5) : null,
                'mode_kerja' => $presensi ? $presensi->mode_kerja : null,
                'lokasi_masuk' => $presensi ? $presensi->lokasi_masuk : null,
                'keterangan' => $keterangan,
            ]);

            $cur->subDay();
        }

        return $logs;
    }
}
