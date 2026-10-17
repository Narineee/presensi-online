<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Aktivitas extends Model
{
    protected $table = 'aktivitas';

    protected $fillable = [
        'pengguna_id',
        'pekerjaan_id',
        'judul',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
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

    /**
     * Relasi ke seluruh rincian progres rencana kerja dalam aktivitas ini.
     */
    public function details()
    {
        return $this->hasMany(AktivitasDetail::class, 'aktivitas_id');
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

    /**
     * Mengelompokkan koleksi aktivitas berdasarkan Rencana Kerja (sesuai PRD alur-rekap.md).
     * Setiap Rencana Kerja memiliki tabel terpisah berisi daftar progres tanggal pengerjaannya.
     *
     * @param  Collection  $aktivitasCollection
     * @return array<int, array{id: ?int, judul: string, deskripsi: ?string, items: array}>
     */
    public static function groupByRencana($aktivitasCollection): array
    {
        $grouped = [];

        foreach ($aktivitasCollection as $item) {
            if ($item->details && $item->details->isNotEmpty()) {
                foreach ($item->details as $d) {
                    $rkId = $d->rencana_kerja_id ? 'rk_'.$d->rencana_kerja_id : 'snap_'.md5((string) $d->rencana_kerja_snapshot);
                    $judul = $d->judul_rencana;
                    $deskripsi = $d->rencanaKerja?->deskripsi;

                    if (! isset($grouped[$rkId])) {
                        $grouped[$rkId] = [
                            'id' => $d->rencana_kerja_id,
                            'judul' => $judul,
                            'deskripsi' => $deskripsi,
                            'items' => [],
                        ];
                    }

                    $grouped[$rkId]['items'][] = [
                        'tanggal' => $item->tanggal,
                        'progres' => $d->progres,
                        'status' => $item->status,
                        'status_label' => match ($item->status) {
                            'approve', 'disetujui' => 'Disetujui',
                            'pending', 'menunggu' => 'Menunggu',
                            default => 'Revisi',
                        },
                        'catatan' => $item->catatan_validasi,
                        'validator' => $item->nama_validator,
                    ];
                }
            } else {
                $rkId = 'umum';
                if (! isset($grouped[$rkId])) {
                    $grouped[$rkId] = [
                        'id' => null,
                        'judul' => $item->pekerjaan?->judul ?? ($item->judul ?? 'Aktivitas Harian'),
                        'deskripsi' => $item->pekerjaan?->deskripsi ?? 'Catatan aktivitas harian mandiri',
                        'items' => [],
                    ];
                }

                $grouped[$rkId]['items'][] = [
                    'tanggal' => $item->tanggal,
                    'progres' => $item->isi,
                    'status' => $item->status,
                    'status_label' => match ($item->status) {
                        'approve', 'disetujui' => 'Disetujui',
                        'pending', 'menunggu' => 'Menunggu',
                        default => 'Revisi',
                    },
                    'catatan' => $item->catatan_validasi,
                    'validator' => $item->nama_validator,
                ];
            }
        }

        // Urutkan catatan di setiap tabel Rencana Kerja secara kronologis (tanggal terlama ke terbaru)
        foreach ($grouped as &$rkGroup) {
            usort($rkGroup['items'], function ($a, $b) {
                return strcmp((string) $a['tanggal'], (string) $b['tanggal']);
            });
        }

        return array_values($grouped);
    }
}
