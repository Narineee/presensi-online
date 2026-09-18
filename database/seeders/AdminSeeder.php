<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Jalankan database seeder untuk membuat Super Admin default.
     * Sesuai PRD: role admin tidak terikat dengan profil magang/cs/pembimbing.
     */
    public function run(): void
    {
        Pengguna::updateOrCreate(
            ['username' => 'admin'],
            [
                'password' => 'admin123', // Otomatis di-hash oleh cast 'hashed' pada model Pengguna
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
