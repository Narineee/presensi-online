<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pembimbing extends Model
{
    protected $table = 'pembimbing';

    protected $fillable = [
        'pengguna_id',
        'divisi_id',
        'nip',
        'nama_lengkap',
        'jabatan',
        'no_hp',
    ];

    // Relasi ke divisi / subbagian / seksi unit kerja
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    // Relasi ke akun login pengguna
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    // Relasi ke anak magang yang dibimbing
    public function magang()
    {
        return $this->hasMany(Magang::class, 'pembimbing_id');
    }

    // Relasi ke penilaian magang
    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'pembimbing_id');
    }

    // Relasi ke seluruh pekerjaan yang dibuat pembimbing
    public function pekerjaan()
    {
        return $this->hasMany(Pekerjaan::class, 'pembimbing_id');
    }

    /**
     * Query peserta magang yang dibina oleh pembimbing ini
     * (baik sebagai pembimbing pokok maupun melalui penempatan rotasi unit).
     */
    public function getSupervisedMagangQuery()
    {
        return Magang::where(function ($q) {
            $q->where('pembimbing_id', $this->id)
                ->orWhereHas('penempatanMagang', function ($pq) {
                    $pq->where('pembimbing_id', $this->id);
                });
        });
    }

    /**
     * Membatasi query (Presensi, Aktivitas, dsb.) HANYA pada catatan tanggal
     * saat peserta magang memang berada di bawah bimbingan pembimbing ini (sesuai periode unit/penempatan).
     * Jika hari berikutnya peserta sudah pindah unit/pembimbing lain, rekaman tersebut tidak masuk ke rekap pembimbing ini.
     *
     * @param  Builder|\Illuminate\Database\Query\Builder  $query
     */
    public function applyPeriodConstraint($query, string $tanggalCol = 'tanggal', string $penggunaIdCol = 'pengguna_id')
    {
        $pId = $this->id;

        return $query->whereExists(function ($sub) use ($pId, $tanggalCol, $penggunaIdCol) {
            $sub->selectRaw(1)
                ->from('magang')
                ->whereColumn('magang.pengguna_id', $penggunaIdCol)
                ->where(function ($mq) use ($pId, $tanggalCol) {
                    // Cabang 1: Ada penempatan_magang di tanggal tersebut yang diasuh oleh pembimbing ini
                    $mq->whereExists(function ($pmQ) use ($pId, $tanggalCol) {
                        $pmQ->selectRaw(1)
                            ->from('penempatan_magang')
                            ->whereColumn('penempatan_magang.magang_id', 'magang.id')
                            ->whereColumn($tanggalCol, '>=', 'penempatan_magang.tanggal_mulai')
                            ->whereColumn($tanggalCol, '<=', 'penempatan_magang.tanggal_selesai')
                            ->where(function ($pCheck) use ($pId) {
                                $pCheck->where('penempatan_magang.pembimbing_id', $pId)
                                    ->orWhere(function ($fallback) use ($pId) {
                                        $fallback->whereNull('penempatan_magang.pembimbing_id')
                                            ->where('magang.pembimbing_id', $pId);
                                    });
                            });
                    })
                    // Cabang 2: Tidak ada penempatan khusus pada tanggal tersebut, dan pembimbing pokoknya adalah pembimbing ini
                        ->orWhere(function ($baseQ) use ($pId, $tanggalCol) {
                            $baseQ->where('magang.pembimbing_id', $pId)
                                ->whereNotExists(function ($otherPmQ) use ($pId, $tanggalCol) {
                                    $otherPmQ->selectRaw(1)
                                        ->from('penempatan_magang')
                                        ->whereColumn('penempatan_magang.magang_id', 'magang.id')
                                        ->whereColumn($tanggalCol, '>=', 'penempatan_magang.tanggal_mulai')
                                        ->whereColumn($tanggalCol, '<=', 'penempatan_magang.tanggal_selesai')
                                        ->whereNotNull('penempatan_magang.pembimbing_id')
                                        ->where('penempatan_magang.pembimbing_id', '!=', $pId);
                                });
                        });
                });
        });
    }
}
