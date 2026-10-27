<?php

namespace App\Http\Controllers;

use App\Models\RencanaKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RencanaKerjaController extends Controller
{
    /**
     * Menampilkan daftar rencana kerja milik peserta magang yang sedang login.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $magang = $user->magang;

        $query = RencanaKerja::where('magang_id', $magang?->id)
            ->with(['pembimbing.divisi'])
            ->withCount('aktivitasDetails');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $query->where(function ($sub) use ($keyword) {
                $sub->where('judul', 'like', $keyword)
                    ->orWhere('deskripsi', 'like', $keyword);
            });
        }

        $rencanaList = $query->orderBy('status', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $allRencana = RencanaKerja::where('magang_id', $magang?->id)->get();
        $stats = [
            'total' => $allRencana->count(),
            'aktif' => $allRencana->where('status', 'aktif')->count(),
            'selesai' => $allRencana->where('status', 'selesai')->count(),
        ];

        return view('rencana.index', compact('user', 'magang', 'rencanaList', 'stats'));
    }

    /**
     * Form tambah rencana kerja baru secara mandiri.
     */
    public function create(): View
    {
        return view('rencana.create');
    }

    /**
     * Simpan rencana kerja baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $magang = Auth::user()->magang;
        abort_if(! $magang, 403, 'Akses khusus peserta magang.');

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ], [
            'judul.required' => 'Judul rencana kerja wajib diisi.',
            'judul.max' => 'Judul rencana kerja maksimal 255 karakter.',
        ]);

        RencanaKerja::create([
            'magang_id' => $magang->id,
            'judul' => trim($request->judul),
            'deskripsi' => $request->deskripsi ? trim($request->deskripsi) : null,
            'status' => 'aktif',
        ]);

        if ($request->redirect_to === 'aktivitas') {
            return redirect()->route('aktivitas.create')
                ->with('success', 'Rencana kerja berhasil ditambahkan! Silakan pilih pada formulir aktivitas.');
        }

        return redirect()->route('rencana.index')
            ->with('success', 'Rencana kerja berhasil ditambahkan.');
    }

    /**
     * Form edit rencana kerja.
     */
    public function edit($id): View
    {
        $magang = Auth::user()->magang;
        abort_if(! $magang, 403, 'Akses khusus peserta magang.');

        $rencana = RencanaKerja::where('magang_id', $magang->id)
            ->withCount('aktivitasDetails')
            ->findOrFail($id);

        return view('rencana.edit', compact('rencana'));
    }

    /**
     * Perbarui rencana kerja.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $magang = Auth::user()->magang;
        abort_if(! $magang, 403, 'Akses khusus peserta magang.');

        $rencana = RencanaKerja::where('magang_id', $magang->id)->findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,selesai,nonaktif',
        ], [
            'judul.required' => 'Judul rencana kerja wajib diisi.',
            'judul.max' => 'Judul rencana kerja maksimal 255 karakter.',
            'status.required' => 'Status rencana kerja wajib dipilih.',
        ]);

        $rencana->update([
            'judul' => trim($request->judul),
            'deskripsi' => $request->deskripsi ? trim($request->deskripsi) : null,
            'status' => $request->status,
        ]);

        return redirect()->route('rencana.index')
            ->with('success', 'Rencana kerja berhasil diperbarui.');
    }

    /**
     * Hapus rencana kerja jika belum memiliki catatan aktivitas.
     */
    public function destroy($id): RedirectResponse
    {
        $magang = Auth::user()->magang;
        abort_if(! $magang, 403, 'Akses khusus peserta magang.');

        $rencana = RencanaKerja::where('magang_id', $magang->id)
            ->withCount('aktivitasDetails')
            ->findOrFail($id);

        if (! $rencana->canBeDeleted()) {
            return redirect()->back()->with('error', 'Rencana kerja ini tidak dapat dihapus karena sudah memiliki '.$rencana->aktivitas_details_count.' riwayat aktivitas. Anda dapat mengubah statusnya menjadi "Selesai" atau "Nonaktif".');
        }

        $rencana->delete();

        return redirect()->route('rencana.index')
            ->with('success', 'Rencana kerja berhasil dihapus.');
    }
}
