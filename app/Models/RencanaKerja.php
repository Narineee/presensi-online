<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RencanaKerja extends Model
{
    protected $table = 'rencana_kerja';

    protected $fillable = [
        'magang_id',
        'pembimbing_id',
        'judul',
        'deskripsi',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (RencanaKerja $rencana) {
            if (empty($rencana->pembimbing_id) && ! empty($rencana->magang_id)) {
                $magang = $rencana->relationLoaded('magang') ? $rencana->magang : Magang::find($rencana->magang_id);
                if ($magang) {
                    $activeMentor = $magang->getPembimbingAt(now());
                    $rencana->pembimbing_id = $activeMentor?->id ?? $magang->pembimbing_id;
                }
            }
        });
    }

    /**
     * Relasi ke peserta magang pemilik rencana kerja ini.
     */
    public function magang(): BelongsTo
    {
        return $this->belongsTo(Magang::class, 'magang_id');
    }

    /**
     * Relasi ke pembimbing lapangan saat rencana kerja dibuat / unit berlaku.
     */
    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    /**
     * Relasi ke seluruh rincian progres aktivitas yang merujuk rencana kerja ini.
     */
    public function aktivitasDetails(): HasMany
    {
        return $this->hasMany(AktivitasDetail::class, 'rencana_kerja_id');
    }

    /**
     * Cek apakah rencana kerja masih aktif.
     */
    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Cek apakah rencana kerja dapat dihapus dengan aman.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->aktivitasDetails()->exists();
    }
}
