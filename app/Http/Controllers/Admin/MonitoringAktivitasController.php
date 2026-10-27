<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonitoringAktivitasController extends Controller
{
    public function __construct(
        protected PdfService $pdfService
    ) {}

    /**
     * Menampilkan rekap seluruh log aktivitas harian Magang untuk dipantau oleh Admin.
     */
    public function index(Request $request)
    {
        $query = Aktivitas::with([
            'pengguna.magang.divisi',
            'pengguna.magang.pembimbing',
            'pengguna.magang.penempatanMagang.pembimbing',
            'pekerjaan',
            'validator.pembimbing',
            'details.rencanaKerja',
        ]);

        // Filter peserta magang tertentu
        if ($request->filled('magang_id')) {
            $selectedMagang = Magang::find($request->magang_id);
            if ($selectedMagang && $selectedMagang->pengguna_id) {
                $query->where('pengguna_id', $selectedMagang->pengguna_id);
            }
        }

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

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pencarian isi
        if ($request->filled('search')) {
            $query->where('isi', 'like', '%'.$request->search.'%');
        }

        $aktivitas = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Aktivitas::count(),
            'approve' => Aktivitas::where('status', 'approve')->count(),
            'pending' => Aktivitas::where('status', 'pending')->count(),
            'revisi' => Aktivitas::where('status', 'revisi')->count(),
        ];

        $magangList = Magang::orderBy('nama_lengkap', 'asc')->get();

        return view('admin.aktivitas.index', compact('aktivitas', 'stats', 'magangList'));
    }

    /**
     * Mencetak laporan rekapitulasi aktivitas peserta Magang untuk Admin langsung dalam format PDF A4.
     */
    public function cetak(Request $request)
    {
        $today = Carbon::today();
        $bulanParam = $request->input('bulan');
        $tanggalMulaiParam = $request->input('tanggal_mulai');
        $tanggalAkhirParam = $request->input('tanggal_akhir');
        $singleTanggalParam = $request->input('tanggal');

        $hasSpecificDateFilter = $request->filled('tanggal_mulai') || $request->filled('tanggal_akhir') || $request->filled('tanggal') || $request->filled('bulan');

        if ($tanggalMulaiParam && $tanggalAkhirParam) {
            $startDate = Carbon::parse($tanggalMulaiParam)->startOfDay();
            $endDate = Carbon::parse($tanggalAkhirParam)->endOfDay();
            $periodeSubJudul = 'Dari Tanggal '.$startDate->locale('id')->isoFormat('D MMMM Y').' Sampai Tanggal '.$endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAwalText = $startDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAkhirText = $endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeText = $startDate->isoFormat('D MMMM Y').' s/d '.$endDate->isoFormat('D MMMM Y');
        } elseif ($tanggalMulaiParam) {
            $startDate = Carbon::parse($tanggalMulaiParam)->startOfDay();
            $endDate = $today->copy()->endOfDay();
            $periodeSubJudul = 'Dari Tanggal '.$startDate->locale('id')->isoFormat('D MMMM Y').' Sampai Tanggal '.$endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAwalText = $startDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAkhirText = $endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeText = 'Mulai '.$startDate->isoFormat('D MMMM Y');
        } elseif ($tanggalAkhirParam) {
            $startDate = Carbon::parse($tanggalAkhirParam)->startOfMonth()->startOfDay();
            $endDate = Carbon::parse($tanggalAkhirParam)->endOfDay();
            $periodeSubJudul = 'Sampai Tanggal '.$endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAwalText = $startDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAkhirText = $endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeText = 'Sampai '.$endDate->isoFormat('D MMMM Y');
        } elseif ($singleTanggalParam) {
            $startDate = Carbon::parse($singleTanggalParam)->startOfDay();
            $endDate = Carbon::parse($singleTanggalParam)->endOfDay();
            $periodeSubJudul = 'Tanggal '.$startDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAwalText = $startDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAkhirText = $endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeText = $startDate->isoFormat('D MMMM Y');
        } elseif ($bulanParam && preg_match('/^\d{4}-\d{2}$/', $bulanParam)) {
            $startDate = Carbon::createFromFormat('Y-m-d', $bulanParam.'-01')->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            $periodeSubJudul = 'Bulan '.$startDate->locale('id')->isoFormat('MMMM Y');
            $periodeAwalText = $startDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAkhirText = $endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeText = $startDate->isoFormat('MMMM Y');
        } else {
            // Default bulan berjalan
            $startDate = $today->copy()->startOfMonth()->startOfDay();
            $endDate = $today->copy()->endOfMonth()->endOfDay();
            $periodeSubJudul = 'Bulan '.$startDate->locale('id')->isoFormat('MMMM Y');
            $periodeAwalText = $startDate->locale('id')->isoFormat('D MMMM Y');
            $periodeAkhirText = $endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeText = $today->isoFormat('MMMM Y');
        }

        $statusFilter = $request->input('status');
        $searchFilter = $request->input('search');

        // Query peserta magang (seluruh peserta magang, atau spesifik jika difilter)
        $magangQuery = Magang::with([
            'divisi',
            'pembimbing',
            'penempatanMagang.pembimbing',
            'penempatanMagang.divisi',
            'rencanaKerja' => function ($q) {
                $q->orderBy('id', 'asc');
            },
            'pengguna.aktivitas' => function ($q) use ($hasSpecificDateFilter, $startDate, $endDate, $statusFilter, $searchFilter) {
                if ($hasSpecificDateFilter) {
                    $q->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]);
                }

                if ($statusFilter) {
                    $q->where('status', $statusFilter);
                }

                if ($searchFilter) {
                    $q->where('isi', 'like', '%'.$searchFilter.'%');
                }

                $q->with(['details.rencanaKerja', 'pekerjaan']);
            },
        ]);

        if ($request->filled('magang_id')) {
            $magangQuery->where('id', $request->magang_id);
        }

        if ($request->filled('divisi_id')) {
            $magangQuery->where('divisi_id', $request->divisi_id);
        }

        $magangList = $magangQuery->orderBy('nama_lengkap', 'asc')->get();

        $rekapList = $magangList->map(function ($magang) use ($startDate, $endDate) {
            $userAktivitas = $magang->pengguna ? $magang->pengguna->aktivitas : collect();

            // Pembimbing pada periode ini
            $penempatanPembimbings = $magang->penempatanMagang
                ->filter(function ($p) use ($startDate, $endDate) {
                    $pStart = Carbon::parse($p->tanggal_mulai)->startOfDay();
                    $pEnd = Carbon::parse($p->tanggal_selesai)->startOfDay();

                    return $pStart->lte($endDate) && $pEnd->gte($startDate);
                })
                ->pluck('pembimbing')
                ->filter();

            if ($penempatanPembimbings->isNotEmpty()) {
                $pembimbingNama = $penempatanPembimbings->pluck('nama_lengkap')->unique()->implode(', ');
                $pembimbingNip = $penempatanPembimbings->last()?->nip;
            } else {
                $pembimbingAktif = $magang->getPembimbingAt($endDate) ?? $magang->pembimbing;
                $pembimbingNama = $pembimbingAktif ? $pembimbingAktif->nama_lengkap : '-';
                $pembimbingNip = $pembimbingAktif?->nip;
            }

            // Periode magang peserta
            if ($magang->tanggal_mulai && $magang->tanggal_selesai) {
                $periodeMagangTeks = Carbon::parse($magang->tanggal_mulai)->locale('id')->isoFormat('D MMM Y')
                    .' - '
                    .Carbon::parse($magang->tanggal_selesai)->locale('id')->isoFormat('D MMM Y');
            } elseif ($magang->tanggal_mulai) {
                $periodeMagangTeks = 'Mulai '.Carbon::parse($magang->tanggal_mulai)->locale('id')->isoFormat('D MMM Y');
            } else {
                $periodeMagangTeks = '-';
            }

            // Rencana kerja peserta diurutkan (1 s.d. N)
            $rencanaKerjaList = $magang->rencanaKerja->pluck('judul')->filter()->values()->all();

            // Fallback: jika belum ada di tabel rencana_kerja, ambil dari detail aktivitas
            if (empty($rencanaKerjaList) && $userAktivitas->isNotEmpty()) {
                $rencanaKerjaList = $userAktivitas->flatMap(function ($akt) {
                    return $akt->details ? $akt->details->pluck('judul_rencana') : collect();
                })->filter()->unique()->values()->all();
            }

            // Fallback kedua: pekerjaan (jika sistem warisan)
            if (empty($rencanaKerjaList) && $userAktivitas->isNotEmpty()) {
                $rencanaKerjaList = $userAktivitas->pluck('pekerjaan.judul')->filter()->unique()->values()->all();
            }

            // Status hasil pekerjaan / rencana kerja (Selesai / Belum)
            $allDetails = $userAktivitas->flatMap(function ($akt) {
                return $akt->details ?? collect();
            });

            if ($allDetails->isNotEmpty()) {
                $totalDetails = $allDetails->count();
                $selesaiCount = $allDetails->filter(fn ($d) => strcasecmp(trim($d->progres), 'selesai') === 0)->count();
                if ($selesaiCount === $totalDetails) {
                    $progresTeks = 'Selesai';
                } elseif ($selesaiCount > 0) {
                    $progresTeks = 'Sebagian Selesai';
                } else {
                    $progresTeks = 'Belum';
                }
            } elseif ($userAktivitas->isNotEmpty()) {
                $hasApproved = $userAktivitas->contains('status', 'approve');
                $progresTeks = $hasApproved ? 'Selesai' : 'Belum';
            } else {
                $progresTeks = '-';
            }

            return (object) [
                'id' => $magang->id,
                'nama_lengkap' => $magang->nama_lengkap,
                'no_induk' => $magang->no_induk,
                'divisi' => $magang->divisi,
                'pembimbing_nama' => $pembimbingNama,
                'pembimbing_nip' => $pembimbingNip,
                'periode_teks' => $periodeMagangTeks,
                'rencana_kerja_list' => $rencanaKerjaList,
                'progres_akhir' => $progresTeks,
                'total_aktivitas' => $userAktivitas->count(),
            ];
        });

        $totalAktivitasAll = $rekapList->sum('total_aktivitas');

        $stats = [
            'total_peserta' => $rekapList->count(),
            'total_selesai' => $rekapList->where('progres_akhir', 'Selesai')->count(),
            'total_aktivitas' => $totalAktivitasAll,
        ];

        $pengaturan = Pengaturan::getPengaturan();

        $logoPath = public_path('storage/images/logo-kalsel.png');
        if (! file_exists($logoPath)) {
            $logoPath = storage_path('app/public/images/logo-kalsel.png');
        }
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        $viewData = compact(
            'rekapList',
            'stats',
            'periodeSubJudul',
            'periodeAwalText',
            'periodeAkhirText',
            'periodeText',
            'pengaturan',
            'logoBase64'
        );

        if ($request->input('format') === 'html') {
            return view('admin.aktivitas.cetak', $viewData);
        }

        $cleanAwal = str_replace([' ', '/', '\\'], '-', $periodeAwalText);
        $cleanAkhir = str_replace([' ', '/', '\\'], '-', $periodeAkhirText);
        $filename = 'Laporan-Aktivitas-Magang-'.$cleanAwal.'-sd-'.$cleanAkhir.'.pdf';

        return $this->pdfService->renderView('admin.aktivitas.cetak', $viewData, $filename, 'P');
    }
}
