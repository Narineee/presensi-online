<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    /**
     * Nama tabel pada basis data.
     */
    protected $table = 'divisi';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'parent_id',
        'nama_divisi',
        'nama_pimpinan',
        'nip_pimpinan',
        'jabatan_pimpinan',
        'latitude',
        'longitude',
        'radius_meter',
    ];

    /**
     * Tipe casting untuk atribut model.
     */
    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meter' => 'integer',
        ];
    }

    /**
     * Relasi ke bidang induk (jika divisi ini adalah subbagian / seksi).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Relasi ke seluruh subbagian / seksi di bawah bidang ini.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Mengembalikan model bidang induk (atau dirinya sendiri jika sudah merupakan tingkat bidang).
     */
    public function getBidangIndukAttribute(): self
    {
        return $this->parent ?? $this;
    }

    /**
     * Apakah unit ini merupakan tingkat bidang / sekretariat utama.
     */
    public function getIsBidangAttribute(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Nama pimpinan tingkat bidang (Kepala Bidang / Sekretaris Dinas) untuk tanda tangan.
     */
    public function getNamaKepalaBidangAttribute(): ?string
    {
        return $this->bidang_induk->nama_pimpinan;
    }

    /**
     * NIP pimpinan tingkat bidang.
     */
    public function getNipKepalaBidangAttribute(): ?string
    {
        return $this->bidang_induk->nip_pimpinan;
    }

    /**
     * Jabatan pimpinan tingkat bidang (misal: Kepala Bidang Aplikasi Informatika).
     */
    public function getJabatanKepalaBidangAttribute(): ?string
    {
        return $this->bidang_induk->jabatan_pimpinan;
    }

    /**
     * Nama divisi bidang tingkat atas (misal: Bidang Aplikasi Informatika).
     */
    public function getNamaBidangAttribute(): string
    {
        return $this->bidang_induk->nama_divisi;
    }

    /**
     * Relasi ke data anak magang yang ditempatkan pada divisi ini.
     * Sesuai PRD: magang.divisi_id > divisi.id
     */
    public function magang(): HasMany
    {
        return $this->hasMany(Magang::class, 'divisi_id');
    }

    /**
     * Relasi ke seluruh riwayat penempatan magang pada divisi ini.
     */
    public function penempatanMagang(): HasMany
    {
        return $this->hasMany(PenempatanMagang::class, 'divisi_id');
    }

    /**
     * Relasi ke seluruh pembimbing lapangan yang bertugas di unit divisi ini.
     */
    public function pembimbing(): HasMany
    {
        return $this->hasMany(Pembimbing::class, 'divisi_id');
    }

    /**
     * Scope untuk mengambil divisi tingkat bidang utama (parent_id is null).
     */
    public function scopeHanyaBidang(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope untuk mengambil divisi tingkat subbagian / seksi (parent_id is not null).
     */
    public function scopeHanyaSubbagianSeksi(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope pencarian berdasarkan nama divisi atau nama/jabatan pimpinan.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $sub) use ($term) {
            $sub->where('nama_divisi', 'like', "%{$term}%")
                ->orWhere('nama_pimpinan', 'like', "%{$term}%")
                ->orWhere('nip_pimpinan', 'like', "%{$term}%")
                ->orWhere('jabatan_pimpinan', 'like', "%{$term}%");
        });
    }
}
