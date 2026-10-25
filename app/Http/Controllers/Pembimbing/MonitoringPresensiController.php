<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\HariLibur;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\PengajuanTugasLuar;
use App\Models\Pengaturan;
use App\Models\Presensi;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MonitoringPresensiController extends Controller
{
    public function __construct(
        private PdfService $pdfService
    ) {}

    private function getPembimbing(): ?Pembimbing
    {
        return Auth::user()->pembimbing;
    }

    /**
     * Peserta magang yang dibimbing oleh pembimbing yang login
     * (baik pembimbing pokok maupun penempatan rotasi unit).
     */
    private function getSupervisedMagangList(): Collection
    {
        $pembimbing = $this->getPembimbing();
        if (! $pembimbing) {
            return collect();
        }

        return $pembimbing->getSupervisedMagangQuery()
            ->with(['divisi', 'pengguna', 'penempatanMagang.divisi', 'penempatanMagang.pembimbing'])
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
        $query = Presensi::query()
            ->with(['pengguna.magang', 'pengajuanTugasLuar']);

        // Batasi presensi hanya pada saat peserta dibina oleh pembimbing ini (sesuai periode unit)
        $pembimbing->applyPeriodConstraint($query, 'presensi.tanggal', 'presensi.pengguna_id');

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

        return view('pembimbing.presensi.index', compact('pembimbing', 'presensi', 'magangList'));
    }

    /**
     * Mengubah satu baris presensi menjadi data siap tampil di kartu.
     */
    private function susunCatatan(Presensi $p, Collection $izinUser): array
    {
        $magang = $p->pengguna?->magang;
        $isIzin = in_array($p->status, ['sakit', 'izin', 'cuti']);
        $isTl = ! $isIzin && $p->is_tugas_luar;
        $jam = fn ($t) => $t ? substr($t, 0, 5) : '-';

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
            $verifikasi = is_null($p->face_distance_masuk) ? '' : ' - Lokasi & wajah terverifikasi';
            $keterangan = $p->keterangan ? $p->keterangan.' ('.$lokasi.$verifikasi.')' : $lokasi.$verifikasi;
        }

        $catatanPulang = null;
        if (! $isIzin) {
            $catatanPulang = $p->catatan_pulang;
            if (! $catatanPulang && $p->jam_keluar && $p->jam_keluar < '16:00:00') {
                $rawKet = (string) $p->keterangan;
                if (preg_match('/\|\s*Pulang:\s*([^|]+)/i', $rawKet, $m)) {
                    $catatanPulang = trim($m[1]);
                } elseif (preg_match('/Pulang:\s*([^|]+)/i', $rawKet, $m)) {
                    $catatanPulang = trim($m[1]);
                } elseif (preg_match('/\b(Sakit|Izin)(?:\s*\([^)]+\))?/i', $rawKet, $m)) {
                    $catatanPulang = trim($m[0]);
                } else {
                    $izinHari = $izinUser->first(fn ($i) => $p->tanggal && $p->tanggal->betweenIncluded($i->tanggal_mulai, $i->tanggal_selesai));
                    if ($izinHari) {
                        $catatanPulang = $izinHari->jenis_izin;
                    }
                }
                if ($catatanPulang) {
                    $catatanPulang = strtolower(trim((string) preg_replace('/^Pulang:\s*/i', '', $catatanPulang)));
                }
            }
        }

        return [
            'pengguna_id' => $p->pengguna_id,
            'magang' => $magang,
            'nama' => $magang?->nama_lengkap ?? ($p->pengguna->username ?? '-'),
            'tanggal' => $p->tanggal,
            'badge' => $isIzin ? ucfirst($p->status) : ($isTl ? 'TL' : ($p->status === 'alpa' ? 'Alpa' : 'Hadir')),
            'badge_kunci' => $isIzin ? $p->status : ($isTl ? 'tl' : ($p->status === 'alpa' ? 'alpa' : 'hadir')),
            'jam_masuk' => $isIzin ? '-' : $jam($p->jam_masuk_efektif),
            'jam_keluar' => $isIzin ? '-' : $jam($p->jam_keluar_efektif),
            'catatan_pulang' => $catatanPulang,
            'terlambat' => $terlambat,
            'foto_masuk_url' => $isIzin ? null : $p->foto_masuk_url,
            'foto_keluar_url' => $isIzin ? null : $p->foto_keluar_url,
            'lampiran_url' => $izin?->bukti_file_url,
            'keterangan' => $keterangan,
        ];
    }

    /**
     * Tanggal libur lookup.
     *
     * @return array<string, string>
     */
    private function getHolidaysLookup(): array
    {
        $dbHolidays = HariLibur::all()->mapWithKeys(function ($item) {
            $tgl = Carbon::parse($item->tanggal)->toDateString();
            $nama = $item->nama ?: ($item->keterangan ?: 'Hari Libur Nasional');

            return [$tgl => $nama];
        })->toArray();

        return array_merge(HariLibur::getDaftarLiburNasionalBawaan(), $dbHolidays);
    }

    /**
     * Cetak rekapitulasi riwayat presensi seluruh binaan pada periode yang ditentukan.
     */
    public function cetak(Request $request)
    {
        $pembimbing = $this->getPembimbing();
        if (! $pembimbing) {
            abort(403, 'Akses tidak diizinkan. Profil pembimbing tidak ditemukan.');
        }

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $singleTanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');

        if ($tanggalMulai && $tanggalAkhir && strtotime($tanggalMulai) && strtotime($tanggalAkhir)) {
            $startDate = Carbon::parse($tanggalMulai)->startOfDay();
            $endDate = Carbon::parse($tanggalAkhir)->startOfDay();
            $periodeText = $startDate->locale('id')->isoFormat('D MMMM Y').' s/d '.$endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeSubJudul = 'Dari Tanggal '.$startDate->locale('id')->isoFormat('D MMMM Y').' Sampai Tanggal '.$endDate->locale('id')->isoFormat('D MMMM Y');
        } elseif ($tanggalMulai && strtotime($tanggalMulai)) {
            $startDate = Carbon::parse($tanggalMulai)->startOfDay();
            $endDate = Carbon::today()->startOfDay();
            $periodeText = 'Mulai '.$startDate->locale('id')->isoFormat('D MMMM Y');
            $periodeSubJudul = 'Mulai Tanggal '.$startDate->locale('id')->isoFormat('D MMMM Y');
        } elseif ($tanggalAkhir && strtotime($tanggalAkhir)) {
            $startDate = Carbon::create(2020, 1, 1)->startOfDay();
            $endDate = Carbon::parse($tanggalAkhir)->startOfDay();
            $periodeText = 'Sampai '.$endDate->locale('id')->isoFormat('D MMMM Y');
            $periodeSubJudul = 'Sampai Tanggal '.$endDate->locale('id')->isoFormat('D MMMM Y');
        } elseif ($singleTanggal && strtotime($singleTanggal)) {
            $startDate = Carbon::parse($singleTanggal)->startOfDay();
            $endDate = Carbon::parse($singleTanggal)->startOfDay();
            $periodeText = $startDate->locale('id')->isoFormat('D MMMM Y');
            $periodeSubJudul = 'Tanggal '.$startDate->locale('id')->isoFormat('D MMMM Y');
        } elseif ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $startDate = Carbon::createFromFormat('Y-m-d', $bulan.'-01')->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->startOfDay();
            $periodeText = $startDate->locale('id')->isoFormat('MMMM Y');
            $periodeSubJudul = 'Bulan '.$startDate->locale('id')->isoFormat('MMMM Y');
        } else {
            // Default: bulan berjalan
            $startDate = Carbon::today()->startOfMonth();
            $endDate = Carbon::today()->endOfMonth();
            $periodeText = Carbon::today()->locale('id')->isoFormat('MMMM Y');
            $periodeSubJudul = 'Bulan '.Carbon::today()->locale('id')->isoFormat('MMMM Y');
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy(), $startDate->copy()];
        }

        $magangQuery = $pembimbing->getSupervisedMagangQuery()
            ->with(['divisi', 'pengguna', 'penempatanMagang.divisi', 'penempatanMagang.pembimbing']);

        $selectedMagang = null;
        if ($request->filled('magang_id')) {
            $selectedMagang = $pembimbing->getSupervisedMagangQuery()->find((int) $request->magang_id);
            if ($selectedMagang) {
                $magangQuery->where('magang.id', $selectedMagang->id);
            }
        }

        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $magangQuery->where(function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', $keyword)
                    ->orWhere('no_induk', 'like', $keyword);
            });
        }

        if ($request->filled('status')) {
            $magangQuery->where('status', $request->status);
        }

        $allMagangs = $magangQuery->orderBy('nama_lengkap', 'asc')->get();
        $userIds = $allMagangs->pluck('pengguna_id')->filter()->values();

        // Ambil presensi dalam rentang tanggal
        $presensiQuery = Presensi::whereIn('pengguna_id', $userIds)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($request->filled('mode_kerja')) {
            $presensiQuery->where('mode_kerja', $request->mode_kerja);
        }

        $pembimbing->applyPeriodConstraint($presensiQuery, 'presensi.tanggal', 'presensi.pengguna_id');
        $presensiGrouped = $presensiQuery->get()->groupBy('pengguna_id');

        // Ambil izin yang disetujui dalam rentang tanggal
        $approvedIzinGrouped = PengajuanIzin::whereIn('pengguna_id', $userIds)
            ->where('status_approval', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $endDate->toDateString())
            ->whereDate('tanggal_selesai', '>=', $startDate->toDateString())
            ->get()
            ->groupBy('pengguna_id');

        // Ambil tugas luar yang disetujui dalam rentang tanggal
        $approvedTugasLuarGrouped = PengajuanTugasLuar::whereIn('pengguna_id', $userIds)
            ->where('status_verifikasi', 'disetujui')
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy('pengguna_id');

        $allHolidays = $this->getHolidaysLookup();
        $today = Carbon::today()->startOfDay();
        $pembimbingId = $pembimbing->id;

        $rekapList = collect();

        foreach ($allMagangs as $magang) {
            $penempatans = $magang->penempatanMagang->filter(function ($p) use ($pembimbingId, $magang) {
                return $p->pembimbing_id === $pembimbingId
                    || (is_null($p->pembimbing_id) && $magang->pembimbing_id === $pembimbingId);
            });

            $unitStart = null;
            $unitEnd = null;
            $periodeUnitTeks = '-';

            if ($penempatans->isNotEmpty()) {
                // Cari penempatan yang tumpang tindih dengan periode yang dipilih
                $overlapping = $penempatans->filter(function ($p) use ($startDate, $endDate) {
                    $pStart = Carbon::parse($p->tanggal_mulai)->startOfDay();
                    $pEnd = Carbon::parse($p->tanggal_selesai)->startOfDay();

                    return $pStart->lte($endDate) && $pEnd->gte($startDate);
                });

                if ($overlapping->isNotEmpty()) {
                    $unitStart = Carbon::parse($overlapping->min('tanggal_mulai'))->startOfDay();
                    $unitEnd = Carbon::parse($overlapping->max('tanggal_selesai'))->startOfDay();

                    $periodeUnitTeks = $overlapping->map(function ($p) {
                        return Carbon::parse($p->tanggal_mulai)->locale('id')->isoFormat('D MMM Y')
                            .' - '
                            .Carbon::parse($p->tanggal_selesai)->locale('id')->isoFormat('D MMM Y');
                    })->implode(', ');
                } else {
                    if ($magang->pembimbing_id !== $pembimbingId && ! $request->filled('magang_id')) {
                        continue;
                    }
                    $firstP = $penempatans->sortBy('tanggal_mulai')->first();
                    $unitStart = Carbon::parse($firstP->tanggal_mulai)->startOfDay();
                    $unitEnd = Carbon::parse($firstP->tanggal_selesai)->startOfDay();
                    $periodeUnitTeks = Carbon::parse($firstP->tanggal_mulai)->locale('id')->isoFormat('D MMM Y')
                        .' - '
                        .Carbon::parse($firstP->tanggal_selesai)->locale('id')->isoFormat('D MMM Y');
                }
            } else {
                if ($magang->pembimbing_id === $pembimbingId) {
                    $unitStart = $magang->tanggal_mulai ? Carbon::parse($magang->tanggal_mulai)->startOfDay() : null;
                    $unitEnd = $magang->tanggal_selesai ? Carbon::parse($magang->tanggal_selesai)->startOfDay() : null;

                    if ($unitStart && $unitEnd) {
                        $periodeUnitTeks = $unitStart->locale('id')->isoFormat('D MMM Y')
                            .' - '
                            .$unitEnd->locale('id')->isoFormat('D MMM Y');
                    } elseif ($unitStart) {
                        $periodeUnitTeks = 'Mulai '.$unitStart->locale('id')->isoFormat('D MMM Y');
                    }
                } else {
                    continue;
                }
            }

            // Hitung irisan tanggal evaluasi antara filter periode dan masa unit
            $effStart = $startDate->copy();
            $effEnd = $endDate->copy();

            if ($unitStart && $unitStart->gt($effStart)) {
                $effStart = $unitStart->copy();
            }
            if ($unitEnd && $unitEnd->lt($effEnd)) {
                $effEnd = $unitEnd->copy();
            }

            $userPresensi = $presensiGrouped->get($magang->pengguna_id, collect())
                ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

            $userApprovedIzin = $approvedIzinGrouped->get($magang->pengguna_id, collect());

            $userApprovedTugasLuar = $approvedTugasLuarGrouped->get($magang->pengguna_id, collect())
                ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

            $totalHadir = 0;
            $totalSakit = 0;
            $totalIzin = 0;
            $totalAlfa = 0;
            $totalTL = 0;

            if ($effStart->lte($effEnd)) {
                $cur = $effStart->copy();
                while ($cur->lte($effEnd)) {
                    $dateStr = $cur->toDateString();

                    $presensi = $userPresensi->get($dateStr);
                    $tugasLuar = $userApprovedTugasLuar->get($dateStr);
                    $izinResmi = $userApprovedIzin->first(function ($item) use ($cur) {
                        return $cur->betweenIncluded(
                            Carbon::parse($item->tanggal_mulai)->startOfDay(),
                            Carbon::parse($item->tanggal_selesai)->startOfDay()
                        );
                    });

                    // Lewatkan hari libur / akhir pekan / tanggal masa depan KECUALI jika ada rekaman presensi / tugas luar / izin
                    $isWeekendOrHoliday = $cur->isWeekend() || isset($allHolidays[$dateStr]);
                    $isFuture = $cur->gt($today);

                    if (($isWeekendOrHoliday || $isFuture) && ! $presensi && ! $tugasLuar && ! $izinResmi) {
                        $cur->addDay();

                        continue;
                    }

                    // Pastikan pada tanggal ini pembimbingnya memang pembimbing ini
                    $pembimbingAktif = $magang->getPembimbingAt($cur);
                    if (! $pembimbingAktif || $pembimbingAktif->id !== $pembimbingId) {
                        $cur->addDay();

                        continue;
                    }

                    if ($izinResmi) {
                        if ($izinResmi->jenis_izin === 'sakit') {
                            $totalSakit++;
                        } else {
                            $totalIzin++;
                        }
                    } elseif ($presensi || $tugasLuar) {
                        $isTugasLuar = $tugasLuar
                            || ($presensi && (
                                $presensi->status === 'tugas_luar'
                                || $presensi->mode_kerja === 'tugas_luar'
                                || stripos((string) $presensi->keterangan, 'tugas luar') !== false
                                || stripos((string) $presensi->keterangan, 'dinas luar') !== false
                            ));

                        if ($isTugasLuar) {
                            $totalTL++;
                            $totalHadir++;
                        } elseif ($presensi->status === 'sakit') {
                            $totalSakit++;
                        } elseif ($presensi->status === 'cuti' || $presensi->status === 'izin') {
                            $totalIzin++;
                        } elseif ($presensi->status === 'hadir' || ! empty($presensi->jam_masuk)) {
                            $totalHadir++;
                        } else {
                            $totalAlfa++;
                        }
                    } else {
                        // Tidak ada presensi dan tidak ada izin pada hari kerja
                        if ($cur->lt($today) || Carbon::now()->hour >= 16) {
                            $totalAlfa++;
                        }
                    }

                    $cur->addDay();
                }
            }

            $rekapList->push((object) [
                'id' => $magang->id,
                'nama_lengkap' => $magang->nama_lengkap,
                'no_induk' => $magang->no_induk,
                'instansi_pendidikan' => $magang->instansi_pendidikan,
                'divisi' => $magang->divisi,
                'periode_unit_teks' => $periodeUnitTeks,
                'total_hadir' => $totalHadir,
                'total_sakit' => $totalSakit,
                'total_izin' => $totalIzin,
                'total_alfa' => $totalAlfa,
                'total_tl' => $totalTL,
            ]);
        }

        $stats = [
            'total_peserta' => $rekapList->count(),
            'total_hadir' => $rekapList->sum('total_hadir'),
            'total_sakit' => $rekapList->sum('total_sakit'),
            'total_izin' => $rekapList->sum('total_izin'),
            'total_alfa' => $rekapList->sum('total_alfa'),
            'total_tl' => $rekapList->sum('total_tl'),
        ];

        $divisi = $pembimbing->divisi ?? $selectedMagang?->divisi ?? Divisi::first();
        $pengaturan = Pengaturan::getPengaturan();

        $viewData = [
            'rekapList' => $rekapList,
            'presensi' => $rekapList,
            'stats' => $stats,
            'periodeText' => $periodeText,
            'periodeSubJudul' => $periodeSubJudul,
            'selectedMagang' => $selectedMagang,
            'pembimbing' => $pembimbing,
            'divisi' => $divisi,
            'pengaturan' => $pengaturan,
        ];

        if ($request->input('format') === 'html') {
            return view('pembimbing.presensi.cetak', $viewData);
        }

        $cleanPeriode = str_replace([' ', '/', '\\'], '-', $periodeText);
        $filename = 'Laporan-Presensi-Magang-'.$cleanPeriode.'.pdf';

        return $this->pdfService->renderView('pembimbing.presensi.cetak', $viewData, $filename, 'P');
    }
}
