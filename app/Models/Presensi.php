<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $fillable = [
        'pengguna_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status',
        'mode_kerja',
        'foto_masuk',
        'foto_keluar',
        'lokasi_masuk',
        'lokasi_keluar',
        'keterangan',
        'face_distance_masuk',
        'face_distance_keluar',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class);
    }

    /**
     * Mendapatkan nama lengkap pengguna (baik Magang maupun CS)
     */
    public function getNamaLengkapAttribute(): string
    {
        if ($this->pengguna && $this->pengguna->magang) {
            return $this->pengguna->magang->nama_lengkap;
        }

        return $this->pengguna->username ?? '-';
    }

    /**
     * URL Foto Masuk
     */
    public function getFotoMasukUrlAttribute(): ?string
    {
        return $this->foto_masuk ? asset('storage/'.$this->foto_masuk) : null;
    }

    public function getFotoKeluarUrlAttribute(): ?string
    {
        return $this->foto_keluar ? asset('storage/'.$this->foto_keluar) : null;
    }

    public function pengajuanTugasLuar()
    {
        return $this->hasOne(PengajuanTugasLuar::class, 'presensi_id');
    }

    public function pengajuanTugasLuars()
    {
        return $this->hasMany(PengajuanTugasLuar::class, 'presensi_id');
    }

    /**
     * Memeriksa apakah presensi ini merupakan Tugas Luar (TL)
     */
    public function getIsTugasLuarAttribute(): bool
    {
        if ($this->pengajuanTugasLuar && in_array($this->pengajuanTugasLuar->status_verifikasi, ['disetujui', 'menunggu'])) {
            return true;
        }

        if ($this->mode_kerja === 'tugas_luar') {
            return true;
        }

        if (stripos((string) $this->keterangan, 'tugas luar') !== false || stripos((string) $this->keterangan, 'dinas luar') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Mendapatkan catatan/alasan kepulangan atau penugasan (TL, sakit, izin).
     */
    public function getCatatanPulangAttribute(): ?string
    {
        if (in_array($this->status, ['sakit', 'izin', 'cuti'])) {
            return null;
        }

        if ($this->is_tugas_luar) {
            return 'TL';
        }

        $rawKet = (string) $this->keterangan;
        $alasan = null;

        if (preg_match('/\|\s*Pulang:\s*([^|]+)/i', $rawKet, $m)) {
            $alasan = trim($m[1]);
        } elseif (preg_match('/Pulang:\s*([^|]+)/i', $rawKet, $m)) {
            $alasan = trim($m[1]);
        } elseif (preg_match('/\b(Sakit|Izin)(?:\s*\([^)]+\))?/i', $rawKet, $m)) {
            $alasan = trim($m[0]);
        }

        if ($alasan) {
            $cleaned = trim((string) preg_replace('/^Pulang:\s*/i', '', $alasan));
            if (stripos($cleaned, 'sakit') !== false) {
                return 'sakit';
            }
            if (stripos($cleaned, 'izin') !== false) {
                return 'izin';
            }

            return strtolower($cleaned);
        }

        return null;
    }

    /**
     * Mendapatkan jam keluar efektif.
     */
    public function getJamKeluarEfektifAttribute(): ?string
    {
        if ($this->jam_keluar) {
            return $this->jam_keluar;
        }

        if ($this->is_tugas_luar) {
            $tl = $this->pengajuanTugasLuar;
            if ($tl) {
                // Jika peserta sudah ada jam masuk dan pengajuan TL dimulai di tengah hari (waktu_mulai > jam_masuk),
                // jam keluar kantornya adalah waktu mulai TL
                if ($this->jam_masuk && $tl->waktu_mulai && $tl->waktu_mulai > $this->jam_masuk) {
                    return $tl->waktu_mulai;
                }

                return $tl->waktu_selesai ?? '16:00:00';
            }

            return '16:00:00';
        }

        return null;
    }

    /**
     * Format jam keluar siap tampil dengan label (contoh: '09:30 (TL)', '09:30 (sakit)', '09:30 (izin)').
     */
    public function formatJamKeluar(string $separator = ':', bool $withLabel = true): string
    {
        $jamKeluar = $this->jam_keluar_efektif;
        if (! $jamKeluar) {
            return '-';
        }

        $timeStr = substr($jamKeluar, 0, 5);
        if ($separator !== ':') {
            $timeStr = str_replace(':', $separator, $timeStr);
        }

        if ($withLabel) {
            $label = $this->catatan_pulang;
            if ($label) {
                return "{$timeStr} ({$label})";
            }
        }

        return $timeStr;
    }

    /**
     * Aksesor untuk jam keluar tampilan berlabel.
     */
    public function getJamKeluarTampilanAttribute(): string
    {
        return $this->formatJamKeluar(':');
    }

    /**
     * Mendapatkan jam masuk efektif (untuk Tugas Luar, otomatis waktu_mulai atau jam pengajuan/08:00:00 jika kosong).
     */
    public function getJamMasukEfektifAttribute(): ?string
    {
        if ($this->jam_masuk) {
            return $this->jam_masuk;
        }

        if ($this->is_tugas_luar) {
            return $this->pengajuanTugasLuar?->waktu_mulai
                ?? ($this->pengajuanTugasLuar?->created_at ? $this->pengajuanTugasLuar->created_at->format('H:i:s') : null)
                ?? '08:00:00';
        }

        return null;
    }

    /**
     * Mendapatkan label status kehadiran tampilan (misal: 'Hadir — Tugas Luar' atau 'Hadir')
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->is_tugas_luar) {
            return 'Hadir — Tugas Luar';
        }

        return ucfirst($this->status);
    }
}
