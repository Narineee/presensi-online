<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Pembimbing;
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
        $query = Presensi::with(['pengguna.magang']);

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
        $query = Presensi::with(['pengguna.magang.divisi']);

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
            'magang' => $presensi->count(),
        ];

        $divisi = Divisi::first();
        $pembimbing = Pembimbing::first();
        $pengaturan = Pengaturan::getPengaturan();

        return view('admin.presensi.cetak', compact('presensi', 'stats', 'periodeText', 'divisi', 'pembimbing', 'pengaturan'));
    }
}
