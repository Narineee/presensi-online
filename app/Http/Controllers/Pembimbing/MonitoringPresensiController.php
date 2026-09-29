<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MonitoringPresensiController extends Controller
{
    /**
     * Mendapatkan profil pembimbing dari pengguna yang sedang login.
     */
    private function getPembimbing(): ?Pembimbing
    {
        return Auth::user()->pembimbing;
    }

    /**
     * Mendapatkan daftar peserta magang yang dibimbing oleh pembimbing yang login.
     */
    private function getSupervisedMagangList()
    {
        $pembimbing = $this->getPembimbing();
        if (! $pembimbing) {
            return collect();
        }

        return Magang::where('pembimbing_id', $pembimbing->id)
            ->with(['divisi', 'pengguna'])
            ->orderBy('nama_lengkap', 'asc')
            ->get();
    }

    /**
     * Menampilkan riwayat presensi seluruh peserta magang binaan pembimbing.
     */
    public function index(Request $request): View
    {
        $pembimbing = $this->getPembimbing();
        $magangList = $this->getSupervisedMagangList();
        $supervisedUserIds = $magangList->pluck('pengguna_id')->filter()->values();

        $query = Presensi::whereIn('pengguna_id', $supervisedUserIds)
            ->with(['pengguna.magang.divisi']);

        // Filter peserta magang tertentu (hanya yang dibina oleh pembimbing ini)
        $selectedMagang = null;
        if ($request->filled('magang_id')) {
            $selectedMagang = $magangList->firstWhere('id', (int) $request->magang_id);
            if ($selectedMagang && $selectedMagang->pengguna_id) {
                $query->where('pengguna_id', $selectedMagang->pengguna_id);
            }
        }

        // Filter rentang tanggal
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

        // Filter status kehadiran (hadir / izin / sakit / cuti / alpa)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $presensi = $query->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Statistik ringkasan hari ini untuk peserta binaan
        $today = Carbon::today()->toDateString();
        $todayPresensi = Presensi::whereIn('pengguna_id', $supervisedUserIds)
            ->whereDate('tanggal', $today)
            ->get();

        $statsToday = [
            'total_binaan' => $magangList->count(),
            'total_hadir' => $todayPresensi->where('status', 'hadir')->count(),
            'total_onsite' => $todayPresensi->where('mode_kerja', 'onsite')->count(),
            'total_wfh' => $todayPresensi->where('mode_kerja', 'wfh')->count(),
            'total_sudah_pulang' => $todayPresensi->whereNotNull('jam_keluar')->count(),
        ];

        return view('pembimbing.presensi.index', compact(
            'pembimbing',
            'magangList',
            'selectedMagang',
            'presensi',
            'statsToday'
        ));
    }

    /**
     * Mencetak laporan rekapitulasi riwayat presensi peserta magang binaan.
     */
    public function cetak(Request $request): View
    {
        $pembimbing = $this->getPembimbing();
        $magangList = $this->getSupervisedMagangList();
        $supervisedUserIds = $magangList->pluck('pengguna_id')->filter()->values();

        $query = Presensi::whereIn('pengguna_id', $supervisedUserIds)
            ->with(['pengguna.magang.divisi']);

        $selectedMagang = null;
        if ($request->filled('magang_id')) {
            $selectedMagang = $magangList->firstWhere('id', (int) $request->magang_id);
            if ($selectedMagang && $selectedMagang->pengguna_id) {
                $query->where('pengguna_id', $selectedMagang->pengguna_id);
            }
        }

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $singleTanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');

        if ($tanggalMulai && $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);
            $periodeText = Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y').' s/d '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
        } elseif ($tanggalMulai) {
            $query->where('tanggal', '>=', $tanggalMulai);
            $periodeText = 'Mulai '.Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y');
        } elseif ($tanggalAkhir) {
            $query->where('tanggal', '<=', $tanggalAkhir);
            $periodeText = 'Sampai '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
        } elseif ($singleTanggal) {
            $query->whereDate('tanggal', $singleTanggal);
            $periodeText = Carbon::parse($singleTanggal)->isoFormat('D MMMM Y');
        } elseif ($bulan) {
            $query->whereYear('tanggal', substr($bulan, 0, 4))
                ->whereMonth('tanggal', substr($bulan, 5, 2));
            $periodeText = Carbon::createFromFormat('Y-m', $bulan)->isoFormat('MMMM Y');
        } else {
            // Default bulan berjalan
            $query->whereYear('tanggal', Carbon::today()->year)
                ->whereMonth('tanggal', Carbon::today()->month);
            $periodeText = Carbon::today()->isoFormat('MMMM Y');
        }

        if ($request->filled('mode_kerja')) {
            $query->where('mode_kerja', $request->mode_kerja);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $presensi = $query->orderBy('tanggal', 'asc')
            ->orderBy('jam_masuk', 'asc')
            ->get();

        $stats = [
            'total' => $presensi->count(),
            'hadir' => $presensi->where('status', 'hadir')->count(),
            'onsite' => $presensi->where('mode_kerja', 'onsite')->count(),
            'wfh' => $presensi->where('mode_kerja', 'wfh')->count(),
            'izin_sakit' => $presensi->whereIn('status', ['izin', 'sakit', 'cuti'])->count(),
        ];

        $divisi = $selectedMagang?->divisi ?? $magangList->first()?->divisi ?? Divisi::first();
        $pengaturan = Pengaturan::getPengaturan();

        return view('pembimbing.presensi.cetak', compact(
            'presensi',
            'stats',
            'periodeText',
            'selectedMagang',
            'pembimbing',
            'divisi',
            'pengaturan'
        ));
    }
}
