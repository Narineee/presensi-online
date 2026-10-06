<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Pekerjaan;
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
        return redirect()->route('aktivitas.create');
    }

    /**
     * Menampilkan formulir tambah aktivitas harian baru.
     */
    public function create()
    {
        $user = Auth::user();
        $magang = $user->magang;

        $pekerjaanList = $magang
            ? $magang->pekerjaan()->where('status', 'aktif')->orderBy('jenis', 'asc')->orderBy('judul', 'asc')->get()
            : collect();

        return view('aktivitas.create', compact('pekerjaanList'));
    }

    /**
     * Menyimpan data aktivitas harian baru ke database.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $magang = $user->magang;

        $allowedPekerjaanIds = $magang
            ? $magang->pekerjaan()->where('status', 'aktif')->pluck('id')->toArray()
            : [];

        // Validasi input data
        $request->validate([
            'pekerjaan_id' => ['required', 'in:'.implode(',', $allowedPekerjaanIds)],
            'judul' => 'nullable|string|max:255',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'isi' => 'required|string|min:5',
        ], [
            'pekerjaan_id.required' => 'Pekerjaan magang wajib dipilih dari daftar tugas aktif.',
            'pekerjaan_id.in' => 'Pekerjaan yang dipilih tidak valid atau bukan tugas aktif yang diberikan pembimbing kepada Anda.',
            'tanggal.required' => 'Tanggal aktivitas wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi.',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi.',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
            'isi.required' => 'Uraian aktivitas pekerjaan wajib diisi.',
            'isi.min' => 'Uraian aktivitas minimal 5 karakter.',
        ]);

        $pekerjaan = Pekerjaan::find($request->pekerjaan_id);
        $progress = ($pekerjaan && $pekerjaan->isProyek()) ? ($pekerjaan->progress ?? 0) : 0;
        $judul = $request->filled('judul') ? $request->judul : ($pekerjaan ? $pekerjaan->judul : null);

        Aktivitas::create([
            'pengguna_id' => $user->id,
            'pekerjaan_id' => $request->pekerjaan_id,
            'judul' => $judul,
            'tanggal' => $request->tanggal,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'isi' => $request->isi,
            'progress' => $progress,
            'status' => 'pending',
        ]);

        if ($request->redirect_to === 'presensi') {
            return redirect()->route('presensi.index')->with('success', 'Aktivitas harian berhasil dicatat! Sekarang Anda dapat melakukan presensi pulang.');
        }
        return redirect()->route('presensi.index')->with('success', 'Aktivitas harian berhasil dicatat dan menunggu validasi pembimbing.');
    }

    /**
     * Menampilkan detail aktivitas dan catatan validasi dari pembimbing.
     */
    public function show($id)
    {
        $aktivitas = Aktivitas::where('pengguna_id', Auth::id())
            ->with(['pekerjaan', 'validator.pembimbing'])
            ->findOrFail($id);

        return view('aktivitas.show', compact('aktivitas'));
    }

    /**
     * Menampilkan formulir edit aktivitas harian.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $aktivitas = Aktivitas::where('pengguna_id', $user->id)
            ->with('pekerjaan')
            ->findOrFail($id);

        // Jika aktivitas sudah disetujui, tolak pengeditan
        if (! $aktivitas->canBeEdited()) {
            return redirect()->route('magang.rekap')
                ->with('error', 'Aktivitas yang telah disetujui oleh pembimbing tidak dapat diubah lagi.');
        }

        $pekerjaanList = $user->magang
            ? $user->magang->pekerjaan()
                ->where(function ($q) use ($aktivitas) {
                    $q->where('status', 'aktif');
                    if ($aktivitas->pekerjaan_id) {
                        $q->orWhere('id', $aktivitas->pekerjaan_id);
                    }
                })
                ->orderBy('judul', 'asc')
                ->get()
            : collect();

        return view('aktivitas.edit', compact('aktivitas', 'pekerjaanList'));
    }

    /**
     * Memperbarui data aktivitas harian.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $aktivitas = Aktivitas::where('pengguna_id', $user->id)
            ->findOrFail($id);

        if (! $aktivitas->canBeEdited()) {
            return redirect()->route('magang.rekap')
                ->with('error', 'Aktivitas yang telah disetujui oleh pembimbing tidak dapat diubah lagi.');
        }

        $allowedPekerjaanIds = $user->magang
            ? $user->magang->pekerjaan()->pluck('id')->toArray()
            : [];

        $request->validate([
            'pekerjaan_id' => ['required', 'in:'.implode(',', $allowedPekerjaanIds)],
            'judul' => 'nullable|string|max:255',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'nullable|date_format:H:i',
            'waktu_selesai' => 'nullable|date_format:H:i|after:waktu_mulai',
            'isi' => 'required|string|min:5',
        ], [
            'pekerjaan_id.required' => 'Pekerjaan magang wajib dipilih dari daftar tugas.',
            'pekerjaan_id.in' => 'Pekerjaan yang dipilih tidak valid atau bukan tugas Anda.',
            'tanggal.required' => 'Tanggal aktivitas wajib diisi.',
            'isi.required' => 'Uraian aktivitas pekerjaan wajib diisi.',
            'isi.min' => 'Uraian aktivitas minimal 5 karakter.',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        // Jika status sebelumnya adalah revisi, kembalikan ke pending agar divalidasi ulang
        $statusBaru = ($aktivitas->status === 'revisi') ? 'pending' : $aktivitas->status;

        $pekerjaan = Pekerjaan::find($request->pekerjaan_id);
        $progress = ($pekerjaan && $pekerjaan->isProyek()) ? ($pekerjaan->progress ?? $aktivitas->progress) : 0;
        $judul = $request->filled('judul') ? $request->judul : ($pekerjaan ? $pekerjaan->judul : $aktivitas->judul);

        $aktivitas->update([
            'pekerjaan_id' => $request->pekerjaan_id,
            'judul' => $judul,
            'tanggal' => $request->tanggal,
            'waktu_mulai' => $request->waktu_mulai,        
            'waktu_selesai' => $request->waktu_selesai,    
            'isi' => $request->isi,
            'progress' => $progress,
            'status' => $statusBaru,
        ]);

        return redirect()->route('magang.rekap')
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
            return redirect()->route('magang.rekap')
                ->with('error', 'Aktivitas yang telah disetujui tidak dapat dihapus.');
        }

        $aktivitas->delete();

        return redirect()->route('magang.rekap')
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
            ->with(['pekerjaan', 'validator.pembimbing']);

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

        $targetDate = $tanggalMulai ?? ($bulan ? Carbon::parse($bulan.'-01')->toDateString() : Carbon::today()->toDateString());
        $divisi = $user->magang?->getDivisiAt($targetDate) ?? $user->magang?->divisi ?? Divisi::first();
        $pembimbing = $user->magang?->pembimbing ?? Pembimbing::first();
        $pengaturan = Pengaturan::getPengaturan();

        return view('aktivitas.cetak', compact('user', 'aktivitas', 'stats', 'periodeText', 'bulan', 'divisi', 'pembimbing', 'pengaturan'));
    }
}
