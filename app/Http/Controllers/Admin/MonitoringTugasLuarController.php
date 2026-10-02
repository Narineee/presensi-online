<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\PengajuanTugasLuar;
use Illuminate\Http\Request;

class MonitoringTugasLuarController extends Controller
{
    /**
     * Menampilkan rekap seluruh pengajuan Tugas Luar peserta Magang untuk dipantau oleh Admin.
     */
    public function index(Request $request)
    {
        $query = PengajuanTugasLuar::with(['pengguna.magang.divisi', 'presensi', 'validator.pembimbing']);

        // Filter tanggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_akhir]);
        } elseif ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Filter status verifikasi
        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        }

        // Filter divisi
        if ($request->filled('divisi_id')) {
            $query->whereHas('pengguna.magang', function ($m) use ($request) {
                $m->where('divisi_id', $request->divisi_id);
            });
        }

        // Filter pencarian
        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('tujuan', 'like', $keyword)
                    ->orWhere('keperluan', 'like', $keyword)
                    ->orWhereHas('pengguna.magang', function ($m) use ($keyword) {
                        $m->where('nama_lengkap', 'like', $keyword)
                            ->orWhere('no_induk', 'like', $keyword);
                    });
            });
        }

        $pengajuanTugasLuar = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $divisiList = Divisi::orderBy('nama_divisi', 'asc')->get();

        $stats = [
            'total' => PengajuanTugasLuar::count(),
            'disetujui' => PengajuanTugasLuar::where('status_verifikasi', 'disetujui')->count(),
            'menunggu' => PengajuanTugasLuar::where('status_verifikasi', 'menunggu')->count(),
            'ditolak' => PengajuanTugasLuar::where('status_verifikasi', 'ditolak')->count(),
        ];

        return view('admin.tugas_luar.index', compact('pengajuanTugasLuar', 'stats', 'divisiList'));
    }
}
