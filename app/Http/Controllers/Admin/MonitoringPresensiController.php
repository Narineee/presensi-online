<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Pembimbing;
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

    /**
     * Mencetak laporan rekapitulasi presensi seluruh peserta (Magang & CS) untuk Admin.
     */
    public function cetak(Request $request)
    {
        $query = Presensi::with(['pengguna.magang.divisi', 'pengguna.cs']);

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $singleTanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');

        if ($tanggalMulai && $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);
            $periodeText = Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y').' s/d '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
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

        $presensi = $query->orderBy('tanggal', 'asc')
            ->orderBy('jam_masuk', 'asc')
            ->get();

        $stats = [
            'total' => $presensi->count(),
            'hadir' => $presensi->where('status', 'hadir')->count(),
            'onsite' => $presensi->where('mode_kerja', 'onsite')->count(),
            'wfh' => $presensi->where('mode_kerja', 'wfh')->count(),
            'magang' => $presensi->filter(fn ($p) => $p->pengguna && $p->pengguna->role === 'magang')->count(),
            'cs' => $presensi->filter(fn ($p) => $p->pengguna && $p->pengguna->role === 'cs')->count(),
        ];

        $divisi = Divisi::first();
        $pembimbing = Pembimbing::first();

        return view('admin.presensi.cetak', compact('presensi', 'stats', 'periodeText', 'divisi', 'pembimbing'));
    }
}
