<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi')->default('Dinas Komunikasi dan Informatika');
            $table->string('nama_aplikasi')->default('Sistem Presensi & Aktivitas Digital');
            $table->string('nama_kepala_dinas');
            $table->string('nip_kepala_dinas', 50)->nullable();
            $table->string('jabatan_kepala_dinas')->default('Kepala Dinas');
            $table->string('pangkat_golongan')->nullable();
            $table->string('kota_surat')->default('Banjarbaru');
            $table->text('alamat_instansi')->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
