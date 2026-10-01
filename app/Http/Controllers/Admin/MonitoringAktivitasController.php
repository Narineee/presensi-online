<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonitoringAktivitasController extends Controller
{
    /**
     * Menampilkan rekap seluruh log aktivitas harian Magang untuk dipantau oleh Admin.
     */
    public function index(Request $request)
    {
        $query = Aktivitas::with(['pengguna.magang', 'pekerjaan', 'validator.pembimbing']);

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

        return view('admin.aktivitas.index', compact('aktivitas', 'stats'));
    }

    /**
     * Mencetak laporan rekapitulasi aktivitas harian Magang untuk Admin.
     */
    public function cetak(Request $request)
    {
        $query = Aktivitas::with(['pengguna.magang.divisi', 'pekerjaan', 'validator.pembimbing']);

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('isi', 'like', '%'.$request->search.'%');
        }

        $aktivitas = $query->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $stats = [
            'total' => $aktivitas->count(),
            'approve' => $aktivitas->where('status', 'approve')->count(),
            'pending' => $aktivitas->where('status', 'pending')->count(),
            'revisi' => $aktivitas->where('status', 'revisi')->count(),
        ];

        $divisi = Divisi::first();
        $pembimbing = Pembimbing::first();
        $pengaturan = Pengaturan::getPengaturan();

        return view('admin.aktivitas.cetak', compact('aktivitas', 'stats', 'periodeText', 'divisi', 'pembimbing', 'pengaturan'));
    }
}
