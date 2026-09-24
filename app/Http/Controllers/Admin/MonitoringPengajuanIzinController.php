<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;

class MonitoringPengajuanIzinController extends Controller
{
    /**
     * Menampilkan rekap seluruh permohonan izin/sakit Magang untuk dipantau oleh Admin.
     */
    public function index(Request $request)
    {
        $query = PengajuanIzin::with(['pengguna.magang', 'validator.pembimbing']);

        // Filter rentang tanggal pengajuan izin (tanggal_mulai & tanggal_akhir)
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_akhir])
                    ->orWhereBetween('tanggal_selesai', [$request->tanggal_mulai, $request->tanggal_akhir])
                    ->orWhere(function ($sub) use ($request) {
                        $sub->where('tanggal_mulai', '<=', $request->tanggal_mulai)
                            ->where('tanggal_selesai', '>=', $request->tanggal_akhir);
                    });
            });
        } elseif ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_selesai', '>=', $request->tanggal_mulai);
        } elseif ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_mulai', '<=', $request->tanggal_akhir);
        } elseif ($request->filled('tanggal')) {
            $query->whereDate('tanggal_mulai', $request->tanggal);
        }

        // Filter status persetujuan
        if ($request->filled('status_approval')) {
            $query->where('status_approval', $request->status_approval);
        }

        // Filter jenis izin
        if ($request->filled('jenis_izin')) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        $pengajuanIzin = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => PengajuanIzin::count(),
            'disetujui' => PengajuanIzin::where('status_approval', 'disetujui')->count(),
            'pending' => PengajuanIzin::where('status_approval', 'pending')->count(),
            'ditolak' => PengajuanIzin::where('status_approval', 'ditolak')->count(),
        ];

        return view('admin.izin.index', compact('pengajuanIzin', 'stats'));
    }
}
