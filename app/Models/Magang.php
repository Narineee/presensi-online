<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Magang extends Model
{
    protected $table = 'magang';

    protected $fillable = [
        'pengguna_id',
        'pembimbing_id',
        'divisi_id',
        'no_induk',
        'nama_lengkap',
        'jenis_kelamin',
        'jurusan',
        'instansi_pendidikan',
        'no_hp',
        'foto',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    /**
     * Label teks jenis kelamin (Laki-laki / Perempuan).
     */
    public function getJenisKelaminTeksAttribute(): ?string
    {
        return match ($this->jenis_kelamin) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => null,
        };
    }

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Relasi ke akun login pengguna
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    // Relasi ke pembimbing
    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    // Relasi ke divisi / sub-bagian (untuk tanda tangan dinamis laporan PDF)
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    // Relasi ke penilaian akhir masa magang
    public function penilaian()
    {
        return $this->hasOne(Penilaian::class, 'magang_id');
    }
}
