<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KriteriaPenilaian extends Model
{
    protected $table = 'kriteria';

    protected $fillable = [
        'nama',
        'bobot',
    ];

    protected $casts = [
        'bobot' => 'integer',
    ];

    public function detailPenilaian()
    {
        return $this->hasMany(
            DetailPenilaian::class,
            'kriteria_id'
        );
    }
}
