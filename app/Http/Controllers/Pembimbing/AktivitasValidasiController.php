<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AktivitasValidasiController extends Controller
{
    /**
     * Mendapatkan daftar ID akun pengguna magang yang dibimbing oleh pembimbing yang sedang login.
     */
    private function getSupervisedUserIds()
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return collect();
        }

        return $pembimbing->magang()->pluck('pengguna_id');
    }

    /**
     * Menampilkan daftar aktivitas anak magang yang perlu divalidasi pembimbing.
     */
    public function index(Request $request)
    {
        $supervisedIds = $this->getSupervisedUserIds();

        $query = Aktivitas::whereIn('pengguna_id', $supervisedIds)
            ->with(['pengguna.magang.divisi']);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        $aktivitas = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Statistik validasi pembimbing
        $stats = [
            'pending' => Aktivitas::whereIn('pengguna_id', $supervisedIds)->where('status', 'pending')->count(),
            'approve' => Aktivitas::whereIn('pengguna_id', $supervisedIds)->where('status', 'approve')->count(),
            'revisi' => Aktivitas::whereIn('pengguna_id', $supervisedIds)->where('status', 'revisi')->count(),
            'total' => Aktivitas::whereIn('pengguna_id', $supervisedIds)->count(),
        ];

        return view('pembimbing.aktivitas.index', compact('aktivitas', 'stats'));
    }

    /**
     * Memproses validasi (Setujui / Minta Revisi) terhadap aktivitas yang diajukan.
     */
    public function validasi(Request $request, $id)
    {
        $supervisedIds = $this->getSupervisedUserIds();

        $aktivitas = Aktivitas::whereIn('pengguna_id', $supervisedIds)
            ->findOrFail($id);

        $request->validate([
            'status' => 'required|in:approve,revisi',
            'catatan_validasi' => 'required_if:status,revisi|nullable|string|max:500',
        ], [
            'status.required' => 'Keputusan validasi wajib dipilih.',
            'catatan_validasi.required_if' => 'Catatan revisi wajib diisi jika meminta revisi kepada peserta.',
            'catatan_validasi.max' => 'Catatan maksimal 500 karakter.',
        ]);

        $aktivitas->update([
            'status' => $request->status,
            'catatan_validasi' => $request->catatan_validasi,
            'validated_by' => Auth::id(),
            'validated_at' => Carbon::now(),
        ]);

        $pesan = ($request->status === 'approve')
            ? 'Aktivitas berhasil disetujui (Approved)!'
            : 'Aktivitas dikembalikan ke peserta dengan status Permintaan Revisi.';

        return redirect()->back()->with('success', $pesan);
    }
}
