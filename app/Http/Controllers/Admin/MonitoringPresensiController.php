<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonitoringPresensiController extends Controller
{
    /**
     * Menampilkan seluruh rekap presensi (Magang & CS) untuk dipantau oleh Admin.
     */
    public function index(Request $request)
    {
        $query = Presensi::with(['pengguna.magang', 'pengguna.cs']);

        // Filter tanggal (default: hari ini jika tidak dipilih)
        $filterTanggal = $request->input('tanggal', Carbon::today()->toDateString());
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $filterTanggal);
        }

        // Filter role (magang / cs)
        if ($request->filled('role')) {
            $query->whereHas('pengguna', function ($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        // Filter mode kerja (onsite / wfh)
        if ($request->filled('mode_kerja')) {
            $query->where('mode_kerja', $request->mode_kerja);
        }

        $presensi = $query->orderBy('jam_masuk', 'desc')->paginate(15)->withQueryString();

        // Hitung ringkasan hari ini
        $today = Carbon::today()->toDateString();
        $statsToday = [
            'total_hadir' => Presensi::where('tanggal', $today)->where('status', 'hadir')->count(),
            'total_onsite' => Presensi::where('tanggal', $today)->where('mode_kerja', 'onsite')->count(),
            'total_wfh' => Presensi::where('tanggal', $today)->where('mode_kerja', 'wfh')->count(),
            'total_sudah_pulang' => Presensi::where('tanggal', $today)->whereNotNull('jam_keluar')->count(),
        ];

        return view('admin.presensi.index', compact('presensi', 'statsToday', 'filterTanggal'));
    }
}
