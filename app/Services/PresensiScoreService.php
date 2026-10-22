<?php

namespace App\Services;

use App\Models\HariLibur;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\PenempatanMagang;
use App\Models\PengajuanIzin;
use App\Models\PengajuanTugasLuar;
use App\Models\Presensi;
use Carbon\Carbon;

class PresensiScoreService
{
    /**
     * Jam operasional standar
     */
    public const JAM_MASUK_STANDAR = '08:00:00';

    public const JAM_KELUAR_STANDAR = '16:00:00';

    public const MENIT_PER_HARI = 480; // 8 jam x 60 menit

    /**
     * Menemukan kriteria penilaian yang bertindak sebagai kriteria presensi objektif.
     */
    public function getKriteriaPresensi(): ?KriteriaPenilaian
    {
        // 1. Prioritaskan kriteria dengan flag is_presensi = true
        $kriteria = KriteriaPenilaian::where('is_presensi', true)->first();
        if ($kriteria) {
            return $kriteria;
        }

        // 2. Fallback pencarian nama
        $kriteria = KriteriaPenilaian::where('nama', 'like', '%kedisiplinan%')
            ->orWhere('nama', 'like', '%presensi%')
            ->first();
        if ($kriteria) {
            return $kriteria;
        }

        // 3. Fallback kriteria pertama jika ada
        return KriteriaPenilaian::orderBy('id', 'asc')->first();
    }

    /**
     * Menghitung skor objektif presensi untuk seorang peserta magang (dapat dibatasi per penempatan divisi).
     */
    public function calculateScore(Magang $magang, ?PenempatanMagang $penempatan = null): array
    {
        if ($penempatan && $penempatan->tanggal_mulai && $penempatan->tanggal_selesai) {
            $startDate = Carbon::parse($penempatan->tanggal_mulai);
            $endDate = Carbon::parse($penempatan->tanggal_selesai);
        } else {
            $startDate = $magang->tanggal_mulai ? Carbon::parse($magang->tanggal_mulai) : Carbon::parse($magang->created_at);
            $endDate = $magang->tanggal_selesai ? Carbon::parse($magang->tanggal_selesai) : Carbon::today();
        }

        // Pastikan urutan tanggal valid
        if ($startDate->gt($endDate)) {
            $temp = $startDate->copy();
            $startDate = $endDate->copy();
            $endDate = $temp;
        }

        // Ambil semua data presensi peserta dalam rentang waktu magang
        $presensiRecords = Presensi::where('pengguna_id', $magang->pengguna_id)
            ->whereDate('tanggal', '>=', $startDate->toDateString())
            ->whereDate('tanggal', '<=', $endDate->toDateString())
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

        // Ambil data pengajuan izin/sakit yang telah disetujui
        $approvedIzinList = PengajuanIzin::where('pengguna_id', $magang->pengguna_id)
            ->where('status_approval', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $endDate->toDateString())
            ->whereDate('tanggal_selesai', '>=', $startDate->toDateString())
            ->get();

        // Ambil data pengajuan tugas luar yang telah disetujui
        $approvedTugasLuarList = PengajuanTugasLuar::where('pengguna_id', $magang->pengguna_id)
            ->where('status_verifikasi', 'disetujui')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNotNull('tanggal_mulai')
                            ->whereNotNull('tanggal_selesai')
                            ->where('tanggal_mulai', '<=', $endDate->toDateString())
                            ->where('tanggal_selesai', '>=', $startDate->toDateString());
                    });
            })
            ->get();

        $targetHari = 0;
        $targetMenit = 0;
        $totalMenitRealisasi = 0;

        $menitHadirNormal = 0;
        $menitIzinResmi = 0;
        $menitLupaCheckout = 0;
        $menitTerlambatPotong = 0;

        $totalHariHadir = 0;
        $totalHariIzin = 0;
        $totalHariLupaCheckout = 0;
        $totalHariTerlambat = 0;
        $totalHariLiburNasional = 0;
        $totalHariAlpa = 0;

        $rincianHarian = [];

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            $dayOfWeek = $current->dayOfWeek; // 0 = Minggu, 6 = Sabtu

            // 1. Cek Hari Libur Rutin: Sabtu & Minggu (bukan hari kerja)
            if ($current->isWeekend()) {
                $current->addDay();

                continue;
            }

            // 2. Cek Tanggal Merah / Hari Libur Nasional (tidak memotong menit peserta)
            if (HariLibur::isTanggalMerah($current)) {
                $totalHariLiburNasional++;
                $ketLibur = HariLibur::getKeteranganLibur($current) ?? 'Hari Libur Nasional';

                $rincianHarian[] = [
                    'tanggal' => $dateStr,
                    'hari' => $current->translatedFormat('l'),
                    'status' => 'libur',
                    'jam_masuk' => '-',
                    'jam_keluar' => '-',
                    'menit' => 0,
                    'is_hitung_target' => false,
                    'keterangan' => 'Tanggal Merah: '.$ketLibur,
                ];

                $current->addDay();

                continue;
            }

            // Ini adalah hari kerja resmi (Senin - Jumat di luar tanggal merah)
            $targetHari++;
            $targetMenit += self::MENIT_PER_HARI;

            // Cek apakah tanggal ini dicover oleh permohonan izin/sakit resmi yang disetujui
            $isApprovedIzin = $approvedIzinList->contains(function ($izin) use ($current) {
                return $current->betweenIncluded(
                    Carbon::parse($izin->tanggal_mulai),
                    Carbon::parse($izin->tanggal_selesai)
                );
            });

            $presensi = $presensiRecords->get($dateStr);

            // KASUS A: Izin / Sakit Resmi Disetujui Pembimbing -> DIHITUNG HADIR PENUH (480 menit)
            if ($isApprovedIzin || ($presensi && in_array($presensi->status, ['izin', 'sakit', 'cuti']))) {
                $jenisIzin = $presensi ? ucfirst($presensi->status) : 'Izin Resmi Disetujui';
                $menit = self::MENIT_PER_HARI;

                $totalMenitRealisasi += $menit;
                $menitIzinResmi += $menit;
                $totalHariIzin++;

                $rincianHarian[] = [
                    'tanggal' => $dateStr,
                    'hari' => $current->translatedFormat('l'),
                    'status' => 'izin_resmi',
                    'jam_masuk' => '-',
                    'jam_keluar' => '-',
                    'menit' => $menit,
                    'is_hitung_target' => true,
                    'keterangan' => $jenisIzin.' (Disetujui Pembimbing - Hadir Penuh 480 Menit)',
                ];

                $current->addDay();

                continue;
            }

            // KASUS A2: Tugas Luar (TL) Disetujui Pembimbing -> DIHITUNG HADIR PENUH (480 menit) KECUALI TELAT MASUK
            $tugasLuar = $approvedTugasLuarList->first(function ($item) use ($current) {
                if ($item->tanggal_mulai && $item->tanggal_selesai) {
                    return $current->betweenIncluded(
                        Carbon::parse($item->tanggal_mulai)->startOfDay(),
                        Carbon::parse($item->tanggal_selesai)->startOfDay()
                    );
                }

                return Carbon::parse($item->tanggal)->toDateString() === $current->toDateString();
            });

            // Tugas Luar dihitung hadir penuh jika telah diverifikasi dan disetujui oleh pembimbing
            $isTugasLuar = (bool) $tugasLuar
                || ($presensi && ($presensi->pengajuanTugasLuar?->status_verifikasi === 'disetujui'
                    || (! $presensi->pengajuanTugasLuar && ($presensi->mode_kerja === 'tugas_luar' || stripos((string) $presensi->keterangan, 'tugas luar') !== false))));

            if ($isTugasLuar) {
                $jamMasukRaw = $presensi?->jam_masuk
                    ?? $presensi?->jam_masuk_efektif
                    ?? $tugasLuar?->waktu_mulai
                    ?? ($tugasLuar?->created_at ? $tugasLuar->created_at->format('H:i:s') : null)
                    ?? self::JAM_MASUK_STANDAR;

                $jamMasukBatas = Carbon::createFromTimeString(self::JAM_MASUK_STANDAR);
                $jamMasuk = Carbon::createFromTimeString($jamMasukRaw);

                // Tugas Luar dihitung full 8 jam (480 menit) kecuali jika telat masuk
                $menitTerlambat = 0;
                if ($jamMasuk->gt($jamMasukBatas)) {
                    $menitTerlambat = (int) abs($jamMasuk->diffInMinutes($jamMasukBatas));
                    $menitTerlambatPotong += $menitTerlambat;
                    $totalHariTerlambat++;
                    $menit = max(0, self::MENIT_PER_HARI - $menitTerlambat);
                } else {
                    $menit = self::MENIT_PER_HARI;
                }

                $totalMenitRealisasi += $menit;
                $menitHadirNormal += $menit;
                $totalHariHadir++;

                $jamMasukFmt = substr($jamMasukRaw, 0, 5);
                $jamKeluarRaw = $presensi?->jam_keluar
                    ?? $presensi?->jam_keluar_efektif
                    ?? $tugasLuar?->waktu_selesai
                    ?? self::JAM_KELUAR_STANDAR;
                $jamKeluarFmt = substr($jamKeluarRaw, 0, 5);

                $tujuanTL = $tugasLuar?->tujuan ?? 'Tugas Luar';
                $ketTL = 'Hadir — Tugas Luar ('.$tujuanTL.' - Disetujui Pembimbing)';
                if ($menitTerlambat > 0) {
                    $ketTL .= ' [Terlambat '.$menitTerlambat.' Menit]';
                }

                $rincianHarian[] = [
                    'tanggal' => $dateStr,
                    'hari' => $current->translatedFormat('l'),
                    'status' => 'hadir',
                    'jam_masuk' => $jamMasukFmt,
                    'jam_keluar' => $jamKeluarFmt,
                    'menit' => $menit,
                    'is_hitung_target' => true,
                    'keterangan' => $ketTL,
                ];

                $current->addDay();

                continue;
            }

            // KASUS B: Ada Presensi Masuk
            if ($presensi && $presensi->jam_masuk) {
                $jamMasuk = Carbon::createFromTimeString($presensi->jam_masuk);
                $jamMasukBatas = Carbon::createFromTimeString(self::JAM_MASUK_STANDAR);
                $jamKeluarBatas = Carbon::createFromTimeString(self::JAM_KELUAR_STANDAR);

                // Jam masuk efektif: jika datang sebelum 08:00, dihitung mulai 08:00
                $jamMasukEfektif = $jamMasuk->lt($jamMasukBatas) ? $jamMasukBatas->copy() : $jamMasuk->copy();

                // Hitung keterlambatan (jika datang lewat 08:00)
                $menitTerlambat = 0;
                if ($jamMasuk->gt($jamMasukBatas)) {
                    $menitTerlambat = (int) abs($jamMasuk->diffInMinutes($jamMasukBatas));
                    $menitTerlambatPotong += $menitTerlambat;
                    $totalHariTerlambat++;
                }

                // SUBKASUS B1: Ada Presensi Keluar (Lengkap)
                if ($presensi->jam_keluar) {
                    $jamKeluar = Carbon::createFromTimeString($presensi->jam_keluar);

                    // Jam keluar efektif: jika pulang lewat 16:00, dibatasi maksimal 16:00 (tidak bertambah)
                    $jamKeluarEfektif = $jamKeluar->gt($jamKeluarBatas) ? $jamKeluarBatas->copy() : $jamKeluar->copy();

                    if ($jamKeluarEfektif->gt($jamMasukEfektif)) {
                        $menitKerja = (int) abs($jamMasukEfektif->diffInMinutes($jamKeluarEfektif));
                    } else {
                        $menitKerja = 0;
                    }

                    // Batasi maksimal 480 menit
                    $menitKerja = min(self::MENIT_PER_HARI, $menitKerja);

                    $totalMenitRealisasi += $menitKerja;
                    $menitHadirNormal += $menitKerja;
                    $totalHariHadir++;

                    $ketHadir = 'Hadir Lengkap';
                    if ($menitTerlambat > 0) {
                        $ketHadir .= ' (Terlambat '.$menitTerlambat.' Menit)';
                    }
                    if ($jamKeluar->lt($jamKeluarBatas)) {
                        $pulangCepat = (int) abs($jamKeluar->diffInMinutes($jamKeluarBatas));
                        $ketHadir .= ' (Pulang Lebih Awal '.$pulangCepat.' Menit)';
                    }

                    $rincianHarian[] = [
                        'tanggal' => $dateStr,
                        'hari' => $current->translatedFormat('l'),
                        'status' => 'hadir',
                        'jam_masuk' => substr($presensi->jam_masuk, 0, 5),
                        'jam_keluar' => substr($presensi->jam_keluar, 0, 5),
                        'menit' => $menitKerja,
                        'is_hitung_target' => true,
                        'keterangan' => $ketHadir,
                    ];
                } else {
                    // SUBKASUS B2: Lupa Presensi Keluar -> DIPOTONG 50%
                    // Potensi menit dari jam masuk efektif s.d. 16:00
                    $potensiMenit = $jamKeluarBatas->gt($jamMasukEfektif)
                        ? (int) abs($jamMasukEfektif->diffInMinutes($jamKeluarBatas))
                        : 0;

                    $menitKerja = (int) round($potensiMenit * 0.5);

                    $totalMenitRealisasi += $menitKerja;
                    $menitLupaCheckout += $menitKerja;
                    $totalHariLupaCheckout++;

                    $ketLupa = 'Lupa Presensi Keluar (Dipotong 50% = '.$menitKerja.' Menit)';
                    if ($menitTerlambat > 0) {
                        $ketLupa .= ' [Terlambat '.$menitTerlambat.' Menit]';
                    }

                    $rincianHarian[] = [
                        'tanggal' => $dateStr,
                        'hari' => $current->translatedFormat('l'),
                        'status' => 'lupa_checkout',
                        'jam_masuk' => substr($presensi->jam_masuk, 0, 5),
                        'jam_keluar' => '-',
                        'menit' => $menitKerja,
                        'is_hitung_target' => true,
                        'keterangan' => $ketLupa,
                    ];
                }

                $current->addDay();

                continue;
            }

            // KASUS C: Tidak Hadir / Alpa (0 menit)
            $totalHariAlpa++;
            $rincianHarian[] = [
                'tanggal' => $dateStr,
                'hari' => $current->translatedFormat('l'),
                'status' => 'alpa',
                'jam_masuk' => '-',
                'jam_keluar' => '-',
                'menit' => 0,
                'is_hitung_target' => true,
                'keterangan' => 'Tidak Hadir / Alpa (0 Menit)',
            ];

            $current->addDay();
        }

        // Hindari pembagian dengan nol jika tidak ada hari kerja
        if ($targetMenit <= 0) {
            $targetHari = 1;
            $targetMenit = self::MENIT_PER_HARI;
        }

        // Kalkulasi Skor Presensi Objektif (Skala 0 - 100)
        $skorFloat = ($totalMenitRealisasi / $targetMenit) * 100;
        $skorPresensi = min(100.0, round($skorFloat, 2));
        $nilaiAngka = (int) round($skorPresensi);

        // Kategori Predikat Sesuai Aturan User:
        // >90 = sangat baik, 80-89 = baik, 70-79 = cukup baik, 60-69 = kurang baik, <60 = tidak baik
        $predikat = 'Tidak Baik';
        $predikatKeterangan = 'Kedisiplinan kehadiran di bawah standar minimal';
        $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';

        if ($nilaiAngka >= 90) {
            $predikat = 'Sangat Baik';
            $predikatKeterangan = 'Tingkat kedisiplinan dan jam kerja terpenuhi dengan sangat baik (≥90%)';
            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        } elseif ($nilaiAngka >= 80) {
            $predikat = 'Baik';
            $predikatKeterangan = 'Tingkat kedisiplinan terpenuhi dengan baik (80% - 89%)';
            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
        } elseif ($nilaiAngka >= 70) {
            $predikat = 'Cukup Baik';
            $predikatKeterangan = 'Tingkat kedisiplinan cukup memadai (70% - 79%)';
            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
        } elseif ($nilaiAngka >= 60) {
            $predikat = 'Kurang Baik';
            $predikatKeterangan = 'Tingkat kedisiplinan kurang memuaskan (60% - 69%)';
            $badgeClass = 'bg-orange-50 text-orange-700 border-orange-200';
        }

        return [
            'magang' => $magang,
            'kriteria_presensi' => $this->getKriteriaPresensi(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'target_hari' => $targetHari,
            'target_menit' => $targetMenit,
            'total_menit_realisasi' => $totalMenitRealisasi,
            'menit_hadir_normal' => $menitHadirNormal,
            'menit_izin_resmi' => $menitIzinResmi,
            'menit_lupa_checkout' => $menitLupaCheckout,
            'menit_terlambat_potong' => $menitTerlambatPotong,
            'total_hari_hadir' => $totalHariHadir,
            'total_hari_izin' => $totalHariIzin,
            'total_hari_lupa_checkout' => $totalHariLupaCheckout,
            'total_hari_terlambat' => $totalHariTerlambat,
            'total_hari_libur_nasional' => $totalHariLiburNasional,
            'total_hari_alpa' => $totalHariAlpa,
            'skor_presensi' => $skorPresensi,
            'nilai_angka' => $nilaiAngka,
            'predikat' => $predikat,
            'predikat_keterangan' => $predikatKeterangan,
            'badge_class' => $badgeClass,
            'rincian_harian' => $rincianHarian,
        ];
    }
}
