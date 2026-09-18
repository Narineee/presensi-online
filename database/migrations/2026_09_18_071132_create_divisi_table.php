<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisi', function (Blueprint $table) {
            $table->id();

            $table->string('nama_divisi', 100);

            $table->string('nama_pimpinan', 100)->nullable();
            $table->string('nip_pimpinan', 30)->nullable();
            $table->string('jabatan_pimpinan', 100)->nullable();

            // Lokasi kantor untuk presensi onsite
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius_meter')->default(100);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divisi');
    }
};
