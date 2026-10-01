<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Magang;
use App\Models\Pekerjaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PekerjaanController extends Controller
{
    /**
     * Mendapatkan entitas pembimbing yang sedang login.
     */
    private function getPembimbing()
    {
        return Auth::user()->pembimbing;
    }

    /**
     * Menampilkan daftar pekerjaan magang yang dibina oleh pembimbing.
     */
    public function index(Request $request): View
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $query = Pekerjaan::where('pembimbing_id', $pembimbing->id)
            ->with(['magang.divisi'])
            ->withCount('aktivitas');

        // Filter berdasarkan peserta magang binaan tertentu
        if ($request->filled('magang_id')) {
            $query->where('magang_id', $request->magang_id);
        }

        // Filter berdasarkan jenis (proyek / rutin)
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        // Filter status (aktif / selesai / nonaktif)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pencarian judul / deskripsi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhereHas('magang', function ($m) use ($search) {
                        $m->where('nama_lengkap', 'like', "%{$search}%");
                    });
            });
        }

        $pekerjaanList = $query->orderBy('status', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        $supervisedMagang = Magang::where('pembimbing_id', $pembimbing->id)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $stats = [
            'total' => Pekerjaan::where('pembimbing_id', $pembimbing->id)->count(),
            'proyek' => Pekerjaan::where('pembimbing_id', $pembimbing->id)->where('jenis', 'proyek')->count(),
            'rutin' => Pekerjaan::where('pembimbing_id', $pembimbing->id)->where('jenis', 'rutin')->count(),
            'aktif' => Pekerjaan::where('pembimbing_id', $pembimbing->id)->where('status', 'aktif')->count(),
            'selesai' => Pekerjaan::where('pembimbing_id', $pembimbing->id)->where('status', 'selesai')->count(),
        ];

        return view('pembimbing.pekerjaan.index', compact('pekerjaanList', 'supervisedMagang', 'stats'));
    }

    /**
     * Menampilkan form pembuatan pekerjaan baru untuk peserta magang binaan.
     */
    public function create(Request $request): View
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $supervisedMagang = Magang::where('pembimbing_id', $pembimbing->id)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $selectedMagangId = $request->input('magang_id');

        return view('pembimbing.pekerjaan.create', compact('supervisedMagang', 'selectedMagangId'));
    }

    /**
     * Menyimpan pekerjaan baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        // Pastikan magang_id benar-benar anak binaan pembimbing yang sedang login
        $allowedMagangIds = Magang::where('pembimbing_id', $pembimbing->id)->pluck('id')->toArray();

        $request->validate([
            'magang_id' => ['required', 'in:'.implode(',', $allowedMagangIds)],
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis' => 'required|in:proyek,rutin',
            'tanggal_mulai' => 'nullable|date',
            'target_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'progress' => 'nullable|integer|min:0|max:100',
        ], [
            'magang_id.required' => 'Pilih peserta magang penerima pekerjaan ini.',
            'magang_id.in' => 'Peserta magang yang dipilih bukan merupakan anak binaan Anda.',
            'judul.required' => 'Judul pekerjaan wajib diisi.',
            'jenis.required' => 'Jenis pekerjaan (Proyek / Rutin) wajib ditentukan.',
            'target_selesai.after_or_equal' => 'Target selesai tidak boleh mendahului tanggal mulai.',
        ]);

        $isProyek = ($request->jenis === 'proyek');
        $progress = $isProyek ? (int) ($request->progress ?? 0) : null;
        $status = ($isProyek && $progress >= 100) ? 'selesai' : 'aktif';

        Pekerjaan::create([
            'pembimbing_id' => $pembimbing->id,
            'magang_id' => $request->magang_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis' => $request->jenis,
            'progress' => $progress,
            'tanggal_mulai' => $request->tanggal_mulai,
            'target_selesai' => $request->target_selesai,
            'status' => $status,
        ]);

        return redirect()->route('pembimbing.pekerjaan.index', ['magang_id' => $request->magang_id])
            ->with('success', 'Pekerjaan berhasil dibuat untuk peserta magang.');
    }

    /**
     * Menampilkan detail pekerjaan beserta daftar aktivitas harian di dalamnya.
     */
    public function show($id): View
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $pekerjaan = Pekerjaan::where('pembimbing_id', $pembimbing->id)
            ->with(['magang.divisi', 'magang.pengguna'])
            ->findOrFail($id);

        $aktivitas = $pekerjaan->aktivitas()
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('pembimbing.pekerjaan.show', compact('pekerjaan', 'aktivitas'));
    }

    /**
     * Menampilkan form edit pekerjaan.
     */
    public function edit($id): View
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $pekerjaan = Pekerjaan::where('pembimbing_id', $pembimbing->id)
            ->with('magang')
            ->findOrFail($id);

        $supervisedMagang = Magang::where('pembimbing_id', $pembimbing->id)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('pembimbing.pekerjaan.edit', compact('pekerjaan', 'supervisedMagang'));
    }

    /**
     * Memperbarui data pekerjaan.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $pekerjaan = Pekerjaan::where('pembimbing_id', $pembimbing->id)
            ->findOrFail($id);

        $allowedMagangIds = Magang::where('pembimbing_id', $pembimbing->id)->pluck('id')->toArray();

        $request->validate([
            'magang_id' => ['required', 'in:'.implode(',', $allowedMagangIds)],
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis' => 'required|in:proyek,rutin',
            'status' => 'required|in:aktif,selesai,nonaktif',
            'tanggal_mulai' => 'nullable|date',
            'target_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'progress' => 'nullable|integer|min:0|max:100',
        ], [
            'magang_id.required' => 'Pilih peserta magang penerima pekerjaan ini.',
            'magang_id.in' => 'Peserta magang yang dipilih bukan merupakan anak binaan Anda.',
            'judul.required' => 'Judul pekerjaan wajib diisi.',
            'target_selesai.after_or_equal' => 'Target selesai tidak boleh mendahului tanggal mulai.',
        ]);

        $isProyek = ($request->jenis === 'proyek');
        $progress = $isProyek ? (int) ($request->progress ?? 0) : null;
        $status = $request->status;

        if ($isProyek && $progress >= 100 && $status === 'aktif') {
            $status = 'selesai';
        }

        $pekerjaan->update([
            'magang_id' => $request->magang_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis' => $request->jenis,
            'progress' => $progress,
            'tanggal_mulai' => $request->tanggal_mulai,
            'target_selesai' => $request->target_selesai,
            'status' => $status,
        ]);

        return redirect()->route('pembimbing.pekerjaan.show', $pekerjaan->id)
            ->with('success', 'Data pekerjaan berhasil diperbarui.');
    }

    /**
     * Menghapus pekerjaan jika belum memiliki relasi aktivitas harian.
     */
    public function destroy($id): RedirectResponse
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $pekerjaan = Pekerjaan::where('pembimbing_id', $pembimbing->id)
            ->withCount('aktivitas')
            ->findOrFail($id);

        if ($pekerjaan->aktivitas_count > 0) {
            return redirect()->back()->with('error', 'Pekerjaan ini tidak dapat dihapus karena sudah memiliki '.$pekerjaan->aktivitas_count.' catatan aktivitas harian. Anda dapat mengubah statusnya menjadi "Nonaktif".');
        }

        $pekerjaan->delete();

        return redirect()->route('pembimbing.pekerjaan.index')
            ->with('success', 'Pekerjaan berhasil dihapus.');
    }
}
