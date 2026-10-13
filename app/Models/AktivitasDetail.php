<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AktivitasDetail extends Model
{
    protected $table = 'aktivitas_detail';

    protected $fillable = [
        'aktivitas_id',
        'rencana_kerja_id',
        'rencana_kerja_snapshot',
        'progres',
    ];

    /**
     * Relasi ke aktivitas induk.
     */
    public function aktivitas(): BelongsTo
    {
        return $this->belongsTo(Aktivitas::class, 'aktivitas_id');
    }

    /**
     * Relasi ke rencana kerja rujukan.
     */
    public function rencanaKerja(): BelongsTo
    {
        return $this->belongsTo(RencanaKerja::class, 'rencana_kerja_id');
    }

    /**
     * Mendapatkan judul rencana kerja (snapshot historis atau nama terkini).
     */
    public function getJudulRencanaAttribute(): string
    {
        return $this->rencana_kerja_snapshot ?: ($this->rencanaKerja?->judul ?: 'Rencana Kerja');
    }
}
