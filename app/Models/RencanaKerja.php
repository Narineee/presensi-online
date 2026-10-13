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
        'judul',
        'deskripsi',
        'status',
    ];

    /**
     * Relasi ke peserta magang pemilik rencana kerja ini.
     */
    public function magang(): BelongsTo
    {
        return $this->belongsTo(Magang::class, 'magang_id');
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
