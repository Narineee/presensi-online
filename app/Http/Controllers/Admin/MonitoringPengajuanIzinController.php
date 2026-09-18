<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;

class MonitoringPengajuanIzinController extends Controller
{
    /**
     * Menampilkan rekap seluruh permohonan izin/sakit (Magang & CS) untuk dipantau oleh Admin.
     */
    public function index(Request $request)
    {
        $query = PengajuanIzin::with(['pengguna.magang', 'pengguna.cs', 'validator.pembimbing']);

        // Filter status persetujuan
        if ($request->filled('status_approval')) {
            $query->where('status_approval', $request->status_approval);
        }

        // Filter jenis izin
        if ($request->filled('jenis_izin')) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        // Filter role pengguna
        if ($request->filled('role')) {
            $query->whereHas('pengguna', function ($q) use ($request) {
                $q->where('role', $request->role);
            });
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
