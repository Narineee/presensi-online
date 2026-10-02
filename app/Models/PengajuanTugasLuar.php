<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanTugasLuar extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_tugas_luar';

    protected $fillable = [
        'presensi_id',
        'pengguna_id',
        'magang_id',
        'tanggal',
        'tujuan',
        'keperluan',
        'waktu_mulai',
        'waktu_selesai',
        'bukti',
        'status_verifikasi',
        'catatan_pembimbing',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'verified_at' => 'datetime',
    ];

    public function presensi()
    {
        return $this->belongsTo(Presensi::class, 'presensi_id');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function magang()
    {
        return $this->belongsTo(Magang::class, 'magang_id');
    }

    public function validator()
    {
        return $this->belongsTo(Pengguna::class, 'verified_by');
    }

    public function getNamaLengkapAttribute(): string
    {
        if ($this->magang) {
            return $this->magang->nama_lengkap;
        }

        if ($this->pengguna && $this->pengguna->magang) {
            return $this->pengguna->magang->nama_lengkap;
        }

        return $this->pengguna->username ?? '-';
    }

    public function getNamaValidatorAttribute(): string
    {
        if ($this->validator && $this->validator->pembimbing) {
            return $this->validator->pembimbing->nama_lengkap;
        }

        return $this->validator->username ?? '-';
    }

    public function getBuktiUrlAttribute(): ?string
    {
        if (! $this->bukti) {
            return null;
        }

        return asset('storage/'.$this->bukti);
    }

    public function getStatusAttribute(): string
    {
        return $this->status_verifikasi;
    }

    public function isMenunggu(): bool
    {
        return $this->status_verifikasi === 'menunggu';
    }

    public function isDisetujui(): bool
    {
        return $this->status_verifikasi === 'disetujui';
    }

    public function isDitolak(): bool
    {
        return $this->status_verifikasi === 'ditolak';
    }
}
