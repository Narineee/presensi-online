<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aktivitas extends Model
{
    protected $table = 'aktivitas';

    protected $fillable = [
        'pengguna_id',
        'pekerjaan_id',
        'judul',
        'tanggal',
        'isi',
        'progress',
        'status',
        'validated_by',
        'validated_at',
        'catatan_validasi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'validated_at' => 'datetime',
        'progress' => 'integer',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class);
    }

    public function pekerjaan()
    {
        return $this->belongsTo(Pekerjaan::class, 'pekerjaan_id');
    }

    public function validator()
    {
        return $this->belongsTo(
            Pengguna::class,
            'validated_by'
        );
    }

    /**
     * Mendapatkan nama lengkap pembuat aktivitas (Magang atau CS)
     */
    public function getNamaLengkapAttribute(): string
    {
        if ($this->pengguna && $this->pengguna->magang) {
            return $this->pengguna->magang->nama_lengkap;
        }

        if ($this->pengguna && $this->pengguna->cs) {
            return $this->pengguna->cs->nama_lengkap;
        }

        return $this->pengguna->username ?? '-';
    }

    /**
     * Mendapatkan nama lengkap pembimbing yang memvalidasi
     */
    public function getNamaValidatorAttribute(): string
    {
        if ($this->validator && $this->validator->pembimbing) {
            return $this->validator->pembimbing->nama_lengkap;
        }

        return $this->validator->username ?? '-';
    }

    /**
     * Cek apakah aktivitas masih dapat diubah / dihapus oleh pembuatnya
     */
    public function canBeEdited(): bool
    {
        return $this->status !== 'approve';
    }
}
