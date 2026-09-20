<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinValidasiController extends Controller
{
    /**
     * Mendapatkan daftar ID akun peserta binaan (Magang & CS).
     */
    private function getSupervisedUserIds()
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return collect();
        }

        $magangUserIds = $pembimbing->magang()->pluck('pengguna_id');
        $csUserIds = $pembimbing->cs()->pluck('pengguna_id');

        return $magangUserIds->merge($csUserIds);
    }

    /**
     * Menampilkan daftar permohonan izin/sakit peserta binaan untuk diproses.
     */
    public function index(Request $request)
    {
        $supervisedIds = $this->getSupervisedUserIds();

        $query = PengajuanIzin::whereIn('pengguna_id', $supervisedIds)
            ->with(['pengguna.magang.divisi', 'pengguna.cs']);

        // Filter status persetujuan
        if ($request->filled('status_approval')) {
            $query->where('status_approval', $request->status_approval);
        }

        // Filter jenis izin
        if ($request->filled('jenis_izin')) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        $pengajuanIzin = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Statistik
        $stats = [
            'pending' => PengajuanIzin::whereIn('pengguna_id', $supervisedIds)->where('status_approval', 'pending')->count(),
            'disetujui' => PengajuanIzin::whereIn('pengguna_id', $supervisedIds)->where('status_approval', 'disetujui')->count(),
            'ditolak' => PengajuanIzin::whereIn('pengguna_id', $supervisedIds)->where('status_approval', 'ditolak')->count(),
            'total' => PengajuanIzin::whereIn('pengguna_id', $supervisedIds)->count(),
        ];

        return view('pembimbing.izin.index', compact('pengajuanIzin', 'stats'));
    }

    /**
     * Memproses keputusan persetujuan (Setujui / Tolak) permohonan izin/sakit.
     */
    public function validasi(Request $request, $id)
    {
        $supervisedIds = $this->getSupervisedUserIds();

        $izin = PengajuanIzin::whereIn('pengguna_id', $supervisedIds)
            ->findOrFail($id);

        $request->validate([
            'status_approval' => 'required|in:disetujui,ditolak',
        ]);

        $izin->update([
            'status_approval' => $request->status_approval,
            'validated_by' => Auth::id(),
            'validated_at' => Carbon::now(),
        ]);

        // Integrasi Otomatis ke Presensi jika Disetujui:
        // Setiap hari pada rentang tanggal mulai s/d selesai otomatis tercatat di tabel presensi
        if ($request->status_approval === 'disetujui') {
            $current = Carbon::parse($izin->tanggal_mulai);
            $end = Carbon::parse($izin->tanggal_selesai);

            $namaPembimbing = Auth::user()->pembimbing->nama_lengkap ?? Auth::user()->username;

            while ($current->lte($end)) {
                Presensi::updateOrCreate(
                    [
                        'pengguna_id' => $izin->pengguna_id,
                        'tanggal' => $current->toDateString(),
                    ],
                    [
                        'status' => $izin->jenis_izin, // 'sakit', 'izin', atau 'cuti'
                        'mode_kerja' => 'onsite',
                        'keterangan' => 'Permohonan '.ucfirst($izin->jenis_izin).' disetujui oleh '.$namaPembimbing.' ('.$izin->alasan.')',
                    ]
                );
                $current->addDay();
            }
        }

        $pesan = ($request->status_approval === 'disetujui')
            ? 'Permohonan '.ucfirst($izin->jenis_izin).' berhasil disetujui dan otomatis sinkron ke presensi!'
            : 'Permohonan '.ucfirst($izin->jenis_izin).' telah ditolak.';

        return redirect()->back()->with('success', $pesan);
    }
}
