<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\HariLibur;
use App\Models\Magang;
use App\Models\PenempatanMagang;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use App\Models\RencanaKerja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BinaanController extends Controller
{
    /**
     * Daftar peserta binaan beserta rekap kehadiran (bisa dibatasi rentang tanggal).
     */
    public function index(Request $request): View
    {
        $pembimbing = Auth::user()->pembimbing;

        if (! $pembimbing) {
            return view('pembimbing.binaan.index', [
                'pembimbing' => null,
                'binaanList' => collect(),
                'divisiList' => collect(),
                'summaryToday' => [
                    'binaan_aktif' => 0,
                    'binaan_selesai' => 0,
                    'total_binaan' => 0,
                    'hadir_hari_ini' => 0,
                    'sakit_hari_ini' => 0,
                    'izin_hari_ini' => 0,
                    'cuti_hari_ini' => 0,
                    'tugas_luar_hari_ini' => 0,
                    'alpa_hari_ini' => 0,
                    'is_workday' => true,
                    'today_label' => Carbon::today()->translatedFormat('l, d F Y'),
                ],
                'summaryKumulatif' => [
                    'total_peserta' => 0,
                    'total_hari_magang' => 0,
                    'total_hadir' => 0,
                    'total_sakit' => 0,
                    'total_izin' => 0,
                    'total_cuti' => 0,
                    'total_tugas_luar' => 0,
                    'total_alpa' => 0,
                    'rata_rata_kehadiran' => 0,
                ],
                'cakupan' => ['mulai' => null, 'selesai' => null, 'total_hari' => 0],
            ]);
        }

        $query = Magang::where(function ($q) use ($pembimbing) {
            $q->where('pembimbing_id', $pembimbing->id)
                ->orWhereHas('penempatanMagang', function ($pq) use ($pembimbing) {
                    $pq->where('pembimbing_id', $pembimbing->id);
                });
        })->with(['divisi', 'pengguna', 'penempatanMagang.divisi', 'penempatanMagang.pembimbing']);

        // Pencarian (nama, NIM, atau asal instansi)
        if ($request->filled('q')) {
            $keyword = '%'.$request->q.'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', $keyword)
                    ->orWhere('no_induk', 'like', $keyword)
                    ->orWhere('instansi_pendidikan', 'like', $keyword);
            });
        }

        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        // Status magang (aktif / selesai)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $magangList = $query->orderBy('nama_lengkap', 'asc')->get();

        // Rentang tanggal rekap (opsional)
        $rangeStart = $request->filled('tanggal_mulai') ? Carbon::parse($request->tanggal_mulai)->startOfDay() : null;
        $rangeEnd = $request->filled('tanggal_akhir') ? Carbon::parse($request->tanggal_akhir)->startOfDay() : null;

        $allHolidays = $this->getHolidaysLookup();

        $todayStr = Carbon::today()->toDateString();
        $isTodayWeekend = Carbon::today()->isWeekend();
        $isTodayHoliday = isset($allHolidays[$todayStr]);
        $isTodayWorkday = ! $isTodayWeekend && ! $isTodayHoliday;

        // Muat sekaligus presensi dan izin seluruh binaan
        $userIds = $magangList->pluck('pengguna_id')->filter()->values();
        $presensiGrouped = Presensi::whereIn('pengguna_id', $userIds)->get()->groupBy('pengguna_id');
        $approvedIzinGrouped = PengajuanIzin::whereIn('pengguna_id', $userIds)
            ->where('status_approval', 'disetujui')
            ->get()
            ->groupBy('pengguna_id');

        $binaanList = $magangList->map(function ($magang) use ($allHolidays, $presensiGrouped, $approvedIzinGrouped, $todayStr, $isTodayWorkday, $isTodayWeekend, $rangeStart, $rangeEnd) {
            $userPresensi = $presensiGrouped->get($magang->pengguna_id, collect())
                ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

            $userApprovedIzin = $approvedIzinGrouped->get($magang->pengguna_id, collect());

            // 1. Apakah binaan masih aktif
            $isStatusAktif = $magang->status === 'aktif';
            $isPeriodValid = $magang->tanggal_selesai
                ? Carbon::parse($magang->tanggal_selesai)->gte(Carbon::today())
                : true;

            $penempatanAktif = $magang->getPenempatanAt(Carbon::today());
            $hasPenempatan = $magang->penempatanMagang->isNotEmpty();
            $isPenempatanAktif = ! $hasPenempatan || ($penempatanAktif !== null);

            $magang->is_binaan_aktif = $isStatusAktif && $isPeriodValid && $isPenempatanAktif;

            // 2. Status kehadiran HARI INI
            $presensiToday = $userPresensi->get($todayStr);
            $izinToday = $userApprovedIzin->first(function ($item) use ($todayStr) {
                return Carbon::parse($todayStr)->betweenIncluded(
                    Carbon::parse($item->tanggal_mulai)->startOfDay(),
                    Carbon::parse($item->tanggal_selesai)->startOfDay()
                );
            });

            $todayStatus = 'belum_absen';
            if (! $isTodayWorkday) {
                $todayStatus = $isTodayWeekend ? 'weekend' : 'libur';
            } elseif ($izinToday) {
                $todayStatus = $izinToday->jenis_izin;
            } elseif ($presensiToday) {
                $isTugasLuar = ($presensiToday->status === 'tugas_luar')
                    || ($presensiToday->mode_kerja === 'tugas_luar')
                    || (stripos((string) $presensiToday->keterangan, 'tugas luar') !== false)
                    || (stripos((string) $presensiToday->keterangan, 'dinas luar') !== false);

                $todayStatus = $isTugasLuar ? 'tugas_luar' : ($presensiToday->status ?: 'hadir');
            } else {
                $todayStatus = Carbon::now()->hour >= 16 ? 'alpa' : 'belum_absen';
            }

            $magang->today_status = $todayStatus;
            $magang->presensi_today = $presensiToday;

            // 3. Rekap kehadiran (periode magang, atau rentang filter bila ada)
            $magang->rekap = $this->calculateRekapPresensi($magang, $allHolidays, $userPresensi, $userApprovedIzin, $rangeStart, $rangeEnd);

            return $magang;
        });

        // Urutkan binaan: yang aktif lebih dahulu, kemudian sesuai abjad nama lengkap (A-Z)
        $binaanList = $binaanList->sort(function ($a, $b) {
            if ($a->is_binaan_aktif !== $b->is_binaan_aktif) {
                return $a->is_binaan_aktif ? -1 : 1;
            }

            return strcasecmp((string) $a->nama_lengkap, (string) $b->nama_lengkap);
        })->values();

        // Metrik HARI INI (binaan aktif)
        $activeBinaanList = $binaanList->filter(fn ($m) => $m->is_binaan_aktif);

        $summaryToday = [
            'binaan_aktif' => $activeBinaanList->count(),
            'binaan_selesai' => $binaanList->filter(fn ($m) => ! $m->is_binaan_aktif)->count(),
            'total_binaan' => $binaanList->count(),
            'hadir_hari_ini' => $activeBinaanList->filter(fn ($m) => in_array($m->today_status, ['hadir', 'tugas_luar']))->count(),
            'sakit_hari_ini' => $activeBinaanList->filter(fn ($m) => $m->today_status === 'sakit')->count(),
            'izin_hari_ini' => $activeBinaanList->filter(fn ($m) => $m->today_status === 'izin')->count(),
            'cuti_hari_ini' => $activeBinaanList->filter(fn ($m) => $m->today_status === 'cuti')->count(),
            'tugas_luar_hari_ini' => $activeBinaanList->filter(fn ($m) => $m->today_status === 'tugas_luar')->count(),
            'alpa_hari_ini' => $activeBinaanList->filter(fn ($m) => in_array($m->today_status, ['alpa', 'belum_absen']))->count(),
            'is_workday' => $isTodayWorkday,
            'today_label' => Carbon::today()->translatedFormat('l, d F Y'),
        ];

        $summaryKumulatif = [
            'total_peserta' => $binaanList->count(),
            'total_hari_magang' => $binaanList->sum(fn ($m) => $m->rekap['total_hari_magang']),
            'total_hadir' => $binaanList->sum(fn ($m) => $m->rekap['total_hadir']),
            'total_sakit' => $binaanList->sum(fn ($m) => $m->rekap['total_sakit']),
            'total_izin' => $binaanList->sum(fn ($m) => $m->rekap['total_izin']),
            'total_cuti' => $binaanList->sum(fn ($m) => $m->rekap['total_cuti']),
            'total_tugas_luar' => $binaanList->sum(fn ($m) => $m->rekap['total_tugas_luar']),
            'total_alpa' => $binaanList->sum(fn ($m) => $m->rekap['total_alpa']),
            'rata_rata_kehadiran' => $binaanList->count() > 0
                ? round($binaanList->avg(fn ($m) => $m->rekap['persentase_kehadiran']), 1)
                : 0,
        ];

        // Cakupan rekap untuk kartu di atas daftar
        $cakupan = [
            'mulai' => $binaanList->map(fn ($m) => $m->rekap['cakupan_mulai'])->filter()->min(),
            'selesai' => $binaanList->map(fn ($m) => $m->rekap['cakupan_selesai'])->filter()->max(),
            'total_hari' => (int) $binaanList->max(fn ($m) => $m->rekap['hari_kerja_berjalan']),
        ];

        $divisiList = Divisi::orderBy('nama_divisi', 'asc')->get();

        return view('pembimbing.binaan.index', compact(
            'pembimbing',
            'binaanList',
            'divisiList',
            'summaryToday',
            'summaryKumulatif',
            'cakupan'
        ));
    }

    /**
     * Detail seorang peserta binaan: profil, rekap individual, presensi hari ini, tindak lanjut.
     */
    public function show(int $id): View
    {
        $pembimbing = Auth::user()->pembimbing;

        if (! $pembimbing) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $magang = Magang::where(function ($q) use ($pembimbing) {
            $q->where('pembimbing_id', $pembimbing->id)
                ->orWhereHas('penempatanMagang', function ($pq) use ($pembimbing) {
                    $pq->where('pembimbing_id', $pembimbing->id);
                });
        })
            ->with(['divisi', 'pengguna', 'penempatanMagang.divisi', 'penempatanMagang.pembimbing'])
            ->findOrFail($id);

        $allHolidays = $this->getHolidaysLookup();

        $presensiQuery = Presensi::where('pengguna_id', $magang->pengguna_id);
        $pembimbing->applyPeriodConstraint($presensiQuery, 'presensi.tanggal', 'presensi.pengguna_id');
        $userPresensi = $presensiQuery->get()
            ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

        $userApprovedIzinQuery = PengajuanIzin::where('pengguna_id', $magang->pengguna_id)
            ->where('status_approval', 'disetujui');
        $pembimbing->applyPeriodConstraint($userApprovedIzinQuery, 'pengajuan_izin.tanggal_mulai', 'pengajuan_izin.pengguna_id');
        $userApprovedIzin = $userApprovedIzinQuery->get();

        $rekap = $this->calculateRekapPresensi($magang, $allHolidays, $userPresensi, $userApprovedIzin);

        $presensiHariIni = Presensi::where('pengguna_id', $magang->pengguna_id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $aktivitasPendingQuery = Aktivitas::where('pengguna_id', $magang->pengguna_id)
            ->where('status', 'pending');
        $pembimbing->applyPeriodConstraint($aktivitasPendingQuery, 'aktivitas.tanggal', 'aktivitas.pengguna_id');
        $aktivitasPending = $aktivitasPendingQuery->orderBy('tanggal', 'desc')->get();

        $pId = $pembimbing->id;
        $rencanaAktif = RencanaKerja::where('magang_id', $magang->id)
            ->where(function ($q) use ($pId) {
                $q->where('pembimbing_id', $pId)
                    ->orWhere(function ($fallback) use ($pId) {
                        $fallback->whereNull('pembimbing_id')
                            ->whereIn('magang_id', Magang::where('pembimbing_id', $pId)->pluck('id'));
                    });
            })
            ->where('status', 'aktif')
            ->orderBy('id', 'desc')
            ->get();

        $pekerjaanAktif = $rencanaAktif;

        $catatanPulangHariIni = $this->extractCatatanPulang($presensiHariIni, $userApprovedIzin);

        return view('pembimbing.binaan.show', compact(
            'pembimbing',
            'magang',
            'rekap',
            'presensiHariIni',
            'catatanPulangHariIni',
            'aktivitasPending',
            'pekerjaanAktif',
            'rencanaAktif'
        ));
    }

    /**
     * Riwayat presensi harian seorang peserta binaan ("Catatan terbaru").
     */
    public function riwayat(int $id): View
    {
        $pembimbing = Auth::user()->pembimbing;
        abort_if(! $pembimbing, 403, 'Akses tidak diizinkan.');

        $magang = Magang::where(function ($q) use ($pembimbing) {
            $q->where('pembimbing_id', $pembimbing->id)
                ->orWhereHas('penempatanMagang', function ($pq) use ($pembimbing) {
                    $pq->where('pembimbing_id', $pembimbing->id);
                });
        })->findOrFail($id);

        $presensiQuery = Presensi::where('pengguna_id', $magang->pengguna_id)
            ->with('pengajuanTugasLuar')
            ->orderBy('tanggal', 'desc');
        $pembimbing->applyPeriodConstraint($presensiQuery, 'presensi.tanggal', 'presensi.pengguna_id');
        $presensi = $presensiQuery->paginate(10);

        $izinQuery = PengajuanIzin::where('pengguna_id', $magang->pengguna_id)
            ->where('status_approval', 'disetujui');
        $pembimbing->applyPeriodConstraint($izinQuery, 'pengajuan_izin.tanggal_mulai', 'pengajuan_izin.pengguna_id');
        $izin = $izinQuery->get();

        $jam = fn ($t) => $t ? str_replace(':', '.', substr($t, 0, 5)) : '-';

        $presensi->setCollection($presensi->getCollection()->map(function ($p) use ($izin, $jam) {
            $isIzin = in_array($p->status, ['sakit', 'izin', 'cuti']);
            $label = strtoupper($p->status);

            $terlambat = null;
            if (! $isIzin && $p->jam_masuk && $p->jam_masuk > '08:00:00') {
                $terlambat = (int) ceil((strtotime($p->jam_masuk) - strtotime('08:00:00')) / 60);
            }

            $catatanPulang = $this->extractCatatanPulang($p, $izin);

            return [
                'tanggal' => $p->tanggal,
                'mode' => $isIzin ? $label : ($p->is_tugas_luar ? 'TUGAS LUAR' : strtoupper((string) $p->mode_kerja)),
                'jam_masuk' => $isIzin ? $label : $jam($p->jam_masuk_efektif),
                'jam_pulang' => $isIzin ? $label : $jam($p->jam_keluar_efektif),
                'catatan_pulang' => $catatanPulang,
                'terlambat' => $terlambat,
                'foto_masuk_url' => $p->foto_masuk_url,
                'foto_keluar_url' => $p->foto_keluar_url,
                'lampiran_url' => $isIzin
                    ? $izin->first(fn ($i) => $p->tanggal->betweenIncluded($i->tanggal_mulai, $i->tanggal_selesai))?->bukti_file_url
                    : null,
            ];
        }));

        return view('pembimbing.binaan.riwayat', compact('magang', 'presensi'));
    }

    /**
     * Mengekstrak alasan/catatan pulang lebih awal (sebelum pukul 16.00).
     *
     * @param  Collection<int, PengajuanIzin>|null  $izin
     */
    private function extractCatatanPulang(?Presensi $p, ?Collection $izin = null): ?string
    {
        if (! $p) {
            return null;
        }

        if (in_array($p->status, ['sakit', 'izin', 'cuti'])) {
            return null;
        }

        if ($p->catatan_pulang) {
            return $p->catatan_pulang;
        }

        if (! $p->jam_keluar || $p->jam_keluar >= '16:00:00') {
            return null;
        }

        $rawKet = (string) $p->keterangan;
        $alasan = null;

        if (preg_match('/\|\s*Pulang:\s*([^|]+)/i', $rawKet, $m)) {
            $alasan = trim($m[1]);
        } elseif (preg_match('/Pulang:\s*([^|]+)/i', $rawKet, $m)) {
            $alasan = trim($m[1]);
        } elseif (preg_match('/\b(Sakit|Izin)(?:\s*\([^)]+\))?/i', $rawKet, $m)) {
            $alasan = trim($m[0]);
        } elseif ($izin) {
            $tgl = $p->tanggal;
            $izinItem = $izin->first(function ($i) use ($tgl) {
                if (! $tgl) {
                    return false;
                }
                $tglCarbon = Carbon::parse($tgl)->startOfDay();

                return $tglCarbon->betweenIncluded(
                    Carbon::parse($i->tanggal_mulai)->startOfDay(),
                    Carbon::parse($i->tanggal_selesai)->startOfDay()
                );
            });
            if ($izinItem) {
                $alasan = $izinItem->jenis_izin;
            }
        }

        if (! $alasan && ! empty($rawKet)) {
            $cleaned = trim(preg_replace('/\[?Terlambat[^\]|]+\]?\s*\|?\s*/i', '', $rawKet));
            if (! empty($cleaned)) {
                $alasan = $cleaned;
            }
        }

        if (! $alasan) {
            return null;
        }

        $alasan = preg_replace('/^Pulang:\s*/i', '', $alasan);

        return strtolower(trim($alasan));
    }

    /**
     * Tanggal merah dari database dan kalender nasional bawaan.
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
     * Menghitung estimasi durasi dalam bulan dan teks yang ramah pengguna.
     *
     * @return array{0: string, 1: int}
     */
    private function calculateDurasiBulan(Carbon $start, Carbon $end): array
    {
        $totalDays = $start->diffInDays($end) + 1;

        if ($totalDays < 25) {
            return [$totalDays.' Hari', 0];
        }

        $bulan = (int) round($totalDays / 30.4);
        if ($bulan < 1) {
            $bulan = 1;
        }

        return [$bulan.' Bulan', $bulan];
    }

    /**
     * Mendapatkan data penempatan unit/divisi peserta binaan yang relevan.
     * Prioritas:
     * 1. Penempatan yang aktif pada targetDate (default: hari ini).
     * 2. Jika tidak ada yang aktif, ambil penempatan pertama/terakhir.
     * 3. Jika tidak ada record penempatan_magang, gunakan divisi utama dan tanggal_mulai/selesai magang dinas.
     *
     * @return array{
     *     penempatan: ?PenempatanMagang,
     *     nama_unit: string,
     *     unit_mulai: Carbon,
     *     unit_selesai: Carbon,
     *     durasi_bulan_unit_teks: string,
     *     durasi_bulan_unit: int,
     *     dinas_mulai: Carbon,
     *     dinas_selesai: Carbon,
     *     durasi_bulan_dinas_teks: string,
     *     durasi_bulan_dinas: int,
     *     is_berbeda_periode_dinas: bool
     * }
     */
    private function getUnitPlacement(Magang $magang, ?Carbon $targetDate = null): array
    {
        $target = $targetDate ? $targetDate->copy()->startOfDay() : Carbon::today()->startOfDay();

        $penempatan = null;
        $hasPenempatanRelation = $magang->relationLoaded('penempatanMagang');
        $penempatans = $hasPenempatanRelation
            ? $magang->penempatanMagang
            : $magang->penempatanMagang()->with('divisi')->orderBy('tanggal_mulai', 'asc')->get();

        if ($penempatans->isNotEmpty()) {
            $penempatan = $penempatans->first(function ($p) use ($target) {
                $mulai = $p->tanggal_mulai ? Carbon::parse($p->tanggal_mulai)->startOfDay() : null;
                $selesai = $p->tanggal_selesai ? Carbon::parse($p->tanggal_selesai)->startOfDay() : null;

                return $mulai && $selesai && $target->betweenIncluded($mulai, $selesai);
            });

            if (! $penempatan) {
                $sorted = $penempatans->sortBy('tanggal_mulai');
                $first = $sorted->first();
                $firstMulai = $first->tanggal_mulai ? Carbon::parse($first->tanggal_mulai)->startOfDay() : null;

                if ($firstMulai && $target->lt($firstMulai)) {
                    $penempatan = $first;
                } else {
                    $penempatan = $sorted->last();
                }
            }
        }

        $namaUnit = $penempatan?->divisi?->nama_divisi
            ?? $magang->divisi?->nama_divisi
            ?? 'Unit Terkait';

        $unitStart = $penempatan?->tanggal_mulai
            ? Carbon::parse($penempatan->tanggal_mulai)->startOfDay()
            : ($magang->tanggal_mulai ? Carbon::parse($magang->tanggal_mulai)->startOfDay() : Carbon::parse($magang->created_at)->startOfDay());

        $unitEnd = $penempatan?->tanggal_selesai
            ? Carbon::parse($penempatan->tanggal_selesai)->startOfDay()
            : ($magang->tanggal_selesai ? Carbon::parse($magang->tanggal_selesai)->startOfDay() : Carbon::today()->startOfDay());

        $dinasStart = $magang->tanggal_mulai
            ? Carbon::parse($magang->tanggal_mulai)->startOfDay()
            : $unitStart->copy();

        $dinasEnd = $magang->tanggal_selesai
            ? Carbon::parse($magang->tanggal_selesai)->startOfDay()
            : $unitEnd->copy();

        if ($unitStart->gt($unitEnd)) {
            [$unitStart, $unitEnd] = [$unitEnd->copy(), $unitStart->copy()];
        }
        if ($dinasStart->gt($dinasEnd)) {
            [$dinasStart, $dinasEnd] = [$dinasEnd->copy(), $dinasStart->copy()];
        }

        [$durasiBulanUnitTeks, $durasiBulanUnit] = $this->calculateDurasiBulan($unitStart, $unitEnd);
        [$durasiBulanDinasTeks, $durasiBulanDinas] = $this->calculateDurasiBulan($dinasStart, $dinasEnd);

        $isBerbeda = ! $unitStart->equalTo($dinasStart) || ! $unitEnd->equalTo($dinasEnd);

        return [
            'penempatan' => $penempatan,
            'nama_unit' => $namaUnit,
            'unit_mulai' => $unitStart,
            'unit_selesai' => $unitEnd,
            'durasi_bulan_unit_teks' => $durasiBulanUnitTeks,
            'durasi_bulan_unit' => $durasiBulanUnit,
            'dinas_mulai' => $dinasStart,
            'dinas_selesai' => $dinasEnd,
            'durasi_bulan_dinas_teks' => $durasiBulanDinasTeks,
            'durasi_bulan_dinas' => $durasiBulanDinas,
            'is_berbeda_periode_dinas' => $isBerbeda,
        ];
    }

    /**
     * Menghitung rekap kehadiran hari kerja dari awal periode sampai KEMARIN (hari ini tidak ikut dihitung).
     * Tugas Luar dihitung sebagai Hadir sekaligus dicatat di total_tugas_luar (subset dari hadir).
     * Kategori yang saling lepas: hadir (termasuk TL) + sakit + izin + cuti + alpa = hari_kerja_berjalan.
     *
     * @param  array<string, string>  $allHolidays
     */
    private function calculateRekapPresensi(
        Magang $magang,
        array $allHolidays,
        Collection $userPresensi,
        Collection $userApprovedIzin,
        ?Carbon $rangeStart = null,
        ?Carbon $rangeEnd = null
    ): array {
        $unitInfo = $this->getUnitPlacement($magang, $rangeStart ?? Carbon::today());

        $unitStart = $unitInfo['unit_mulai']->copy();
        $unitEnd = $unitInfo['unit_selesai']->copy();

        // Total hari kerja resmi pada periode unit (tanpa akhir pekan & tanggal merah)
        $totalHariUnit = 0;
        $curUnit = $unitStart->copy();
        while ($curUnit->lte($unitEnd)) {
            if (! $curUnit->isWeekend() && ! isset($allHolidays[$curUnit->toDateString()])) {
                $totalHariUnit++;
            }
            $curUnit->addDay();
        }

        // Hitung progres "hari keberapa" saat ini (inklusif hari ini jika hari kerja)
        $today = Carbon::today()->startOfDay();
        $hariKe = 0;
        if ($today->lt($unitStart)) {
            $hariKe = 0;
            $hariKeLabel = 'Belum dimulai';
        } elseif ($today->gt($unitEnd)) {
            $hariKe = $totalHariUnit;
            $hariKeLabel = 'Selesai ('.$totalHariUnit.' hari kerja)';
        } else {
            $curDay = $unitStart->copy();
            while ($curDay->lte($today)) {
                if (! $curDay->isWeekend() && ! isset($allHolidays[$curDay->toDateString()])) {
                    $hariKe++;
                }
                $curDay->addDay();
            }
            $hariKeLabel = 'Hari ke-'.$hariKe.' dari '.$totalHariUnit.' hari kerja';
        }

        // Batasi rentang rekap jika ada filter
        $startDate = $unitStart->copy();
        $endDate = $unitEnd->copy();

        if ($rangeStart && $rangeStart->gt($startDate)) {
            $startDate = $rangeStart->copy();
        }
        if ($rangeEnd && $rangeEnd->lt($endDate)) {
            $endDate = $rangeEnd->copy();
        }

        // Total hari kerja cakupan filter
        $totalHariCakupan = 0;
        $curCakupan = $startDate->copy();
        while ($curCakupan->lte($endDate)) {
            if (! $curCakupan->isWeekend() && ! isset($allHolidays[$curCakupan->toDateString()])) {
                $totalHariCakupan++;
            }
            $curCakupan->addDay();
        }

        // Presensi harian dihitung sampai kemarin saja (hari ini live di kartu presensi hari ini)
        $effectiveEnd = $endDate->lt($today) ? $endDate->copy() : $today->copy()->subDay();

        $hariKerjaBerjalan = 0;
        $totalHadir = 0;
        $totalSakit = 0;
        $totalIzin = 0;
        $totalCuti = 0;
        $totalTugasLuar = 0;
        $totalAlpa = 0;

        if ($startDate->lte($effectiveEnd)) {
            $cur = $startDate->copy();
            while ($cur->lte($effectiveEnd)) {
                $dateStr = $cur->toDateString();

                if ($cur->isWeekend() || isset($allHolidays[$dateStr])) {
                    $cur->addDay();

                    continue;
                }

                $hariKerjaBerjalan++;

                // Izin/sakit/cuti yang sudah disetujui pembimbing
                $izinResmi = $userApprovedIzin->first(function ($item) use ($cur) {
                    return $cur->betweenIncluded(
                        Carbon::parse($item->tanggal_mulai)->startOfDay(),
                        Carbon::parse($item->tanggal_selesai)->startOfDay()
                    );
                });

                $presensi = $userPresensi->get($dateStr);

                if ($izinResmi) {
                    match ($izinResmi->jenis_izin) {
                        'sakit' => $totalSakit++,
                        'cuti' => $totalCuti++,
                        default => $totalIzin++,
                    };
                } elseif ($presensi) {
                    $isTugasLuar = ($presensi->status === 'tugas_luar')
                        || ($presensi->mode_kerja === 'tugas_luar')
                        || (stripos((string) $presensi->keterangan, 'tugas luar') !== false)
                        || (stripos((string) $presensi->keterangan, 'dinas luar') !== false);

                    if ($isTugasLuar) {
                        $totalTugasLuar++;
                        $totalHadir++;
                    } elseif ($presensi->status === 'sakit') {
                        $totalSakit++;
                    } elseif ($presensi->status === 'cuti') {
                        $totalCuti++;
                    } elseif ($presensi->status === 'izin') {
                        $totalIzin++;
                    } elseif ($presensi->status === 'hadir' || ! empty($presensi->jam_masuk)) {
                        $totalHadir++;
                    } else {
                        $totalAlpa++;
                    }
                } else {
                    $totalAlpa++;
                }

                $cur->addDay();
            }
        }

        $persentase = $hariKerjaBerjalan > 0
            ? round(($totalHadir / $hariKerjaBerjalan) * 100, 1)
            : 0.0;

        return [
            'nama_unit' => $unitInfo['nama_unit'],
            'unit_mulai' => $unitStart->toDateString(),
            'unit_selesai' => $unitEnd->toDateString(),
            'unit_mulai_formatted' => $unitStart->locale('id')->isoFormat('D MMM Y'),
            'unit_selesai_formatted' => $unitEnd->locale('id')->isoFormat('D MMM Y'),
            'durasi_bulan_unit' => $unitInfo['durasi_bulan_unit'],
            'durasi_bulan_unit_teks' => $unitInfo['durasi_bulan_unit_teks'],
            'total_hari_unit' => $totalHariUnit,
            'dinas_mulai' => $unitInfo['dinas_mulai']->toDateString(),
            'dinas_selesai' => $unitInfo['dinas_selesai']->toDateString(),
            'dinas_mulai_formatted' => $unitInfo['dinas_mulai']->locale('id')->isoFormat('D MMM Y'),
            'dinas_selesai_formatted' => $unitInfo['dinas_selesai']->locale('id')->isoFormat('D MMM Y'),
            'durasi_bulan_dinas' => $unitInfo['durasi_bulan_dinas'],
            'durasi_bulan_dinas_teks' => $unitInfo['durasi_bulan_dinas_teks'],
            'is_berbeda_periode_dinas' => $unitInfo['is_berbeda_periode_dinas'],
            'hari_ke' => $hariKe,
            'hari_ke_label' => $hariKeLabel,
            'total_hari_magang' => $totalHariCakupan,
            'hari_kerja_berjalan' => $hariKerjaBerjalan,
            'total_hadir' => $totalHadir,
            'total_sakit' => $totalSakit,
            'total_izin' => $totalIzin,
            'total_cuti' => $totalCuti,
            'total_tugas_luar' => $totalTugasLuar,
            'total_alpa' => $totalAlpa,
            'persentase_kehadiran' => $persentase,
            'sisa_hari_kerja' => max(0, $totalHariUnit - $hariKerjaBerjalan),
            'cakupan_mulai' => $startDate->toDateString(),
            'cakupan_selesai' => $effectiveEnd->gte($startDate) ? $effectiveEnd->toDateString() : $startDate->toDateString(),
        ];
    }
}
