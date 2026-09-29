<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KriteriaPenilaian extends Model
{
    protected $table = 'kriteria';

    protected $fillable = [
        'nama',
        'bobot',
        'is_presensi',
    ];

    protected $casts = [
        'bobot' => 'integer',
        'is_presensi' => 'boolean',
    ];

    public function detailPenilaian()
    {
        return $this->hasMany(
            DetailPenilaian::class,
            'kriteria_id'
        );
    }
}
