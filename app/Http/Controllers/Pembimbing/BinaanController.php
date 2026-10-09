<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\HariLibur;
use App\Models\Magang;
use App\Models\Pekerjaan;
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
     * Daftar peserta binaan beserta rekap kehadiran (bisa dibatasi rentang tanggal).
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
                'cakupan' => ['mulai' => null, 'selesai' => null, 'total_hari' => 0],
            ]);
        }

        $query = Magang::where('pembimbing_id', $pembimbing->id)
            ->with(['divisi', 'pengguna', 'penempatanMagang.divisi']);

        // Pencarian (nama, NIM, atau asal instansi)
        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', $keyword)
                    ->orWhere('no_induk', 'like', $keyword)
                    ->orWhere('instansi_pendidikan', 'like', $keyword);
            });
        }

        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        // Status magang (aktif / selesai)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $magangList = $query->orderBy('nama_lengkap', 'asc')->get();

        // Rentang tanggal rekap (opsional)
        $rangeStart = $request->filled('tanggal_mulai') ? Carbon::parse($request->tanggal_mulai)->startOfDay() : null;
        $rangeEnd = $request->filled('tanggal_akhir') ? Carbon::parse($request->tanggal_akhir)->startOfDay() : null;

        $allHolidays = $this->getHolidaysLookup();

        $todayStr = Carbon::today()->toDateString();
        $isTodayWeekend = Carbon::today()->isWeekend();
        $isTodayHoliday = isset($allHolidays[$todayStr]);
        $isTodayWorkday = ! $isTodayWeekend && ! $isTodayHoliday;

        // Muat sekaligus presensi dan izin seluruh binaan
        $userIds = $magangList->pluck('pengguna_id')->filter()->values();
        $presensiGrouped = Presensi::whereIn('pengguna_id', $userIds)->get()->groupBy('pengguna_id');
        $approvedIzinGrouped = PengajuanIzin::whereIn('pengguna_id', $userIds)
            ->where('status_approval', 'disetujui')
            ->get()
            ->groupBy('pengguna_id');

        $binaanList = $magangList->map(function ($magang) use ($allHolidays, $presensiGrouped, $approvedIzinGrouped, $todayStr, $isTodayWorkday, $isTodayWeekend, $rangeStart, $rangeEnd) {
            $userPresensi = $presensiGrouped->get($magang->pengguna_id, collect())
                ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

            $userApprovedIzin = $approvedIzinGrouped->get($magang->pengguna_id, collect());

            // 1. Apakah binaan masih aktif
            $isStatusAktif = $magang->status === 'aktif';
            $isPeriodValid = $magang->tanggal_selesai
                ? Carbon::parse($magang->tanggal_selesai)->gte(Carbon::today())
                : true;

            $penempatanAktif = $magang->getPenempatanAt(Carbon::today());
            $hasPenempatan = $magang->penempatanMagang->isNotEmpty();
            $isPenempatanAktif = ! $hasPenempatan || ($penempatanAktif !== null);

            $magang->is_binaan_aktif = $isStatusAktif && $isPeriodValid && $isPenempatanAktif;

            // 2. Status kehadiran HARI INI
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
                $todayStatus = $izinToday->jenis_izin;
            } elseif ($presensiToday) {
                $isTugasLuar = ($presensiToday->status === 'tugas_luar')
                    || ($presensiToday->mode_kerja === 'tugas_luar')
                    || (stripos((string) $presensiToday->keterangan, 'tugas luar') !== false)
                    || (stripos((string) $presensiToday->keterangan, 'dinas luar') !== false);

                $todayStatus = $isTugasLuar ? 'tugas_luar' : ($presensiToday->status ?: 'hadir');
            } else {
                $todayStatus = Carbon::now()->hour >= 16 ? 'alpa' : 'belum_absen';
            }

            $magang->today_status = $todayStatus;
            $magang->presensi_today = $presensiToday;

            // 3. Rekap kehadiran (periode magang, atau rentang filter bila ada)
            $magang->rekap = $this->calculateRekapPresensi($magang, $allHolidays, $userPresensi, $userApprovedIzin, $rangeStart, $rangeEnd);

            return $magang;
        });

        // Metrik HARI INI (binaan aktif)
        $activeBinaanList = $binaanList->filter(fn ($m) => $m->is_binaan_aktif);

        $summaryToday = [
            'binaan_aktif' => $activeBinaanList->count(),
            'binaan_selesai' => $binaanList->filter(fn ($m) => ! $m->is_binaan_aktif)->count(),
            'total_binaan' => $binaanList->count(),
            'hadir_hari_ini' => $activeBinaanList->filter(fn ($m) => in_array($m->today_status, ['hadir', 'tugas_luar']))->count(),
            'sakit_hari_ini' => $activeBinaanList->filter(fn ($m) => $m->today_status === 'sakit')->count(),
            'izin_hari_ini' => $activeBinaanList->filter(fn ($m) => $m->today_status === 'izin')->count(),
            'cuti_hari_ini' => $activeBinaanList->filter(fn ($m) => $m->today_status === 'cuti')->count(),
            'tugas_luar_hari_ini' => $activeBinaanList->filter(fn ($m) => $m->today_status === 'tugas_luar')->count(),
            'alpa_hari_ini' => $activeBinaanList->filter(fn ($m) => in_array($m->today_status, ['alpa', 'belum_absen']))->count(),
            'is_workday' => $isTodayWorkday,
            'today_label' => Carbon::today()->translatedFormat('l, d F Y'),
        ];

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

        // Cakupan rekap untuk kartu di atas daftar
        $cakupan = [
            'mulai' => $binaanList->map(fn ($m) => $m->rekap['cakupan_mulai'])->filter()->min(),
            'selesai' => $binaanList->map(fn ($m) => $m->rekap['cakupan_selesai'])->filter()->max(),
            'total_hari' => (int) $binaanList->max(fn ($m) => $m->rekap['hari_kerja_berjalan']),
        ];

        $divisiList = Divisi::orderBy('nama_divisi', 'asc')->get();

        return view('pembimbing.binaan.index', compact(
            'pembimbing',
            'binaanList',
            'divisiList',
            'summaryToday',
            'summaryKumulatif',
            'cakupan'
        ));
    }

    /**
     * Detail seorang peserta binaan: profil, rekap individual, presensi hari ini, tindak lanjut.
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

        $presensiHariIni = Presensi::where('pengguna_id', $magang->pengguna_id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $aktivitasPending = Aktivitas::where('pengguna_id', $magang->pengguna_id)
            ->where('status', 'pending')
            ->orderBy('tanggal', 'desc')
            ->get();

        $pekerjaanAktif = Pekerjaan::where('magang_id', $magang->id)
            ->where('status', 'aktif')
            ->orderBy('target_selesai')
            ->first();

        return view('pembimbing.binaan.show', compact(
            'pembimbing',
            'magang',
            'rekap',
            'presensiHariIni',
            'aktivitasPending',
            'pekerjaanAktif'
        ));
    }

    /**
     * Riwayat presensi harian seorang peserta binaan ("Catatan terbaru").
     */
    public function riwayat(int $id): View
    {
        $pembimbing = Auth::user()->pembimbing;
        abort_if(! $pembimbing, 403, 'Akses tidak diizinkan.');

        $magang = Magang::where('pembimbing_id', $pembimbing->id)->findOrFail($id);

        $presensi = Presensi::where('pengguna_id', $magang->pengguna_id)
            ->with('pengajuanTugasLuar')
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        $izin = PengajuanIzin::where('pengguna_id', $magang->pengguna_id)
            ->where('status_approval', 'disetujui')
            ->get();

        $jam = fn ($t) => $t ? str_replace(':', '.', substr($t, 0, 5)) : '-';

        $presensi->setCollection($presensi->getCollection()->map(function ($p) use ($izin, $jam) {
            $isIzin = in_array($p->status, ['sakit', 'izin', 'cuti']);
            $label = strtoupper($p->status);

            $terlambat = null;
            if (! $isIzin && $p->jam_masuk && $p->jam_masuk > '08:00:00') {
                $terlambat = (int) ceil((strtotime($p->jam_masuk) - strtotime('08:00:00')) / 60);
            }

            return [
                'tanggal' => $p->tanggal,
                'mode' => $isIzin ? $label : ($p->is_tugas_luar ? 'TUGAS LUAR' : strtoupper((string) $p->mode_kerja)),
                'jam_masuk' => $isIzin ? $label : $jam($p->jam_masuk),
                'jam_pulang' => $isIzin ? $label : $jam($p->jam_keluar),
                'terlambat' => $terlambat,
                'foto_masuk_url' => $p->foto_masuk_url,
                'foto_keluar_url' => $p->foto_keluar_url,
                'lampiran_url' => $isIzin
                    ? $izin->first(fn ($i) => $p->tanggal->betweenIncluded($i->tanggal_mulai, $i->tanggal_selesai))?->bukti_file_url
                    : null,
            ];
        }));

        return view('pembimbing.binaan.riwayat', compact('magang', 'presensi'));
    }

    /**
     * Tanggal merah dari database dan kalender nasional bawaan.
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

        return array_merge(HariLibur::getDaftarLiburNasionalBawaan(), $dbHolidays);
    }

    /**
     * Menghitung rekap kehadiran hari kerja dari awal periode sampai KEMARIN (hari ini tidak ikut dihitung).
     * Tugas Luar dihitung sebagai Hadir sekaligus dicatat di total_tugas_luar (subset dari hadir).
     * Kategori yang saling lepas: hadir (termasuk TL) + sakit + izin + cuti + alpa = hari_kerja_berjalan.
     *
     * @param  array<string, string>  $allHolidays
     */
    private function calculateRekapPresensi(
        Magang $magang,
        array $allHolidays,
        Collection $userPresensi,
        Collection $userApprovedIzin,
        ?Carbon $rangeStart = null,
        ?Carbon $rangeEnd = null
    ): array {
        $startDate = $magang->tanggal_mulai
            ? Carbon::parse($magang->tanggal_mulai)->startOfDay()
            : Carbon::parse($magang->created_at)->startOfDay();

        $endDate = $magang->tanggal_selesai
            ? Carbon::parse($magang->tanggal_selesai)->startOfDay()
            : Carbon::today()->startOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy(), $startDate->copy()];
        }

        // Batasi ke rentang filter (jika ada)
        if ($rangeStart && $rangeStart->gt($startDate)) {
            $startDate = $rangeStart->copy();
        }
        if ($rangeEnd && $rangeEnd->lt($endDate)) {
            $endDate = $rangeEnd->copy();
        }

        // Total hari kerja resmi pada cakupan (tanpa akhir pekan & tanggal merah)
        $totalHariMagang = 0;
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if (! $current->isWeekend() && ! isset($allHolidays[$current->toDateString()])) {
                $totalHariMagang++;
            }
            $current->addDay();
        }

        // Dihitung sampai kemarin saja
        $today = Carbon::today()->startOfDay();
        $effectiveEnd = $endDate->lt($today) ? $endDate->copy() : $today->copy()->subDay();

        $hariKerjaBerjalan = 0;
        $totalHadir = 0;
        $totalSakit = 0;
        $totalIzin = 0;
        $totalCuti = 0;
        $totalTugasLuar = 0;
        $totalAlpa = 0;

        if ($startDate->lte($effectiveEnd)) {
            $cur = $startDate->copy();
            while ($cur->lte($effectiveEnd)) {
                $dateStr = $cur->toDateString();

                if ($cur->isWeekend() || isset($allHolidays[$dateStr])) {
                    $cur->addDay();

                    continue;
                }

                $hariKerjaBerjalan++;

                // Izin/sakit/cuti yang sudah disetujui pembimbing
                $izinResmi = $userApprovedIzin->first(function ($item) use ($cur) {
                    return $cur->betweenIncluded(
                        Carbon::parse($item->tanggal_mulai)->startOfDay(),
                        Carbon::parse($item->tanggal_selesai)->startOfDay()
                    );
                });

                $presensi = $userPresensi->get($dateStr);

                if ($izinResmi) {
                    match ($izinResmi->jenis_izin) {
                        'sakit' => $totalSakit++,
                        'cuti' => $totalCuti++,
                        default => $totalIzin++,
                    };
                } elseif ($presensi) {
                    $isTugasLuar = ($presensi->status === 'tugas_luar')
                        || ($presensi->mode_kerja === 'tugas_luar')
                        || (stripos((string) $presensi->keterangan, 'tugas luar') !== false)
                        || (stripos((string) $presensi->keterangan, 'dinas luar') !== false);

                    if ($isTugasLuar) {
                        $totalTugasLuar++;
                        $totalHadir++;
                    } elseif ($presensi->status === 'sakit') {
                        $totalSakit++;
                    } elseif ($presensi->status === 'cuti') {
                        $totalCuti++;
                    } elseif ($presensi->status === 'izin') {
                        $totalIzin++;
                    } elseif ($presensi->status === 'hadir' || ! empty($presensi->jam_masuk)) {
                        $totalHadir++;
                    } else {
                        $totalAlpa++;
                    }
                } else {
                    // Hari kerja tanpa presensi dan tanpa izin
                    $totalAlpa++;
                }

                $cur->addDay();
            }
        }

        $persentase = $hariKerjaBerjalan > 0
            ? round(($totalHadir / $hariKerjaBerjalan) * 100, 1)
            : 0.0;

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
            'sisa_hari_kerja' => max(0, $totalHariMagang - $hariKerjaBerjalan),
            'cakupan_mulai' => $startDate->toDateString(),
            'cakupan_selesai' => $effectiveEnd->toDateString(),
        ];
    }
}