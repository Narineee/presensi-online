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
            ->with(['pengguna.magang.divisi', 'pekerjaan']);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pekerjaan
        if ($request->filled('pekerjaan_id')) {
            $query->where('pekerjaan_id', $request->pekerjaan_id);
        }

        // Filter peserta magang tertentu
        if ($request->filled('magang_id')) {
            $magangId = $request->magang_id;
            $query->whereHas('pengguna.magang', function ($q) use ($magangId) {
                $q->where('id', $magangId);
            });
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
            ->with('pekerjaan')
            ->findOrFail($id);

        $rules = [
            'status' => 'required|in:approve,revisi',
            'catatan_validasi' => 'required_if:status,revisi|nullable|string|max:500',
        ];

        // Jika pekerjaan bertipe proyek dan disetujui, pembimbing wajib/dapat menentukan progres baru
        $isProyek = $aktivitas->pekerjaan && $aktivitas->pekerjaan->isProyek();
        if ($isProyek && $request->status === 'approve') {
            $rules['progress'] = 'required|integer|min:0|max:100';
        }

        $request->validate($rules, [
            'status.required' => 'Keputusan validasi wajib dipilih.',
            'catatan_validasi.required_if' => 'Catatan revisi wajib diisi jika meminta revisi kepada peserta.',
            'catatan_validasi.max' => 'Catatan maksimal 500 karakter.',
            'progress.required' => 'Progres capaian pekerjaan proyek wajib ditentukan.',
            'progress.integer' => 'Progres harus berupa angka bulat 0 - 100.',
            'progress.min' => 'Progres minimal 0%.',
            'progress.max' => 'Progres maksimal 100%.',
        ]);

        $updateData = [
            'status' => $request->status,
            'catatan_validasi' => $request->catatan_validasi,
            'validated_by' => Auth::id(),
            'validated_at' => Carbon::now(),
        ];

        // Jika disetujui dan merupakan pekerjaan proyek, perbarui progress pekerjaan
        if ($request->status === 'approve') {
            if ($isProyek) {
                $newProgress = (int) $request->progress;
                $updateData['progress'] = $newProgress;

                $pekerjaan = $aktivitas->pekerjaan;
                $pekerjaanUpdate = ['progress' => $newProgress];
                if ($newProgress >= 100) {
                    $pekerjaanUpdate['status'] = 'selesai';
                }
                $pekerjaan->update($pekerjaanUpdate);
            }
        }

        $aktivitas->update($updateData);

        $pesan = ($request->status === 'approve')
            ? 'Aktivitas berhasil disetujui (Approved)!'
            : 'Aktivitas dikembalikan ke peserta dengan status Permintaan Revisi.';

        return redirect()->back()->with('success', $pesan);
    }
}
