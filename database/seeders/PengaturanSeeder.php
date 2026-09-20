<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pengaturan::updateOrCreate(
            ['id' => 1],
            [
                'nama_instansi' => 'Dinas Komunikasi dan Informatika',
                'nama_aplikasi' => 'Sistem Presensi & Aktivitas Digital',
                'nama_kepala_dinas' => 'Dr. H. Asep Saepudin, M.Si',
                'nip_kepala_dinas' => '19720315 199803 1 004',
                'jabatan_kepala_dinas' => 'Kepala Dinas Komunikasi dan Informatika',
                'pangkat_golongan' => 'Pembina Utama Muda (IV/c)',
                'kota_surat' => 'Banjarbaru',
                'alamat_instansi' => 'Jl. Panglima Batur No. 1, Kota Banjarbaru, Kalimantan Selatan 70711',
                'telepon' => '(0511) 4772555',
                'email' => 'diskominfo@banjarbarukota.go.id',
                'website' => 'https://diskominfo.banjarbarukota.go.id',
            ]
        );
    }
}
