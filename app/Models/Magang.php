<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Magang extends Model
{
    protected $table = 'magang';

    protected $fillable = [
        'pengguna_id',
        'pembimbing_id',
        'divisi_id',
        'no_induk',
        'nama_lengkap',
        'jenis_kelamin',
        'jurusan',
        'instansi_pendidikan',
        'no_hp',
        'foto',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'face_descriptors',
        'face_foto',
        'face_registered_at',
    ];

    /**
     * Label teks jenis kelamin (Laki-laki / Perempuan).
     */
    public function getJenisKelaminTeksAttribute(): ?string
    {
        return match ($this->jenis_kelamin) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => null,
        };
    }

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'face_descriptors' => 'array',
        'face_registered_at' => 'datetime',
    ];

    protected $hidden = ['face_descriptors'];

    // Relasi ke akun login pengguna
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    // Relasi ke pembimbing
    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    // Relasi ke divisi / sub-bagian (untuk tanda tangan dinamis laporan PDF)
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    // Relasi ke penilaian akhir masa magang
    public function penilaian()
    {
        return $this->hasOne(Penilaian::class, 'magang_id');
    }

    // Relasi ke pekerjaan yang diberikan pembimbing
    public function pekerjaan()
    {
        return $this->hasMany(Pekerjaan::class, 'magang_id');
    }

    /**
     * Relasi ke seluruh riwayat penempatan divisi peserta magang.
     */
    public function penempatanMagang(): HasMany
    {
        return $this->hasMany(PenempatanMagang::class, 'magang_id')->orderBy('tanggal_mulai', 'asc');
    }

    /**
     * Mengambil data penempatan divisi yang aktif pada tanggal tertentu.
     */
    public function getPenempatanAt($date): ?PenempatanMagang
    {
        $targetDate = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        if ($this->relationLoaded('penempatanMagang')) {
            return $this->penempatanMagang->first(function ($p) use ($targetDate) {
                $mulai = $p->tanggal_mulai ? $p->tanggal_mulai->toDateString() : null;
                $selesai = $p->tanggal_selesai ? $p->tanggal_selesai->toDateString() : null;

                return $mulai && $selesai && $mulai <= $targetDate && $selesai >= $targetDate;
            });
        }

        return $this->penempatanMagang()
            ->with('divisi')
            ->where('tanggal_mulai', '<=', $targetDate)
            ->where('tanggal_selesai', '>=', $targetDate)
            ->first();
    }

    /**
     * Mengambil model Divisi yang berlaku pada tanggal tertentu:
     * - Pertama, dicari dari riwayat penempatan divisi yang mencakup tanggal tersebut.
     * - Jika tidak ada penempatan yang cocok, fallback ke divisi default peserta.
     */
    public function getDivisiAt($date): ?Divisi
    {
        $penempatan = $this->getPenempatanAt($date);

        if ($penempatan && $penempatan->divisi) {
            return $penempatan->divisi;
        }

        return $this->divisi;
    }

    /**
     * Accessor untuk mendapatkan penempatan divisi yang sedang aktif hari ini.
     */
    public function getPenempatanAktifAttribute(): ?PenempatanMagang
    {
        return $this->getPenempatanAt(Carbon::today());
    }
}
