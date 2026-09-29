<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use Illuminate\Database\Seeder;

class HariLiburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = HariLibur::getDaftarLiburNasionalBawaan();

        foreach ($holidays as $tanggal => $keterangan) {
            HariLibur::firstOrCreate(
                ['tanggal' => $tanggal],
                ['keterangan' => $keterangan]
            );
        }
    }
}
