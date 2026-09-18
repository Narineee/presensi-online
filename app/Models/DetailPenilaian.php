<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenilaian extends Model
{
    protected $table = 'detail_penilaian';

    protected $fillable = [
        'penilaian_id',
        'kriteria_id',
        'nilai',
    ];

    public function penilaian()
    {
        return $this->belongsTo(Penilaian::class, 'penilaian_id');
    }

    public function kriteria()
    {
        return $this->belongsTo(
            KriteriaPenilaian::class,
            'kriteria_id'
        );
    }

    /**
     * Menghitung kontribusi nilai kriteria berdasarkan bobotnya
     */
    public function getNilaiTerbobotAttribute(): float
    {
        $bobot = $this->kriteria->bobot ?? 0;

        return round(($this->nilai * $bobot) / 100, 2);
    }
}
