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

    /**
     * URL Foto Keluar
     */
    public function getFotoKeluarUrlAttribute(): ?string
    {
        return $this->foto_keluar ? asset('storage/'.$this->foto_keluar) : null;
    }
}
