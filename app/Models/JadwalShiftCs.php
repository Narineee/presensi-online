<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalShiftCs extends Model
{
    protected $table = 'jadwal_shift_cs';

    protected $fillable = [
        'cs_id',
        'shift_id',
        'tanggal',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // Relasi ke Customer Service (CS)
    public function cs()
    {
        return $this->belongsTo(Cs::class, 'cs_id');
    }

    // Relasi ke Shift
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
