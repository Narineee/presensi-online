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
        'diaktifkan_oleh_admin',
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

    /**
     * URL foto profil peserta magang.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->foto)) {
            return null;
        }

        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return $this->foto;
        }

        return asset('storage/'.$this->foto);
    }

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'diaktifkan_oleh_admin' => 'boolean',
        'face_descriptors' => 'array',
        'face_registered_at' => 'datetime',
    ];

    protected $hidden = ['face_descriptors'];

    /**
     * Menentukan apakah periode magang peserta telah berakhir.
     * Periode berakhir jika tanggal selesai telah lewat sebelum hari ini.
     */
    public function isPeriodeBerakhir(?Carbon $date = null): bool
    {
        if (! $this->tanggal_selesai) {
            return false;
        }

        $date = $date ? $date->copy()->startOfDay() : Carbon::today();

        return Carbon::parse($this->tanggal_selesai)->startOfDay()->isBefore($date);
    }

    /**
     * Menentukan apakah akun peserta berhak login / aktif.
     */
    public function dapatLogin(): bool
    {
        return (bool) ($this->pengguna && $this->pengguna->is_active);
    }

    /**
     * Sinkronisasi status magang jika periode telah berakhir.
     * Otomatis menandai status peserta menjadi 'selesai' tanpa mematikan akun login.
     */
    public function syncStatusKeaktifan(): bool
    {
        if ($this->isPeriodeBerakhir() && $this->status === 'aktif') {
            $this->update(['status' => 'selesai']);

            return true;
        }

        return false;
    }

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

    // Relasi ke penilaian akhir masa magang (tunggal / latest untuk kemudahan query & backward-compat)
    public function penilaian()
    {
        return $this->hasOne(Penilaian::class, 'magang_id')->latestOfMany();
    }

    /**
     * Relasi ke seluruh riwayat penilaian magang oleh berbagai pembimbing.
     */
    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'magang_id')->orderBy('created_at', 'asc');
    }

    /**
     * Menghitung nilai akhir magang:
     * - Jika tidak ada penilaian: return null
     * - Jika 1 pembimbing (tidak rotasi): return penilaian tunggal tersebut
     * - Jika rotasi / multi-pembimbing: return DTO akumulasi (rata-rata nilai & per kriteria)
     */
    public function getPenilaianAkumulasi(): ?object
    {
        $evaluations = $this->relationLoaded('penilaians')
            ? $this->penilaians
            : $this->penilaians()->with(['detail.kriteria', 'pembimbing', 'penempatanMagang.divisi'])->get();

        if ($evaluations->isEmpty()) {
            return null;
        }

        if ($evaluations->count() === 1) {
            $single = $evaluations->first();
            $single->is_akumulasi = false;
            $single->jumlah_pembimbing = 1;
            $single->evaluasi_count = 1;
            $single->evaluasi_list = $evaluations;
            $single->nilai_akhir = $single->total_nilai;

            return $single;
        }

        // Multi-pembimbing: hitung akumulasi rata-rata nilai akhir
        $avgTotal = (int) round($evaluations->avg('total_nilai'));
        $predikat = Penilaian::hitungPredikat($avgTotal);
        $keterangan = Penilaian::hitungKeteranganPredikat($predikat);
        $badge = Penilaian::hitungBadgeClass($predikat);

        // Rekapitulasi rata-rata per kriteria
        $kriteriaGroups = [];
        foreach ($evaluations as $ev) {
            foreach ($ev->detail as $dt) {
                if (! $dt->kriteria) {
                    continue;
                }
                $kId = $dt->kriteria_id;
                if (! isset($kriteriaGroups[$kId])) {
                    $kriteriaGroups[$kId] = [
                        'kriteria' => $dt->kriteria,
                        'kriteria_id' => $kId,
                        'nilai_sum' => 0,
                        'count' => 0,
                    ];
                }
                $kriteriaGroups[$kId]['nilai_sum'] += (float) $dt->nilai;
                $kriteriaGroups[$kId]['count']++;
            }
        }

        $detailAkumulasi = collect();
        foreach ($kriteriaGroups as $kg) {
            $rataRata = $kg['count'] > 0 ? round($kg['nilai_sum'] / $kg['count']) : 0;
            $bobot = $kg['kriteria']->bobot ?? 0;
            $terbobot = round(($rataRata * $bobot) / 100, 2);

            $detailAkumulasi->push((object) [
                'id' => $kg['kriteria_id'],
                'kriteria_id' => $kg['kriteria_id'],
                'kriteria' => $kg['kriteria'],
                'nilai' => (int) $rataRata,
                'nilai_desimal' => round($kg['count'] > 0 ? $kg['nilai_sum'] / $kg['count'] : 0, 1),
                'terbobot' => $terbobot,
            ]);
        }

        $dto = new \stdClass;
        $dto->id = $evaluations->last()->id ?? null;
        $dto->magang_id = $this->id;
        $dto->magang = $this;
        $dto->pembimbing_id = $this->pembimbing_id;
        $dto->pembimbing = $this->pembimbing;
        $dto->total_nilai = $avgTotal;
        $dto->nilai_akhir = $avgTotal;
        $dto->predikat = $predikat;
        $dto->keterangan_predikat = $keterangan;
        $dto->badge_class = $badge;
        $dto->is_akumulasi = true;
        $dto->jumlah_pembimbing = $evaluations->count();
        $dto->evaluasi_count = $evaluations->count();
        $dto->evaluasi_list = $evaluations;
        $dto->detail = $detailAkumulasi;
        $dto->detail_rata_rata = $detailAkumulasi;
        $dto->created_at = $evaluations->last()->created_at ?? now();

        $hasilKaryaList = collect();
        foreach ($evaluations as $ev) {
            if (! empty($ev->hasil_karya) && is_array($ev->hasil_karya)) {
                foreach ($ev->hasil_karya as $hk) {
                    if (is_string($hk) && trim($hk) !== '') {
                        $hasilKaryaList->push(trim($hk));
                    }
                }
            }
        }
        $dto->hasil_karya = $hasilKaryaList->unique()->values()->all();

        return $dto;
    }

    /**
     * Accessor untuk penilaian akumulasi
     */
    public function getPenilaianAkumulasiAttribute(): ?object
    {
        return $this->getPenilaianAkumulasi();
    }

    /**
     * Mengambil daftar seluruh pembimbing yang terlibat dalam pembinaan magang ini
     */
    public function getSemuaPembimbing()
    {
        $pembimbingIds = collect([$this->pembimbing_id]);

        if ($this->relationLoaded('penempatanMagang')) {
            $pembimbingIds = $pembimbingIds->merge($this->penempatanMagang->pluck('pembimbing_id'));
        } else {
            $pembimbingIds = $pembimbingIds->merge($this->penempatanMagang()->pluck('pembimbing_id'));
        }

        $pembimbingIds = $pembimbingIds->filter()->unique();

        return Pembimbing::whereIn('id', $pembimbingIds)->get();
    }

    // Relasi ke pekerjaan yang diberikan pembimbing (legacy)
    public function pekerjaan()
    {
        return $this->hasMany(Pekerjaan::class, 'magang_id');
    }

    /**
     * Relasi ke rencana kerja mandiri peserta magang.
     */
    public function rencanaKerja(): HasMany
    {
        return $this->hasMany(RencanaKerja::class, 'magang_id');
    }

    public function pengajuanTugasLuar(): HasMany
    {
        return $this->hasMany(PengajuanTugasLuar::class, 'magang_id');
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
     * Mengambil model Pembimbing yang berlaku pada tanggal tertentu:
     * - Pertama, dicari dari penempatan unit (penempatan_magang) pada tanggal tersebut.
     * - Jika ada pembimbing khusus di unit tersebut, gunakan pembimbing tersebut.
     * - Fallback ke pembimbing pokok peserta magang.
     */
    public function getPembimbingAt($date): ?Pembimbing
    {
        $penempatan = $this->getPenempatanAt($date);

        if ($penempatan && $penempatan->pembimbing) {
            return $penempatan->pembimbing;
        }

        return $this->pembimbing;
    }

    /**
     * Memeriksa apakah peserta magang ini sedang dibina oleh pembimbing tertentu pada tanggal tertentu.
     */
    public function isDibimbingOleh($pembimbingId, $date = null): bool
    {
        $pembimbingAktif = $this->getPembimbingAt($date);

        return $pembimbingAktif && (int) $pembimbingAktif->id === (int) $pembimbingId;
    }

    /**
     * Accessor untuk mendapatkan penempatan divisi yang sedang aktif hari ini.
     */
    public function getPenempatanAktifAttribute(): ?PenempatanMagang
    {
        return $this->getPenempatanAt(Carbon::today());
    }
}
