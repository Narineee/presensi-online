<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pekerjaan extends Model
{
    protected $table = 'pekerjaan';

    protected $fillable = [
        'magang_id',
        'pembimbing_id',
        'judul',
        'deskripsi',
        'jenis',
        'progress',
        'tanggal_mulai',
        'target_selesai',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'target_selesai' => 'date',
        'progress' => 'integer',
    ];

    /**
     * Relasi ke peserta magang pemilik pekerjaan ini.
     */
    public function magang(): BelongsTo
    {
        return $this->belongsTo(Magang::class, 'magang_id');
    }

    /**
     * Relasi ke pembimbing yang memberikan pekerjaan.
     */
    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    /**
     * Relasi ke seluruh catatan aktivitas harian di bawah pekerjaan ini.
     */
    public function aktivitas(): HasMany
    {
        return $this->hasMany(Aktivitas::class, 'pekerjaan_id');
    }

    /**
     * Cek apakah pekerjaan bertipe Proyek (memiliki target dan progress).
     */
    public function isProyek(): bool
    {
        return $this->jenis === 'proyek';
    }

    /**
     * Cek apakah pekerjaan bertipe Rutin (tanpa progress).
     */
    public function isRutin(): bool
    {
        return $this->jenis === 'rutin';
    }
}
