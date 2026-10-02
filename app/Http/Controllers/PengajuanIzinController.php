<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengajuanIzinController extends Controller
{
    /**
     * Menampilkan riwayat permohonan izin/sakit milik pengguna yang login.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PengajuanIzin::where('pengguna_id', $user->id);

        // Filter berdasarkan jenis izin (sakit, izin, cuti)
        if ($request->filled('jenis_izin')) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        // Filter status persetujuan (pending, disetujui, ditolak)
        if ($request->filled('status_approval')) {
            $query->where('status_approval', $request->status_approval);
        }

        $pengajuanIzin = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Ringkasan metrik status permohonan
        $stats = [
            'total' => PengajuanIzin::where('pengguna_id', $user->id)->count(),
            'disetujui' => PengajuanIzin::where('pengguna_id', $user->id)->where('status_approval', 'disetujui')->count(),
            'pending' => PengajuanIzin::where('pengguna_id', $user->id)->where('status_approval', 'pending')->count(),
            'ditolak' => PengajuanIzin::where('pengguna_id', $user->id)->where('status_approval', 'ditolak')->count(),
        ];

        return view('izin.index', compact('pengajuanIzin', 'stats'));
    }

    /**
     * Menampilkan formulir pengajuan izin / sakit baru.
     */
    public function create()
    {
        return view('izin.create');
    }

    /**
     * Menyimpan permohonan izin / sakit ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis_izin' => 'required|in:sakit,izin,cuti',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|min:5',
            'bukti_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'jenis_izin.required' => 'Pilih jenis permohonan (Sakit, Izin, atau Cuti).',
            'tanggal_mulai.required' => 'Tanggal mulai izin wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai izin wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'alasan.required' => 'Alasan permohonan izin wajib diisi.',
            'alasan.min' => 'Alasan permohonan minimal 5 karakter.',
            'bukti_file.required' => 'Bukti lampiran wajib diunggah.',
            'bukti_file.mimes' => 'File bukti harus berformat JPG, PNG, atau PDF.',
            'bukti_file.max' => 'Ukuran file bukti maksimal 2MB.',
        ]);

        // Upload berkas bukti (misal: surat dokter)
        $buktiPath = null;
        if ($request->hasFile('bukti_file')) {
            $buktiPath = $request->file('bukti_file')->store('bukti-izin', 'public');
        }

        PengajuanIzin::create([
            'pengguna_id' => Auth::id(),
            'jenis_izin' => $request->jenis_izin,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'bukti_file' => $buktiPath,
            'status_approval' => 'pending',
        ]);

        return redirect()->route('izin.index')
            ->with('success', 'Permohonan izin/sakit berhasil diajukan dan menunggu persetujuan pembimbing.');
    }

    /**
     * Menampilkan detail permohonan izin beserta dokumen bukti.
     */
    public function show($id)
    {
        $izin = PengajuanIzin::where('pengguna_id', Auth::id())
            ->findOrFail($id);

        return view('izin.show', compact('izin'));
    }

    /**
     * Menampilkan formulir edit permohonan izin (hanya jika masih pending).
     */
    public function edit($id)
    {
        $izin = PengajuanIzin::where('pengguna_id', Auth::id())
            ->findOrFail($id);

        if (! $izin->canBeEdited()) {
            return redirect()->route('izin.index')
                ->with('error', 'Permohonan yang telah diverifikasi tidak dapat diubah lagi.');
        }

        return view('izin.edit', compact('izin'));
    }

    /**
     * Memperbarui data permohonan izin.
     */
    public function update(Request $request, $id)
    {
        $izin = PengajuanIzin::where('pengguna_id', Auth::id())
            ->findOrFail($id);

        if (! $izin->canBeEdited()) {
            return redirect()->route('izin.index')
                ->with('error', 'Permohonan yang telah diverifikasi tidak dapat diubah lagi.');
        }

        $request->validate([
            'jenis_izin' => 'required|in:sakit,izin,cuti',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|min:5',
            'bukti_file' => [
                $izin->bukti_file ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048',
            ],
        ], [
            'jenis_izin.required' => 'Pilih jenis permohonan (Sakit, Izin, atau Cuti).',
            'tanggal_mulai.required' => 'Tanggal mulai izin wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai izin wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'alasan.required' => 'Alasan permohonan izin wajib diisi.',
            'alasan.min' => 'Alasan permohonan minimal 5 karakter.',
            'bukti_file.required' => 'Bukti lampiran wajib diunggah.',
            'bukti_file.mimes' => 'File bukti harus berformat JPG, PNG, atau PDF.',
            'bukti_file.max' => 'Ukuran file bukti maksimal 2MB.',
        ]);

        $buktiPath = $izin->bukti_file;
        if ($request->hasFile('bukti_file')) {
            // Hapus file lama jika ada
            if ($izin->bukti_file) {
                Storage::disk('public')->delete($izin->bukti_file);
            }
            $buktiPath = $request->file('bukti_file')->store('bukti-izin', 'public');
        }

        $izin->update([
            'jenis_izin' => $request->jenis_izin,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'bukti_file' => $buktiPath,
        ]);

        return redirect()->route('izin.index')
            ->with('success', 'Permohonan izin/sakit berhasil diperbarui!');
    }

    /**
     * Membatalkan / menghapus permohonan izin.
     */
    public function destroy($id)
    {
        $izin = PengajuanIzin::where('pengguna_id', Auth::id())
            ->findOrFail($id);

        if (! $izin->canBeEdited()) {
            return redirect()->route('izin.index')
                ->with('error', 'Permohonan yang telah diverifikasi tidak dapat dibatalkan.');
        }

        // Hapus file lampiran jika ada
        if ($izin->bukti_file) {
            Storage::disk('public')->delete($izin->bukti_file);
        }

        $izin->delete();

        return redirect()->route('izin.index')
            ->with('success', 'Permohonan izin berhasil dibatalkan.');
    }
}
