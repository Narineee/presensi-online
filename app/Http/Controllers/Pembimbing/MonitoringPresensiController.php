<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\Pengaturan;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MonitoringPresensiController extends Controller
{
    private function getPembimbing(): ?Pembimbing
    {
        return Auth::user()->pembimbing;
    }

    /**
     * Peserta magang yang dibimbing oleh pembimbing yang login.
     */
    private function getSupervisedMagangList(): Collection
    {
        $pembimbing = $this->getPembimbing();
        if (! $pembimbing) {
            return collect();
        }

        return Magang::where('pembimbing_id', $pembimbing->id)
            ->with(['divisi', 'pengguna'])
            ->orderBy('nama_lengkap', 'asc')
            ->get();
    }

    /**
     * Filter yang dipakai bersama oleh daftar riwayat dan cetak: peserta, pencarian, mode kerja, status.
     * Mengembalikan peserta terpilih (bila filter magang_id dipakai).
     */
    private function applyFilters($query, Request $request, Collection $magangList): ?Magang
    {
        $selectedMagang = null;

        if ($request->filled('magang_id')) {
            $selectedMagang = $magangList->firstWhere('id', (int) $request->magang_id);
            if ($selectedMagang && $selectedMagang->pengguna_id) {
                $query->where('pengguna_id', $selectedMagang->pengguna_id);
            }
        }

        // Cari peserta (nama atau nomor induk)
        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $query->whereHas('pengguna.magang', function ($m) use ($keyword) {
                $m->where(function ($x) use ($keyword) {
                    $x->where('nama_lengkap', 'like', $keyword)
                        ->orWhere('no_induk', 'like', $keyword);
                });
            });
        }

        if ($request->filled('mode_kerja')) {
            $query->where('mode_kerja', $request->mode_kerja);
        }

        // Status: hadir / sakit / izin / cuti / alpa, atau "tl" (Tugas Luar)
        $status = $request->input('status');
        if ($status === 'tl') {
            $query->where(function ($q) {
                $q->where('mode_kerja', 'tugas_luar')
                    ->orWhereHas('pengajuanTugasLuar', fn ($t) => $t->where('status_verifikasi', 'disetujui'));
            });
        } elseif ($status) {
            $query->where('status', $status);
        }

        return $selectedMagang;
    }

    /**
     * Riwayat presensi seluruh peserta binaan ("Catatan terbaru").
     */
    public function index(Request $request): View
    {
        $pembimbing = $this->getPembimbing();
        $magangList = $this->getSupervisedMagangList();
        $supervisedUserIds = $magangList->pluck('pengguna_id')->filter()->values();

        $query = Presensi::whereIn('pengguna_id', $supervisedUserIds)
            ->with(['pengguna.magang', 'pengajuanTugasLuar']);

        // Rentang tanggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_akhir]);
        } elseif ($request->filled('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        } elseif ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        }

        $this->applyFilters($query, $request, $magangList);

        $presensi = $query->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Izin yang disetujui, untuk baris sakit/izin/cuti (alasan, tanggal persetujuan, lampiran)
        $izinPerUser = PengajuanIzin::whereIn('pengguna_id', $presensi->pluck('pengguna_id')->unique())
            ->where('status_approval', 'disetujui')
            ->get()
            ->groupBy('pengguna_id');

        $presensi->setCollection(
            $presensi->getCollection()->map(fn ($p) => $this->susunCatatan($p, $izinPerUser->get($p->pengguna_id, collect())))
        );

        return view('pembimbing.presensi.index', compact('pembimbing', 'presensi'));
    }

    /**
     * Mengubah satu baris presensi menjadi data siap tampil di kartu.
     */
    private function susunCatatan(Presensi $p, Collection $izinUser): array
    {
        $magang = $p->pengguna?->magang;
        $isIzin = in_array($p->status, ['sakit', 'izin', 'cuti']);
        $isTl = ! $isIzin && $p->is_tugas_luar;
        $jam = fn ($t) => $t ? str_replace(':', '.', substr($t, 0, 5)) : '-';

        $terlambat = null;
        if (! $isIzin && $p->jam_masuk && $p->jam_masuk > '08:00:00') {
            $terlambat = (int) ceil((strtotime($p->jam_masuk) - strtotime('08:00:00')) / 60);
        }

        $izin = $isIzin
            ? $izinUser->first(fn ($i) => $p->tanggal->betweenIncluded($i->tanggal_mulai, $i->tanggal_selesai))
            : null;

        // Baris keterangan di bagian bawah kartu
        if ($isIzin) {
            $keterangan = ($izin?->alasan ?: ucfirst($p->status))
                .($izin?->validated_at ? ' - Disetujui '.$izin->validated_at->locale('id')->isoFormat('D MMM') : '');
        } else {
            $lokasi = match ($p->mode_kerja) {
                'wfh' => 'WFH',
                'tugas_luar' => 'Tugas Luar',
                default => $magang?->getDivisiAt($p->tanggal)?->nama_divisi ?? 'Kantor',
            };
            if ($isTl && $p->pengajuanTugasLuar?->tujuan && $p->mode_kerja !== 'tugas_luar') {
                $lokasi .= ' · TL: '.$p->pengajuanTugasLuar->tujuan;
            }
            $keterangan = $lokasi.(is_null($p->face_distance_masuk) ? '' : ' - Lokasi & wajah terverifikasi');
        }

        return [
            'magang' => $magang,
            'nama' => $magang?->nama_lengkap ?? ($p->pengguna->username ?? '-'),
            'tanggal' => $p->tanggal,
            'badge' => $isIzin ? ucfirst($p->status) : ($isTl ? 'TL' : ($p->status === 'alpa' ? 'Alpa' : 'Hadir')),
            'badge_kunci' => $isIzin ? $p->status : ($isTl ? 'tl' : ($p->status === 'alpa' ? 'alpa' : 'hadir')),
            'jam_masuk' => $isIzin ? '-' : $jam($p->jam_masuk),
            'jam_keluar' => $isIzin ? '-' : $jam($p->jam_keluar),
            'terlambat' => $terlambat,
            'foto_masuk_url' => $isIzin ? null : $p->foto_masuk_url,
            'foto_keluar_url' => $isIzin ? null : $p->foto_keluar_url,
            'lampiran_url' => $izin?->bukti_file_url,
            'keterangan' => $keterangan,
        ];
    }

    /**
     * Cetak rekapitulasi riwayat presensi seluruh binaan pada periode yang ditentukan.
     */
    public function cetak(Request $request): View
    {
        $pembimbing = $this->getPembimbing();
        $magangList = $this->getSupervisedMagangList();
        $supervisedUserIds = $magangList->pluck('pengguna_id')->filter()->values();

        $query = Presensi::whereIn('pengguna_id', $supervisedUserIds)
            ->with(['pengguna.magang.divisi', 'pengajuanTugasLuar']);

        $selectedMagang = $this->applyFilters($query, $request, $magangList);

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $singleTanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');

        if ($tanggalMulai && $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);
            $periodeText = Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y').' s/d '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
        } elseif ($tanggalMulai) {
            $query->where('tanggal', '>=', $tanggalMulai);
            $periodeText = 'Mulai '.Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y');
        } elseif ($tanggalAkhir) {
            $query->where('tanggal', '<=', $tanggalAkhir);
            $periodeText = 'Sampai '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
        } elseif ($singleTanggal) {
            $query->whereDate('tanggal', $singleTanggal);
            $periodeText = Carbon::parse($singleTanggal)->isoFormat('D MMMM Y');
        } elseif ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $query->whereYear('tanggal', substr($bulan, 0, 4))->whereMonth('tanggal', substr($bulan, 5, 2));
            $periodeText = Carbon::createFromFormat('Y-m-d', $bulan.'-01')->isoFormat('MMMM Y');
        } else {
            // Tanpa periode: bulan berjalan
            $query->whereYear('tanggal', Carbon::today()->year)->whereMonth('tanggal', Carbon::today()->month);
            $periodeText = Carbon::today()->isoFormat('MMMM Y');
        }

        $presensi = $query->orderBy('tanggal', 'asc')
            ->orderBy('jam_masuk', 'asc')
            ->get();

        $stats = [
            'total' => $presensi->count(),
            'hadir' => $presensi->where('status', 'hadir')->count(),
            'onsite' => $presensi->where('mode_kerja', 'onsite')->count(),
            'wfh' => $presensi->where('mode_kerja', 'wfh')->count(),
            'izin_sakit' => $presensi->whereIn('status', ['izin', 'sakit', 'cuti'])->count(),
        ];

        $divisi = $selectedMagang?->divisi ?? $magangList->first()?->divisi ?? Divisi::first();
        $pengaturan = Pengaturan::getPengaturan();

        return view('pembimbing.presensi.cetak', compact(
            'presensi',
            'stats',
            'periodeText',
            'selectedMagang',
            'pembimbing',
            'divisi',
            'pengaturan'
        ));
    }
}