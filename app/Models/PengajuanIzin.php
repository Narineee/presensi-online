<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanIzin extends Model
{
    protected $table = 'pengajuan_izin';

    protected $fillable = [
        'pengguna_id',
        'jenis_izin',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'bukti_file',
        'status_approval',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'validated_at' => 'datetime',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class);
    }

    public function validator()
    {
        return $this->belongsTo(
            Pengguna::class,
            'validated_by'
        );
    }

    /**
     * Mendapatkan nama pemohon izin (Magang atau CS)
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
     * Mendapatkan nama pembimbing yang menyetujui / menolak
     */
    public function getNamaValidatorAttribute(): string
    {
        if ($this->validator && $this->validator->pembimbing) {
            return $this->validator->pembimbing->nama_lengkap;
        }

        return $this->validator->username ?? '-';
    }

    /**
     * Mendapatkan URL file bukti (surat dokter / berkas izin)
     */
    public function getBuktiFileUrlAttribute(): ?string
    {
        return $this->bukti_file ? asset('storage/'.$this->bukti_file) : null;
    }

    /**
     * Menghitung total durasi hari izin
     */
    public function getJumlahHariAttribute(): int
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
        }

        return 1;
    }

    /**
     * Cek apakah permohonan masih dalam status pending sehingga dapat diedit / dibatalkan
     */
    public function canBeEdited(): bool
    {
        return $this->status_approval === 'pending';
    }
}
