<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Magang;
use App\Models\Pekerjaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PekerjaanController extends Controller
{
    private function getPembimbing()
    {
        return Auth::user()->pembimbing;
    }

    /**
     * Daftar pekerjaan yang dibuat pembimbing, lengkap dengan uraian aktivitas terbaru.
     */
    public function index(Request $request): View
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $query = Pekerjaan::where('pembimbing_id', $pembimbing->id)->with('magang');

        if ($request->filled('magang_id')) {
            $query->where('magang_id', $request->magang_id);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Cari peserta (nama / nomor induk) atau judul pekerjaan
        $keyword = $request->input('q', $request->input('search'));
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                    ->orWhereHas('magang', function ($m) use ($keyword) {
                        $m->where('nama_lengkap', 'like', "%{$keyword}%")
                            ->orWhere('no_induk', 'like', "%{$keyword}%");
                    });
            });
        }

        // Rentang tanggal: pekerjaan yang periodenya beririsan dengan rentang yang dipilih
        if ($request->filled('tanggal_mulai')) {
            $mulai = $request->tanggal_mulai;
            $query->where(function ($q) use ($mulai) {
                $q->whereNull('target_selesai')->orWhereDate('target_selesai', '>=', $mulai);
            });
        }
        if ($request->filled('tanggal_akhir')) {
            $akhir = $request->tanggal_akhir;
            $query->where(function ($q) use ($akhir) {
                $q->whereNull('tanggal_mulai')->orWhereDate('tanggal_mulai', '<=', $akhir);
            });
        }

        $pekerjaanList = $query->orderBy('status', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Aktivitas terbaru per pekerjaan (satu query untuk seluruh halaman)
        $terbaru = Aktivitas::whereIn('pekerjaan_id', $pekerjaanList->pluck('id'))
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('pekerjaan_id')
            ->map(fn ($g) => $g->first());

        return view('pembimbing.pekerjaan.index', compact('pekerjaanList', 'terbaru'));
    }

    /**
     * Form tambah pekerjaan.
     */
    public function create(Request $request): View
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $supervisedMagang = Magang::where('pembimbing_id', $pembimbing->id)
            ->with('divisi')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $selectedMagangId = $request->input('magang_id');

        return view('pembimbing.pekerjaan.create', compact('supervisedMagang', 'selectedMagangId'));
    }

    /**
     * Simpan pekerjaan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $allowedMagangIds = Magang::where('pembimbing_id', $pembimbing->id)->pluck('id')->toArray();

        $request->validate([
            'magang_id' => ['required', 'in:'.implode(',', $allowedMagangIds)],
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'jenis' => 'required|in:proyek,rutin',
            'tanggal_mulai' => 'nullable|date',
            'target_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'progress' => 'nullable|integer|min:0|max:100',
        ], [
            'magang_id.required' => 'Pilih peserta magang penerima pekerjaan ini.',
            'magang_id.in' => 'Peserta magang yang dipilih bukan merupakan anak binaan Anda.',
            'judul.required' => 'Judul pekerjaan wajib diisi.',
            'deskripsi.required' => 'Deskripsi / petunjuk tugas wajib diisi.',
            'jenis.required' => 'Jenis pekerjaan (Proyek / Rutin) wajib ditentukan.',
            'target_selesai.after_or_equal' => 'Tanggal selesai tidak boleh mendahului tanggal mulai.',
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
     * Detail pekerjaan beserta riwayat aktivitas terkait.
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
     * Form edit pekerjaan.
     */
    public function edit($id): View
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $pekerjaan = Pekerjaan::where('pembimbing_id', $pembimbing->id)
            ->withCount('aktivitas')
            ->findOrFail($id);

        $supervisedMagang = Magang::where('pembimbing_id', $pembimbing->id)
            ->with('divisi')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('pembimbing.pekerjaan.edit', compact('pekerjaan', 'supervisedMagang'));
    }

    /**
     * Perbarui pekerjaan.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $pembimbing = $this->getPembimbing();
        abort_if(! $pembimbing, 403, 'Akses khusus pembimbing lapangan.');

        $pekerjaan = Pekerjaan::where('pembimbing_id', $pembimbing->id)->findOrFail($id);

        $allowedMagangIds = Magang::where('pembimbing_id', $pembimbing->id)->pluck('id')->toArray();

        $request->validate([
            'magang_id' => ['required', 'in:'.implode(',', $allowedMagangIds)],
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'jenis' => 'required|in:proyek,rutin',
            'status' => 'required|in:aktif,selesai,nonaktif',
            'tanggal_mulai' => 'nullable|date',
            'target_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'progress' => 'nullable|integer|min:0|max:100',
        ], [
            'magang_id.required' => 'Pilih peserta magang penerima pekerjaan ini.',
            'magang_id.in' => 'Peserta magang yang dipilih bukan merupakan anak binaan Anda.',
            'judul.required' => 'Judul pekerjaan wajib diisi.',
            'deskripsi.required' => 'Deskripsi / petunjuk tugas wajib diisi.',
            'target_selesai.after_or_equal' => 'Tanggal selesai tidak boleh mendahului tanggal mulai.',
        ]);

        $isProyek = ($request->jenis === 'proyek');
        $progress = $isProyek ? (int) ($request->progress ?? $pekerjaan->progress ?? 0) : null;
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
     * Hapus pekerjaan bila belum memiliki aktivitas harian.
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
