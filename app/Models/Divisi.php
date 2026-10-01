<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meter' => 'integer',
        ];
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
