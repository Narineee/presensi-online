<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AktivitasController extends Controller
{
    /**
     * Menampilkan daftar aktivitas harian milik pengguna yang sedang login (Magang).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Aktivitas::where('pengguna_id', $user->id);

        // Filter berdasarkan status (pending, approve, revisi)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        // Filter pencarian isi aktivitas
        if ($request->filled('search')) {
            $query->where('isi', 'like', '%'.$request->search.'%');
        }

        $aktivitas = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Ringkasan statistik aktivitas milik user
        $stats = [
            'total' => Aktivitas::where('pengguna_id', $user->id)->count(),
            'approve' => Aktivitas::where('pengguna_id', $user->id)->where('status', 'approve')->count(),
            'pending' => Aktivitas::where('pengguna_id', $user->id)->where('status', 'pending')->count(),
            'revisi' => Aktivitas::where('pengguna_id', $user->id)->where('status', 'revisi')->count(),
        ];

        return view('aktivitas.index', compact('aktivitas', 'stats'));
    }

    /**
     * Menampilkan formulir tambah aktivitas harian baru.
     */
    public function create()
    {
        return view('aktivitas.create');
    }

    /**
     * Menyimpan data aktivitas harian baru ke database.
     */
    public function store(Request $request)
    {

        // Validasi input data
        $request->validate([
            'tanggal' => 'required|date',
            'isi' => 'required|string|min:5',
            'progress' => 'required|integer|min:0|max:100',
        ], [
            'tanggal.required' => 'Tanggal aktivitas wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'isi.required' => 'Uraian aktivitas pekerjaan wajib diisi.',
            'isi.min' => 'Uraian aktivitas minimal 5 karakter.',
            'progress.required' => 'Persentase progres wajib diisi.',
            'progress.integer' => 'Progres harus berupa angka bulat antara 0 - 100.',
            'progress.min' => 'Progres minimal 0%.',
            'progress.max' => 'Progres maksimal 100%.',
        ]);

        Aktivitas::create([
            'pengguna_id' => Auth::id(),
            'tanggal' => $request->tanggal,
            'isi' => $request->isi,
            'progress' => $request->progress,
            'status' => 'pending',
        ]);

        if ($request->filled('redirect_to') && $request->redirect_to === 'presensi') {
            return redirect()->route('presensi.index')
                ->with('success', 'Aktivitas harian berhasil dicatat! Sekarang Anda dapat melakukan presensi pulang.');
        }

        return redirect()->route('aktivitas.index')
            ->with('success', 'Aktivitas harian berhasil dicatat dan menunggu validasi pembimbing.');
    }

    /**
     * Menampilkan detail aktivitas dan catatan validasi dari pembimbing.
     */
    public function show($id)
    {
        $aktivitas = Aktivitas::where('pengguna_id', Auth::id())
            ->findOrFail($id);

        return view('aktivitas.show', compact('aktivitas'));
    }

    /**
     * Menampilkan formulir edit aktivitas harian.
     */
    public function edit($id)
    {
        $aktivitas = Aktivitas::where('pengguna_id', Auth::id())
            ->findOrFail($id);

        // Jika aktivitas sudah disetujui, tolak pengeditan
        if (! $aktivitas->canBeEdited()) {
            return redirect()->route('aktivitas.index')
                ->with('error', 'Aktivitas yang telah disetujui oleh pembimbing tidak dapat diubah lagi.');
        }

        return view('aktivitas.edit', compact('aktivitas'));
    }

    /**
     * Memperbarui data aktivitas harian.
     */
    public function update(Request $request, $id)
    {
        $aktivitas = Aktivitas::where('pengguna_id', Auth::id())
            ->findOrFail($id);

        if (! $aktivitas->canBeEdited()) {
            return redirect()->route('aktivitas.index')
                ->with('error', 'Aktivitas yang telah disetujui oleh pembimbing tidak dapat diubah lagi.');
        }

        $request->validate([
            'tanggal' => 'required|date',
            'isi' => 'required|string|min:5',
            'progress' => 'required|integer|min:0|max:100',
        ], [
            'tanggal.required' => 'Tanggal aktivitas wajib diisi.',
            'isi.required' => 'Uraian aktivitas pekerjaan wajib diisi.',
            'isi.min' => 'Uraian aktivitas minimal 5 karakter.',
            'progress.required' => 'Persentase progres wajib diisi.',
            'progress.integer' => 'Progres harus berupa angka bulat 0 - 100.',
        ]);

        // Jika status sebelumnya adalah revisi, kembalikan ke pending agar divalidasi ulang
        $statusBaru = ($aktivitas->status === 'revisi') ? 'pending' : $aktivitas->status;

        $aktivitas->update([
            'tanggal' => $request->tanggal,
            'isi' => $request->isi,
            'progress' => $request->progress,
            'status' => $statusBaru,
        ]);

        return redirect()->route('aktivitas.index')
            ->with('success', 'Aktivitas harian berhasil diperbarui!');
    }

    /**
     * Menghapus catatan aktivitas harian.
     */
    public function destroy($id)
    {
        $aktivitas = Aktivitas::where('pengguna_id', Auth::id())
            ->findOrFail($id);

        if (! $aktivitas->canBeEdited()) {
            return redirect()->route('aktivitas.index')
                ->with('error', 'Aktivitas yang telah disetujui tidak dapat dihapus.');
        }

        $aktivitas->delete();

        return redirect()->route('aktivitas.index')
            ->with('success', 'Catatan aktivitas harian berhasil dihapus.');
    }

    /**
     * Mencetak laporan rekapitulasi aktivitas harian pribadi (Magang).
     */
    public function cetak(Request $request)
    {
        /** @var Pengguna $user */
        $user = Auth::user();

        $user->load(['magang.divisi', 'magang.pembimbing']);

        $query = Aktivitas::where('pengguna_id', $user->id)
            ->with('validator.pembimbing');

        $bulan = $request->input('bulan', Carbon::today()->format('Y-m'));
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');

        if ($tanggalMulai && $tanggalAkhir && strtotime($tanggalMulai) && strtotime($tanggalAkhir)) {
            $query->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);
            $periodeText = Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y').' s/d '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
        } elseif ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $query->whereYear('tanggal', substr($bulan, 0, 4))
                ->whereMonth('tanggal', substr($bulan, 5, 2));
            $periodeText = Carbon::createFromFormat('Y-m', $bulan)->isoFormat('MMMM Y');
        } else {
            $bulan = Carbon::today()->format('Y-m');
            $query->whereYear('tanggal', Carbon::today()->year)
                ->whereMonth('tanggal', Carbon::today()->month);
            $periodeText = Carbon::today()->isoFormat('MMMM Y');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $divisi = $user->magang?->divisi ?? Divisi::first();
        $pembimbing = $user->magang?->pembimbing ?? Pembimbing::first();
        $pengaturan = Pengaturan::getPengaturan();

        return view('aktivitas.cetak', compact('user', 'aktivitas', 'stats', 'periodeText', 'bulan', 'divisi', 'pembimbing', 'pengaturan'));
    }
}
