<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\PengajuanTugasLuar;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasLuarValidasiController extends Controller
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
     * Menampilkan daftar permohonan Tugas Luar anak magang binaan untuk diproses.
     */
    public function index(Request $request)
    {
        $supervisedIds = $this->getSupervisedUserIds();

        $query = PengajuanTugasLuar::whereIn('pengguna_id', $supervisedIds)
            ->with(['pengguna.magang.divisi', 'presensi', 'validator.pembimbing']);

        // Filter pencarian nama / no induk / keperluan / tujuan
        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('tujuan', 'like', $keyword)
                    ->orWhere('keperluan', 'like', $keyword)
                    ->orWhereHas('pengguna.magang', function ($m) use ($keyword) {
                        $m->where('nama_lengkap', 'like', $keyword)
                            ->orWhere('no_induk', 'like', $keyword);
                    });
            });
        }

        // Filter tanggal tunggal atau rentang tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        $status = $request->input('status_verifikasi', $request->input('status'));

        $menunggu = collect();
        if (! $status || $status === 'menunggu') {
            $menunggu = (clone $query)->where('status_verifikasi', 'menunggu')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();
        }

        $keputusanQuery = clone $query;
        if ($status === 'disetujui' || $status === 'ditolak') {
            $keputusanQuery->where('status_verifikasi', $status);
        } elseif ($status === 'menunggu') {
            $keputusanQuery->whereRaw('1 = 0');
        } else {
            $keputusanQuery->whereIn('status_verifikasi', ['disetujui', 'ditolak']);
        }

        $keputusan = $keputusanQuery
            ->orderBy('verified_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pembimbing.tugas-luar.index', compact('menunggu', 'keputusan'));
    }

    /**
     * Memproses keputusan verifikasi (Setujui / Tolak) permohonan Tugas Luar.
     */
    public function validasi(Request $request, $id)
    {
        $supervisedIds = $this->getSupervisedUserIds();

        $tugasLuar = PengajuanTugasLuar::whereIn('pengguna_id', $supervisedIds)
            ->findOrFail($id);

        $request->validate([
            'status_verifikasi' => 'required|in:disetujui,ditolak',
            'catatan_pembimbing' => 'nullable|string|max:500',
        ], [
            'status_verifikasi.required' => 'Pilih keputusan verifikasi (Setujui atau Tolak).',
            'status_verifikasi.in' => 'Status verifikasi tidak valid.',
            'catatan_pembimbing.max' => 'Catatan maksimal 500 karakter.',
        ]);

        $tugasLuar->update([
            'status_verifikasi' => $request->status_verifikasi,
            'catatan_pembimbing' => $request->catatan_pembimbing,
            'verified_by' => Auth::id(),
            'verified_at' => Carbon::now(),
        ]);

        $namaPembimbing = Auth::user()->pembimbing->nama_lengkap ?? Auth::user()->username;

        // Jika disetujui, sinkronisasi otomatis ke presensi hari tersebut
        if ($request->status_verifikasi === 'disetujui') {
            $presensi = Presensi::where('pengguna_id', $tugasLuar->pengguna_id)
                ->whereDate('tanggal', $tugasLuar->tanggal)
                ->first();

            $infoTugasLuar = 'Tugas Luar ('.$tugasLuar->tujuan.') disetujui oleh '.$namaPembimbing;
            $waktuMasukTL = $tugasLuar->waktu_mulai ?: ($tugasLuar->created_at ? $tugasLuar->created_at->format('H:i:s') : '08:00:00');
            $waktuPulangTL = $tugasLuar->waktu_selesai ?: '16:00:00';

            if ($presensi) {
                // Skenario 1 atau Skenario 2: Presensi sudah ada
                // Jam masuk dan mode awal (misal Onsite) tetap dipertahankan
                $keteranganUpdate = $presensi->keterangan;
                if (! str_contains($keteranganUpdate ?? '', 'disetujui')) {
                    $keteranganUpdate = $keteranganUpdate
                        ? rtrim($keteranganUpdate).' | '.$infoTugasLuar
                        : $infoTugasLuar;
                }

                $jamKeluarSync = $presensi->jam_keluar;
                if (! $jamKeluarSync) {
                    $jamKeluarSync = ($presensi->jam_masuk && $presensi->jam_masuk !== $waktuMasukTL)
                        ? $waktuMasukTL
                        : $waktuPulangTL;
                }

                $presensi->update([
                    'status' => 'hadir',
                    'jam_masuk' => $presensi->jam_masuk ?: $waktuMasukTL,
                    'jam_keluar' => $jamKeluarSync,
                    'keterangan' => $keteranganUpdate,
                ]);
            } else {
                // Jika belum ada record presensi (misal dibuat langsung oleh pembimbing)
                $presensi = Presensi::create([
                    'pengguna_id' => $tugasLuar->pengguna_id,
                    'tanggal' => $tugasLuar->tanggal,
                    'jam_masuk' => $waktuMasukTL,
                    'jam_keluar' => $waktuPulangTL,
                    'status' => 'hadir',
                    'mode_kerja' => 'tugas_luar',
                    'keterangan' => $infoTugasLuar,
                ]);
            }

            // Pastikan relasi presensi_id tersambung
            $tugasLuar->update([
                'presensi_id' => $presensi->id,
            ]);
        } else {
            // Jika ditolak
            if ($tugasLuar->presensi && $tugasLuar->presensi->mode_kerja === 'tugas_luar') {
                $catatan = $request->catatan_pembimbing ? ' ('.$request->catatan_pembimbing.')' : '';
                $tugasLuar->presensi->update([
                    'keterangan' => 'Tugas Luar ditolak oleh '.$namaPembimbing.$catatan,
                ]);
            }
        }

        $pesan = ($request->status_verifikasi === 'disetujui')
            ? 'Pengajuan Tugas Luar berhasil disetujui dan otomatis sinkron ke presensi!'
            : 'Pengajuan Tugas Luar telah ditolak.';

        return redirect()->back()->with('success', $pesan);
    }
}
