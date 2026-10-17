<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\PengajuanTugasLuar;
use App\Models\Pengaturan;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonitoringPresensiController extends Controller
{
    /**
     * Menampilkan seluruh rekap presensi Magang untuk dipantau oleh Admin.
     */
    public function index(Request $request)
    {
        $query = Presensi::with([
            'pengguna.magang.pembimbing',
            'pengguna.magang.divisi',
            'pengguna.magang.penempatanMagang.pembimbing',
            'pengguna.magang.penempatanMagang.divisi',
        ]);

        // Filter rentang tanggal (tanggal_mulai & tanggal_akhir)
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_akhir]);
        } elseif ($request->filled('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        } elseif ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        } elseif ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        // Filter mode kerja (onsite / wfh)
        if ($request->filled('mode_kerja')) {
            $query->where('mode_kerja', $request->mode_kerja);
        }

        $presensi = $query->orderBy('tanggal', 'desc')->orderBy('jam_masuk', 'desc')->paginate(15)->withQueryString();

        // Hitung ringkasan hari ini
        $today = Carbon::today()->toDateString();
        $statsToday = [
            'total_hadir' => Presensi::where('tanggal', $today)->where('status', 'hadir')->count(),
            'total_onsite' => Presensi::where('tanggal', $today)->where('mode_kerja', 'onsite')->count(),
            'total_wfh' => Presensi::where('tanggal', $today)->where('mode_kerja', 'wfh')->count(),
            'total_sudah_pulang' => Presensi::where('tanggal', $today)->whereNotNull('jam_keluar')->count(),
        ];

        $filterTanggal = $request->input('tanggal', Carbon::today()->toDateString());

        return view('admin.presensi.index', compact('presensi', 'statsToday', 'filterTanggal'));
    }

    /**
     * Mencetak laporan rekapitulasi presensi peserta Magang untuk Admin.
     */
    public function cetak(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $singleTanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');

        if ($tanggalMulai && $tanggalAkhir) {
            $startDate = Carbon::parse($tanggalMulai)->startOfDay();
            $endDate = Carbon::parse($tanggalAkhir)->startOfDay();
        } elseif ($tanggalMulai) {
            $startDate = Carbon::parse($tanggalMulai)->startOfDay();
            $endDate = Carbon::today()->startOfDay();
        } elseif ($tanggalAkhir) {
            $endDate = Carbon::parse($tanggalAkhir)->startOfDay();
            $startDate = $endDate->copy()->startOfMonth();
        } elseif ($singleTanggal) {
            $startDate = Carbon::parse($singleTanggal)->startOfDay();
            $endDate = Carbon::parse($singleTanggal)->startOfDay();
        } elseif ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth()->startOfDay();
        } else {
            // Default: bulan berjalan
            $startDate = Carbon::today()->startOfMonth()->startOfDay();
            $endDate = Carbon::today()->endOfMonth()->startOfDay();
        }

        if ($startDate->gt($endDate)) {
            $temp = $startDate->copy();
            $startDate = $endDate->copy();
            $endDate = $temp;
        }

        $periodeAwalText = $startDate->locale('id')->isoFormat('D MMMM Y');
        $periodeAkhirText = $endDate->locale('id')->isoFormat('D MMMM Y');
        $periodeSubJudul = 'Dari Tanggal '.$periodeAwalText.' Sampai Tanggal '.$periodeAkhirText;
        $periodeText = $periodeSubJudul;

        // Ambil data hari libur nasional untuk lookup cepat
        $allHolidays = HariLibur::pluck('keterangan', 'tanggal')
            ->mapWithKeys(fn ($v, $k) => [Carbon::parse($k)->toDateString() => $v])
            ->toArray();

        // Query seluruh peserta magang untuk Admin
        $query = Magang::with([
            'pengguna',
            'divisi',
            'pembimbing',
            'penempatanMagang.divisi',
            'penempatanMagang.pembimbing',
        ]);

        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        if ($request->filled('pembimbing_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('pembimbing_id', $request->pembimbing_id)
                    ->orWhereHas('penempatanMagang', fn ($pq) => $pq->where('pembimbing_id', $request->pembimbing_id));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q') || $request->filled('search')) {
            $keyword = '%'.($request->q ?? $request->search).'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', $keyword)
                    ->orWhere('no_induk', 'like', $keyword)
                    ->orWhere('instansi_pendidikan', 'like', $keyword);
            });
        }

        $magangList = $query->orderBy('nama_lengkap', 'asc')->get();

        $userIds = $magangList->pluck('pengguna_id')->filter()->values();

        // Eager load presensi, izin yang disetujui, dan tugas luar yang disetujui
        $presensiQuery = Presensi::whereIn('pengguna_id', $userIds)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($request->filled('mode_kerja')) {
            $presensiQuery->where('mode_kerja', $request->mode_kerja);
        }

        $presensiGrouped = $presensiQuery->get()->groupBy('pengguna_id');

        $approvedIzinGrouped = PengajuanIzin::whereIn('pengguna_id', $userIds)
            ->where('status_approval', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $endDate->toDateString())
            ->whereDate('tanggal_selesai', '>=', $startDate->toDateString())
            ->get()
            ->groupBy('pengguna_id');

        $approvedTugasLuarGrouped = PengajuanTugasLuar::whereIn('pengguna_id', $userIds)
            ->where('status_verifikasi', 'disetujui')
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy('pengguna_id');

        $today = Carbon::today()->startOfDay();

        $rekapList = $magangList->map(function ($magang) use (
            $startDate,
            $endDate,
            $today,
            $allHolidays,
            $presensiGrouped,
            $approvedIzinGrouped,
            $approvedTugasLuarGrouped
        ) {
            $userPresensi = $presensiGrouped->get($magang->pengguna_id, collect())
                ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

            $userApprovedIzin = $approvedIzinGrouped->get($magang->pengguna_id, collect());

            $userApprovedTugasLuar = $approvedTugasLuarGrouped->get($magang->pengguna_id, collect())
                ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

            // Tentukan rentang aktif magang
            $magangStart = $magang->tanggal_mulai
                ? Carbon::parse($magang->tanggal_mulai)->startOfDay()
                : ($magang->created_at ? Carbon::parse($magang->created_at)->startOfDay() : null);

            $magangEnd = $magang->tanggal_selesai
                ? Carbon::parse($magang->tanggal_selesai)->startOfDay()
                : null;

            // Format teks periode magang
            if ($magang->tanggal_mulai && $magang->tanggal_selesai) {
                $periodeMagangTeks = Carbon::parse($magang->tanggal_mulai)->locale('id')->isoFormat('D MMM Y')
                    .' - '
                    .Carbon::parse($magang->tanggal_selesai)->locale('id')->isoFormat('D MMM Y');
            } elseif ($magang->tanggal_mulai) {
                $periodeMagangTeks = 'Mulai '.Carbon::parse($magang->tanggal_mulai)->locale('id')->isoFormat('D MMM Y');
            } else {
                $periodeMagangTeks = '-';
            }

            // Pembimbing aktif
            $pembimbingAktif = $magang->getPembimbingAt($endDate) ?? $magang->pembimbing;
            $pembimbingNama = $pembimbingAktif ? $pembimbingAktif->nama_lengkap : '-';

            // Hitung irisan tanggal evaluasi antara filter periode dan masa magang
            $effStart = $startDate->copy();
            $effEnd = $endDate->copy();

            if ($magangStart && $magangStart->gt($effStart)) {
                $effStart = $magangStart->copy();
            }
            if ($magangEnd && $magangEnd->lt($effEnd)) {
                $effEnd = $magangEnd->copy();
            }

            $totalHadir = 0;
            $totalSakit = 0;
            $totalIzin = 0;
            $totalAlfa = 0;
            $totalTugasLuar = 0;

            if ($effStart->lte($effEnd)) {
                $cur = $effStart->copy();
                while ($cur->lte($effEnd)) {
                    $dateStr = $cur->toDateString();

                    // Lewatkan hari libur & akhir pekan
                    if ($cur->isWeekend() || isset($allHolidays[$dateStr])) {
                        $cur->addDay();

                        continue;
                    }

                    // Jangan hitung tanggal di masa depan
                    if ($cur->gt($today)) {
                        $cur->addDay();

                        continue;
                    }

                    $izinResmi = $userApprovedIzin->first(function ($item) use ($cur) {
                        return $cur->betweenIncluded(
                            Carbon::parse($item->tanggal_mulai)->startOfDay(),
                            Carbon::parse($item->tanggal_selesai)->startOfDay()
                        );
                    });

                    $presensi = $userPresensi->get($dateStr);
                    $tugasLuar = $userApprovedTugasLuar->get($dateStr);

                    if ($izinResmi) {
                        if ($izinResmi->jenis_izin === 'sakit') {
                            $totalSakit++;
                        } else {
                            $totalIzin++;
                        }
                    } elseif ($presensi || $tugasLuar) {
                        $isTugasLuar = $tugasLuar
                            || ($presensi && (
                                $presensi->status === 'tugas_luar'
                                || $presensi->mode_kerja === 'tugas_luar'
                                || stripos((string) $presensi->keterangan, 'tugas luar') !== false
                                || stripos((string) $presensi->keterangan, 'dinas luar') !== false
                            ));

                        if ($isTugasLuar) {
                            $totalTugasLuar++;
                            $totalHadir++;
                        } elseif ($presensi->status === 'sakit') {
                            $totalSakit++;
                        } elseif ($presensi->status === 'cuti' || $presensi->status === 'izin') {
                            $totalIzin++;
                        } elseif ($presensi->status === 'hadir' || ! empty($presensi->jam_masuk)) {
                            $totalHadir++;
                        } else {
                            $totalAlfa++;
                        }
                    } else {
                        // Tidak ada presensi dan tidak ada izin pada hari kerja
                        if ($cur->lt($today) || Carbon::now()->hour >= 16) {
                            $totalAlfa++;
                        }
                    }

                    $cur->addDay();
                }
            }

            return (object) [
                'id' => $magang->id,
                'nama_lengkap' => $magang->nama_lengkap,
                'no_induk' => $magang->no_induk,
                'divisi' => $magang->divisi,
                'pembimbing_nama' => $pembimbingNama,
                'pembimbing_nip' => $pembimbingAktif?->nip,
                'periode_teks' => $periodeMagangTeks,
                'total_hadir' => $totalHadir,
                'total_sakit' => $totalSakit,
                'total_izin' => $totalIzin,
                'total_alpa' => $totalAlfa,
                'total_tugas_luar' => $totalTugasLuar,
            ];
        });

        $stats = [
            'total_peserta' => $rekapList->count(),
            'total_hadir' => $rekapList->sum('total_hadir'),
            'total_sakit' => $rekapList->sum('total_sakit'),
            'total_izin' => $rekapList->sum('total_izin'),
            'total_alpa' => $rekapList->sum('total_alpa'),
            'total_tugas_luar' => $rekapList->sum('total_tugas_luar'),
        ];

        $pengaturan = Pengaturan::getPengaturan();

        return view('admin.presensi.cetak', compact(
            'rekapList',
            'stats',
            'periodeSubJudul',
            'periodeAwalText',
            'periodeAkhirText',
            'periodeText',
            'pengaturan'
        ));
    }
}
