<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $table = 'penilaian';

    protected $fillable = [
        'magang_id',
        'pembimbing_id',
        'penempatan_magang_id',
        'total_nilai',
    ];

    public function magang()
    {
        return $this->belongsTo(Magang::class, 'magang_id');
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    public function penempatanMagang()
    {
        return $this->belongsTo(PenempatanMagang::class, 'penempatan_magang_id');
    }

    public function detail()
    {
        return $this->hasMany(DetailPenilaian::class, 'penilaian_id');
    }

    /**
     * Hitung huruf mutu / predikat nilai dari skor angka.
     */
    public static function hitungPredikat(float|int $nilai): string
    {
        if ($nilai >= 90) {
            return 'A';
        } elseif ($nilai >= 80) {
            return 'B';
        } elseif ($nilai >= 70) {
            return 'C';
        } elseif ($nilai >= 60) {
            return 'D';
        } else {
            return 'E';
        }
    }

    /**
     * Hitung keterangan predikat dari huruf predikat.
     */
    public static function hitungKeteranganPredikat(string $predikat): string
    {
        return match ($predikat) {
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup Baik',
            'D' => 'Kurang Baik',
            default => 'Tidak Baik',
        };
    }

    /**
     * Hitung kelas badge Tailwind dari huruf predikat.
     */
    public static function hitungBadgeClass(string $predikat): string
    {
        return match ($predikat) {
            'A' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'B' => 'bg-blue-50 text-blue-700 border-blue-200',
            'C' => 'bg-amber-50 text-amber-700 border-amber-200',
            'D' => 'bg-orange-50 text-orange-700 border-orange-200',
            default => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    /**
     * Mendapatkan huruf mutu / predikat nilai (A, B, C, D, E)
     */
    public function getPredikatAttribute(): string
    {
        return self::hitungPredikat((float) ($this->total_nilai ?? 0));
    }

    /**
     * Mendapatkan keterangan predikat nilai
     */
    public function getKeteranganPredikatAttribute(): string
    {
        return self::hitungKeteranganPredikat($this->predikat);
    }

    /**
     * Mendapatkan class warna badge predikat
     */
    public function getBadgeClassAttribute(): string
    {
        return self::hitungBadgeClass($this->predikat);
    }
}
