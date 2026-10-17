<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PenempatanMagang extends Model
{
    /**
     * Nama tabel pada basis data.
     */
    protected $table = 'penempatan_magang';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'magang_id',
        'divisi_id',
        'pembimbing_id',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    /**
     * Type casting untuk atribut model.
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    /**
     * Relasi ke peserta magang terkait.
     */
    public function magang(): BelongsTo
    {
        return $this->belongsTo(Magang::class, 'magang_id');
    }

    /**
     * Relasi ke divisi penempatan.
     */
    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    /**
     * Relasi ke pembimbing pada penempatan unit ini.
     */
    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    /**
     * Relasi ke penilaian akhir unit oleh pembimbing terkait penempatan ini.
     */
    public function penilaian(): HasOne
    {
        return $this->hasOne(Penilaian::class, 'penempatan_magang_id');
    }

    /**
     * Pembimbing efektif untuk unit ini (fallback ke pembimbing utama magang jika tidak diset khusus).
     */
    public function getPembimbingEfektifAttribute(): ?Pembimbing
    {
        return $this->pembimbing ?? $this->magang?->pembimbing;
    }

    /**
     * Menghitung status penempatan berdasarkan tanggal hari ini:
     * - 'Akan Datang' : tanggal mulai belum tiba
     * - 'Berjalan'    : sedang dalam rentang penempatan
     * - 'Selesai'     : tanggal selesai telah lewat
     */
    public function getStatusAttribute(): string
    {
        $today = Carbon::today();

        if ($this->tanggal_mulai && $this->tanggal_mulai->isAfter($today)) {
            return 'Akan Datang';
        }

        if ($this->tanggal_selesai && $this->tanggal_selesai->isBefore($today)) {
            return 'Selesai';
        }

        return 'Berjalan';
    }

    /**
     * Apakah penempatan ini sedang aktif/berjalan hari ini.
     */
    public function isBerjalan(): bool
    {
        return $this->status === 'Berjalan';
    }

    /**
     * Class CSS badge warna untuk status penempatan.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Berjalan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Akan Datang' => 'bg-blue-50 text-blue-700 border-blue-200',
            'Selesai' => 'bg-slate-100 text-slate-700 border-slate-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
