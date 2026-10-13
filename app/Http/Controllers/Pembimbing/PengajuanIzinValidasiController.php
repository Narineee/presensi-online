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
     * Mendapatkan daftar ID akun anak magang binaan.
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
     * Menampilkan daftar permohonan izin/sakit anak magang binaan untuk diproses.
     */
    public function index(Request $request)
    {
        $supervisedIds = $this->getSupervisedUserIds();

        $query = PengajuanIzin::whereIn('pengguna_id', $supervisedIds)
            ->with(['pengguna.magang.divisi', 'validator.pembimbing']);

        // Filter pencarian nama / no induk peserta
        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $query->whereHas('pengguna.magang', function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', $keyword)
                    ->orWhere('no_induk', 'like', $keyword);
            });
        }

        // Filter rentang tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_selesai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_mulai', '<=', $request->tanggal_akhir);
        }

        // Filter jenis izin
        if ($request->filled('jenis_izin')) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        $status = $request->input('status_approval', $request->input('status'));

        $menunggu = collect();
        if (! $status || $status === 'pending') {
            $menunggu = (clone $query)->where('status_approval', 'pending')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();
        }

        $keputusanQuery = clone $query;
        if ($status === 'disetujui' || $status === 'ditolak') {
            $keputusanQuery->where('status_approval', $status);
        } elseif ($status === 'pending') {
            $keputusanQuery->whereRaw('1 = 0');
        } else {
            $keputusanQuery->whereIn('status_approval', ['disetujui', 'ditolak']);
        }

        $keputusan = $keputusanQuery
            ->orderBy('validated_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pembimbing.izin.index', compact('menunggu', 'keputusan'));
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
            'catatan_penolakan' => 'nullable|string|max:500',
        ], [
            'status_approval.required' => 'Keputusan validasi wajib ditentukan.',
            'catatan_penolakan.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $updateData = [
            'status_approval' => $request->status_approval,
            'validated_by' => Auth::id(),
            'validated_at' => Carbon::now(),
            'catatan_penolakan' => ($request->status_approval === 'ditolak') ? $request->catatan_penolakan : null,
        ];

        $izin->update($updateData);

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
