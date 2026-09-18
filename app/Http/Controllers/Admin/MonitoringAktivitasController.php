<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use Illuminate\Http\Request;

class MonitoringAktivitasController extends Controller
{
    /**
     * Menampilkan rekap seluruh log aktivitas harian (Magang & CS) untuk dipantau oleh Admin.
     */
    public function index(Request $request)
    {
        $query = Aktivitas::with(['pengguna.magang', 'pengguna.cs', 'validator.pembimbing']);

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter role pengguna
        if ($request->filled('role')) {
            $query->whereHas('pengguna', function ($q) use ($request) {
                $q->where('role', $request->role);
            });
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
}
