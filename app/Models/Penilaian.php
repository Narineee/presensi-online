<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $table = 'penilaian';

    protected $fillable = [
        'magang_id',
        'pembimbing_id',
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

    public function detail()
    {
        return $this->hasMany(DetailPenilaian::class, 'penilaian_id');
    }

    /**
     * Mendapatkan huruf mutu / predikat nilai (A, B, C, D, E)
     */
    public function getPredikatAttribute(): string
    {
        $nilai = $this->total_nilai ?? 0;
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
     * Mendapatkan keterangan predikat nilai
     */
    public function getKeteranganPredikatAttribute(): string
    {
        $predikat = $this->predikat;

        return match ($predikat) {
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup Baik',
            'D' => 'Kurang Baik',
            default => 'Tidak Baik',
        };
    }

    /**
     * Mendapatkan class warna badge predikat
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->predikat) {
            'A' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'B' => 'bg-blue-50 text-blue-700 border-blue-200',
            'C' => 'bg-amber-50 text-amber-700 border-amber-200',
            'D' => 'bg-orange-50 text-orange-700 border-orange-200',
            default => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }
}
