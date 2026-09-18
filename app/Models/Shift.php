<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 'shift';

    protected $fillable = [
        'nama',
        'jam_masuk',
        'jam_keluar',
        'toleransi_masuk',
        'is_active',
    ];

    protected $casts = [
        'toleransi_masuk' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relasi ke jadwal shift CS
    public function jadwalShift()
    {
        return $this->hasMany(JadwalShiftCs::class, 'shift_id');
    }
}
