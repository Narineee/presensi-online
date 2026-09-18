<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cs extends Model
{
    protected $table = 'cs';

    protected $fillable = [
        'pengguna_id',
        'pembimbing_id',
        'nik',
        'nama_lengkap',
        'jabatan',
        'no_hp',
        'tanggal_bergabung',
        'status',
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
    ];

    // Relasi ke akun login pengguna
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    // Relasi ke pembimbing / validator CS
    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    // Relasi ke jadwal shift kerja
    public function jadwalShift()
    {
        return $this->hasMany(JadwalShiftCs::class, 'cs_id');
    }
}
