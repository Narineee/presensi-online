<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Magang;
use App\Models\RencanaKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RencanaKerjaController extends Controller
{
    private function getPembimbing()
    {
        return Auth::user()->pembimbing;
    }

    /**
     * Menampilkan daftar rencana kerja seluruh peserta binaan (Monitoring / Read-only).
     */
    public function index(Request $request): View
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $supervisedMagangIds = Magang::where('pembimbing_id', $pembimbing->id)->pluck('id');

        $query = RencanaKerja::whereIn('magang_id', $supervisedMagangIds)
            ->with(['magang.divisi'])
            ->withCount('aktivitasDetails');

        if ($request->filled('magang_id')) {
            $query->where('magang_id', $request->magang_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $keyword = $request->input('q', $request->input('search'));
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('magang', function ($m) use ($keyword) {
                        $m->where('nama_lengkap', 'like', "%{$keyword}%")
                            ->orWhere('no_induk', 'like', "%{$keyword}%");
                    });
            });
        }

        $rencanaList = $query->orderBy('status', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        $supervisedMagang = Magang::where('pembimbing_id', $pembimbing->id)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('pembimbing.rencana.index', compact('rencanaList', 'supervisedMagang'));
    }

    /**
     * Menampilkan detail rencana kerja peserta binaan beserta riwayat progres aktivitasnya.
     */
    public function show($id): View
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $supervisedMagangIds = Magang::where('pembimbing_id', $pembimbing->id)->pluck('id');

        $rencana = RencanaKerja::whereIn('magang_id', $supervisedMagangIds)
            ->with(['magang.divisi', 'magang.pengguna'])
            ->findOrFail($id);

        $riwayatAktivitas = $rencana->aktivitasDetails()
            ->with(['aktivitas.validator.pembimbing'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('pembimbing.rencana.show', compact('rencana', 'riwayatAktivitas'));
    }
}
