<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembimbing extends Model
{
    protected $table = 'pembimbing';

    protected $fillable = [
        'pengguna_id',
        'nip',
        'nama_lengkap',
        'jabatan',
        'no_hp',
    ];

    // Relasi ke akun login pengguna
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    // Relasi ke anak magang yang dibimbing
    public function magang()
    {
        return $this->hasMany(Magang::class, 'pembimbing_id');
    }

    // Relasi ke penilaian magang
    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'pembimbing_id');
    }
}
