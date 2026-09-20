<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Cs;
use App\Models\Magang;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard pembimbing beserta daftar peserta yang diampu dan pantau kehadiran.
     */
    public function index(Request $request): View
    {
        $pembimbing = Auth::user()->pembimbing;

        if (! $pembimbing) {
            return view('pembimbing.dashboard', [
                'pembimbing' => null,
                'magangList' => collect(),
                'csList' => collect(),
                'presensiHariIni' => collect(),
                'stats' => [
                    'pending_aktivitas' => 0,
                    'pending_izin' => 0,
                    'hadir_hari_ini' => 0,
                    'total_magang' => 0,
                    'total_cs' => 0,
                    'total_binaan' => 0,
                ],
            ]);
        }

        $magangList = Magang::where('pembimbing_id', $pembimbing->id)
            ->with(['divisi', 'penilaian', 'pengguna'])
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $csList = Cs::where('pembimbing_id', $pembimbing->id)
            ->with(['jadwalShift.shift', 'pengguna'])
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $magangUserIds = $magangList->pluck('pengguna_id')->filter();
        $csUserIds = $csList->pluck('pengguna_id')->filter();
        $allSupervisedUserIds = $magangUserIds->merge($csUserIds)->values();

        // Presensi hari ini seluruh peserta binaan (Pantau Kehadiran)
        $today = Carbon::today()->toDateString();
        $presensiHariIni = Presensi::whereIn('pengguna_id', $allSupervisedUserIds)
            ->whereDate('tanggal', $today)
            ->with(['pengguna.magang', 'pengguna.cs'])
            ->get();

        // Ringkasan operasional pembimbing
        $stats = [
            'pending_aktivitas' => Aktivitas::whereIn('pengguna_id', $allSupervisedUserIds)->where('status', 'pending')->count(),
            'pending_izin' => PengajuanIzin::whereIn('pengguna_id', $allSupervisedUserIds)->where('status_approval', 'pending')->count(),
            'hadir_hari_ini' => $presensiHariIni->whereNotNull('jam_masuk')->count(),
            'total_magang' => $magangList->count(),
            'total_cs' => $csList->count(),
            'total_binaan' => $magangList->count() + $csList->count(),
        ];

        return view('pembimbing.dashboard', compact(
            'pembimbing',
            'magangList',
            'csList',
            'presensiHariIni',
            'stats'
        ));
    }
}
