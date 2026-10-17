<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Pengaturan;
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
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        $query = Aktivitas::query()
            ->with(['pengguna.magang.divisi', 'details.rencanaKerja', 'pekerjaan', 'validator.pembimbing']);

        // Batasi aktivitas hanya pada tanggal saat peserta dibina oleh pembimbing ini (sesuai periode unit)
        $pembimbing->applyPeriodConstraint($query, 'aktivitas.tanggal', 'aktivitas.pengguna_id');

        // Filter pencarian nama / no induk
        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $query->whereHas('pengguna.magang', function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', $keyword)
                    ->orWhere('no_induk', 'like', $keyword);
            });
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

        // Filter tanggal tunggal atau rentang tanggal
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        }

        $status = $request->input('status');

        $menunggu = collect();
        if (! $status || $status === 'pending') {
            $menunggu = (clone $query)->where('status', 'pending')
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->get();
        }

        $keputusanQuery = clone $query;
        if ($status === 'approve' || $status === 'revisi') {
            $keputusanQuery->where('status', $status);
        } elseif ($status === 'pending') {
            $keputusanQuery->whereRaw('1 = 0');
        } else {
            $keputusanQuery->whereIn('status', ['approve', 'revisi']);
        }

        $keputusan = $keputusanQuery
            ->orderBy('validated_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $magangList = $pembimbing->getSupervisedMagangQuery()
            ->with('divisi')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('pembimbing.aktivitas.index', compact('menunggu', 'keputusan', 'magangList'));
    }

    /**
     * Memproses validasi (Setujui / Minta Revisi) terhadap aktivitas yang diajukan.
     */
    public function validasi(Request $request, $id)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            abort(403, 'Profil pembimbing tidak ditemukan.');
        }

        $query = Aktivitas::where('id', $id)->with('pekerjaan');
        $pembimbing->applyPeriodConstraint($query, 'aktivitas.tanggal', 'aktivitas.pengguna_id');

        $aktivitas = $query->firstOrFail();

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

    /**
     * Cetak rekapitulasi aktivitas harian peserta magang binaan (dikelompokkan per Rencana Kerja sesuai alur-rekap.md).
     */
    public function cetak(Request $request)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        $magangList = $pembimbing->getSupervisedMagangQuery()
            ->with(['divisi', 'pengguna'])
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $query = Aktivitas::query()
            ->with(['pengguna.magang.divisi', 'details.rencanaKerja', 'pekerjaan', 'validator.pembimbing']);

        // Terapkan batas periode binaan
        $pembimbing->applyPeriodConstraint($query, 'aktivitas.tanggal', 'aktivitas.pengguna_id');

        $selectedMagang = null;
        if ($request->filled('magang_id')) {
            $selectedMagang = $magangList->firstWhere('id', (int) $request->magang_id);
            if ($selectedMagang && $selectedMagang->pengguna_id) {
                $query->where('pengguna_id', $selectedMagang->pengguna_id);
            }
        }

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $singleTanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');

        if ($tanggalMulai && $tanggalAkhir && strtotime($tanggalMulai) && strtotime($tanggalAkhir)) {
            $query->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);
            $periodeText = Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y').' s/d '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
        } elseif ($tanggalMulai && strtotime($tanggalMulai)) {
            $query->where('tanggal', '>=', $tanggalMulai);
            $periodeText = 'Mulai '.Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y');
        } elseif ($tanggalAkhir && strtotime($tanggalAkhir)) {
            $query->where('tanggal', '<=', $tanggalAkhir);
            $periodeText = 'Sampai '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
        } elseif ($singleTanggal && strtotime($singleTanggal)) {
            $query->whereDate('tanggal', $singleTanggal);
            $periodeText = Carbon::parse($singleTanggal)->isoFormat('D MMMM Y');
        } elseif ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $query->whereYear('tanggal', substr($bulan, 0, 4))->whereMonth('tanggal', substr($bulan, 5, 2));
            $periodeText = Carbon::createFromFormat('Y-m-d', $bulan.'-01')->isoFormat('MMMM Y');
        } else {
            $query->whereYear('tanggal', Carbon::today()->year)->whereMonth('tanggal', Carbon::today()->month);
            $periodeText = Carbon::today()->isoFormat('MMMM Y');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $aktivitas = $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc')->get();

        $stats = [
            'total' => $aktivitas->count(),
            'approve' => $aktivitas->where('status', 'approve')->count(),
            'pending' => $aktivitas->where('status', 'pending')->count(),
            'revisi' => $aktivitas->where('status', 'revisi')->count(),
        ];

        // Kelompokkan per Rencana Kerja sesuai alur-rekap.md
        $groupedByRencana = Aktivitas::groupByRencana($aktivitas);

        $groupedByPeserta = [];
        if (! $selectedMagang) {
            $byUser = $aktivitas->groupBy('pengguna_id');
            foreach ($byUser as $userId => $userAktivitas) {
                $userObj = $userAktivitas->first()->pengguna;
                $magangObj = $userObj?->magang;
                $groupedByPeserta[] = [
                    'magang' => $magangObj,
                    'user' => $userObj,
                    'nama_lengkap' => $magangObj->nama_lengkap ?? ($userObj->username ?? 'Peserta'),
                    'no_induk' => $magangObj->no_induk ?? '-',
                    'divisi' => $magangObj->divisi->nama_divisi ?? '-',
                    'instansi' => $magangObj->instansi_pendidikan ?? '-',
                    'grouped_rencana' => Aktivitas::groupByRencana($userAktivitas),
                    'total' => $userAktivitas->count(),
                ];
            }
        }

        $divisi = $selectedMagang?->divisi ?? $magangList->first()?->divisi ?? Divisi::first();
        $pengaturan = Pengaturan::getPengaturan();

        return view('pembimbing.aktivitas.cetak', compact(
            'pembimbing',
            'magangList',
            'selectedMagang',
            'aktivitas',
            'groupedByRencana',
            'groupedByPeserta',
            'stats',
            'periodeText',
            'divisi',
            'pengaturan'
        ));
    }
}
