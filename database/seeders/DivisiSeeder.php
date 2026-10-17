<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Pembimbing;
use Illuminate\Database\Seeder;

class DivisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data 5 Bidang / Sekretariat (Unit Induk Tingkat Bidang)
        $bidangList = [
            'Sekretariat' => [
                'nama_divisi' => 'Sekretariat',
                'nama_pimpinan' => 'Sekretaris Dinas',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Sekretaris Dinas Komunikasi dan Informatika',
                'radius_meter' => 40,
                'parent_id' => null,
            ],
            'Bidang Informasi Publik & Statistik' => [
                'nama_divisi' => 'Bidang Informasi Publik & Statistik',
                'nama_pimpinan' => 'Kepala Bidang Informasi Publik & Statistik',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Bidang Informasi Publik & Statistik',
                'radius_meter' => 40,
                'parent_id' => null,
            ],
            'Bidang Komunikasi Publik' => [
                'nama_divisi' => 'Bidang Komunikasi Publik',
                'nama_pimpinan' => 'Kepala Bidang Komunikasi Publik',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Bidang Komunikasi Publik',
                'radius_meter' => 40,
                'parent_id' => null,
            ],
            'Bidang Aplikasi Informatika' => [
                'nama_divisi' => 'Bidang Aplikasi Informatika',
                'nama_pimpinan' => 'Kepala Bidang Aplikasi Informatika',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Bidang Aplikasi Informatika',
                'radius_meter' => 40,
                'parent_id' => null,
            ],
            'Bidang Persandian & Keamanan Informasi' => [
                'nama_divisi' => 'Bidang Persandian & Keamanan Informasi',
                'nama_pimpinan' => 'Kepala Bidang Persandian & Keamanan Informasi',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Bidang Persandian & Keamanan Informasi',
                'radius_meter' => 40,
                'parent_id' => null,
            ],
        ];

        // Buat atau perbarui Bidang induk terlebih dahulu
        $bidangModels = [];
        foreach ($bidangList as $key => $data) {
            $bidangModels[$key] = Divisi::updateOrCreate(
                ['nama_divisi' => $data['nama_divisi']],
                $data
            );
        }

        // 2. Data 15 Subbagian & Seksi (Unit Kerja Operasional di bawah Bidang)
        $subUnitList = [
            // 1. Di bawah Sekretariat (ada 3 subbagian)
            [
                'bidang_key' => 'Sekretariat',
                'nama_divisi' => 'Sub Bagian Umum & Kepegawaian',
                'nama_pimpinan' => 'Kepala Sub Bagian Umum & Kepegawaian',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kasubbag Umum & Kepegawaian',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Sekretariat',
                'nama_divisi' => 'Sub Bagian Keuangan & Aset',
                'nama_pimpinan' => 'Kepala Sub Bagian Keuangan & Aset',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kasubbag Keuangan & Aset',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Sekretariat',
                'nama_divisi' => 'Sub Bagian Perencanaan & Pelaporan',
                'nama_pimpinan' => 'Kepala Sub Bagian Perencanaan & Pelaporan',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kasubbag Perencanaan & Pelaporan',
                'radius_meter' => 40,
            ],

            // 2. Di bawah Bidang Informasi Publik & Statistik (ada 3 seksi)
            [
                'bidang_key' => 'Bidang Informasi Publik & Statistik',
                'nama_divisi' => 'Seksi Layanan Informasi Publik',
                'nama_pimpinan' => 'Kepala Seksi Layanan Informasi Publik',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Layanan Informasi Publik',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Bidang Informasi Publik & Statistik',
                'nama_divisi' => 'Seksi Pengelolaan Data Statistik',
                'nama_pimpinan' => 'Kepala Seksi Pengelolaan Data Statistik',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Pengelolaan Data Statistik',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Bidang Informasi Publik & Statistik',
                'nama_divisi' => 'Seksi Pengelolaan Informasi Publik & Media',
                'nama_pimpinan' => 'Kepala Seksi Pengelolaan Informasi Publik & Media',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Pengelolaan Informasi Publik & Media',
                'radius_meter' => 40,
            ],

            // 3. Di bawah Bidang Komunikasi Publik (ada 3 seksi)
            [
                'bidang_key' => 'Bidang Komunikasi Publik',
                'nama_divisi' => 'Seksi Kemitraan Komunikasi Publik & Hubungan Media',
                'nama_pimpinan' => 'Kepala Seksi Kemitraan Komunikasi Publik & Hubungan Media',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Kemitraan Komunikasi Publik & Hubungan Media',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Bidang Komunikasi Publik',
                'nama_divisi' => 'Seksi Pengelolaan Opini Publik',
                'nama_pimpinan' => 'Kepala Seksi Pengelolaan Opini Publik',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Pengelolaan Opini Publik',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Bidang Komunikasi Publik',
                'nama_divisi' => 'Seksi Sumber Daya Komunikasi Publik',
                'nama_pimpinan' => 'Kepala Seksi Sumber Daya Komunikasi Publik',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Sumber Daya Komunikasi Publik',
                'radius_meter' => 40,
            ],

            // 4. Di bawah Bidang Aplikasi Informatika (ada 3 seksi)
            [
                'bidang_key' => 'Bidang Aplikasi Informatika',
                'nama_divisi' => 'Seksi Pengembangan Aplikasi',
                'nama_pimpinan' => 'Kepala Seksi Pengembangan Aplikasi',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Pengembangan Aplikasi',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Bidang Aplikasi Informatika',
                'nama_divisi' => 'Seksi Tata Kelola Infrastruktur E-Goverment',
                'nama_pimpinan' => 'Kepala Seksi Tata Kelola Infrastruktur E-Goverment',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Tata Kelola Infrastruktur E-Goverment',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Bidang Aplikasi Informatika',
                'nama_divisi' => 'Seksi Tata Kelola & Ekosistem E-Goverment',
                'nama_pimpinan' => 'Kepala Seksi Tata Kelola & Ekosistem E-Goverment',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Tata Kelola & Ekosistem E-Goverment',
                'radius_meter' => 40,
            ],

            // 5. Di bawah Bidang Persandian & Keamanan Informasi (ada 3 seksi)
            [
                'bidang_key' => 'Bidang Persandian & Keamanan Informasi',
                'nama_divisi' => 'Seksi Pengembangan Kapasitas Tata Kelola Persandian & Keamanan Informasi',
                'nama_pimpinan' => 'Kepala Seksi Pengembangan Kapasitas Tata Kelola Persandian & Keamanan Informasi',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Pengembangan Kapasitas Tata Kelola Persandian & Keamanan Informasi',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Bidang Persandian & Keamanan Informasi',
                'nama_divisi' => 'Seksi Manajemen Integritas Data & Keamanan Informasi',
                'nama_pimpinan' => 'Kepala Seksi Manajemen Integritas Data & Keamanan Informasi',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Manajemen Integritas Data & Keamanan Informasi',
                'radius_meter' => 40,
            ],
            [
                'bidang_key' => 'Bidang Persandian & Keamanan Informasi',
                'nama_divisi' => 'Seksi Monitoring Evaluasi & Audit Persandian & Keamanan Informasi',
                'nama_pimpinan' => 'Kepala Seksi Monitoring Evaluasi & Audit Persandian & Keamanan Informasi',
                'nip_pimpinan' => null,
                'jabatan_pimpinan' => 'Kepala Seksi Monitoring Evaluasi & Audit Persandian & Keamanan Informasi',
                'radius_meter' => 40,
            ],
        ];

        foreach ($subUnitList as $subData) {
            $bidangKey = $subData['bidang_key'];
            unset($subData['bidang_key']);

            $subData['parent_id'] = $bidangModels[$bidangKey]->id;

            Divisi::updateOrCreate(
                ['nama_divisi' => $subData['nama_divisi']],
                $subData
            );
        }

        // Pastikan pembimbing yang sudah ada terhubung ke salah satu divisi default jika belum memiliki divisi
        $defaultDivisi = Divisi::where('nama_divisi', 'Sub Bagian Umum & Kepegawaian')->first() ?? Divisi::first();
        if ($defaultDivisi) {
            Pembimbing::whereNull('divisi_id')->update(['divisi_id' => $defaultDivisi->id]);
        }
    }
}
