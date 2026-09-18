<?php

namespace Database\Seeders;

use App\Models\KriteriaPenilaian;
use Illuminate\Database\Seeder;

class KriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kriteria = [
            [
                'nama' => 'Kedisiplinan & Tanggung Jawab',
                'bobot' => 25,
            ],
            [
                'nama' => 'Kualitas Hasil Kerja & Keterampilan Teknis',
                'bobot' => 30,
            ],
            [
                'nama' => 'Inisiatif & Pemecahan Masalah',
                'bobot' => 20,
            ],
            [
                'nama' => 'Kerjasama Tim & Komunikasi',
                'bobot' => 25,
            ],
        ];

        foreach ($kriteria as $item) {
            KriteriaPenilaian::firstOrCreate(
                ['nama' => $item['nama']],
                ['bobot' => $item['bobot']]
            );
        }
    }
}
