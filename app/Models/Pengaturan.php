<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    /**
     * Nama tabel dalam basis data.
     */
    protected $table = 'pengaturan';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'nama_instansi',
        'nama_aplikasi',
        'nama_kepala_dinas',
        'nip_kepala_dinas',
        'jabatan_kepala_dinas',
        'pangkat_golongan',
        'kota_surat',
        'alamat_instansi',
        'telepon',
        'email',
        'website',
    ];

    /**
     * Mengambil data pengaturan sistem utama (singleton record).
     */
    public static function getPengaturan(): self
    {
        return static::firstOrCreate([], [
            'nama_instansi' => 'Dinas Komunikasi dan Informatika',
            'nama_aplikasi' => 'Sistem Presensi & Aktivitas Digital',
            'nama_kepala_dinas' => 'Dr. H. Asep Saepudin, M.Si',
            'nip_kepala_dinas' => '19720315 199803 1 004',
            'jabatan_kepala_dinas' => 'Kepala Dinas',
            'pangkat_golongan' => 'Pembina Utama Muda (IV/c)',
            'kota_surat' => 'Banjarbaru',
            'alamat_instansi' => 'Jl. Panglima Batur No. 1, Kota Banjarbaru, Kalimantan Selatan',
            'telepon' => '(0511) 4772555',
            'email' => 'diskominfo@banjarbarukota.go.id',
            'website' => 'https://diskominfo.banjarbarukota.go.id',
        ]);
    }
}
