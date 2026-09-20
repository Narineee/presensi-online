<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Cs;
use App\Models\Divisi;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama Super Admin beserta ringkasan status sistem.
     */
    public function index(): View
    {
        $today = Carbon::today()->toDateString();

        $stats = [
            'total_pengguna' => Pengguna::count(),
            'total_divisi' => Divisi::count(),
            'total_pembimbing' => Pembimbing::count(),
            'total_magang' => Magang::count(),
            'total_cs' => Cs::count(),
            'total_kriteria' => KriteriaPenilaian::count(),
            'presensi_today' => Presensi::whereDate('tanggal', $today)->count(),
            'aktivitas_today' => Aktivitas::whereDate('tanggal', $today)->count(),
            'izin_pending' => PengajuanIzin::where('status_approval', 'pending')->count(),
            'total_shift' => Shift::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
