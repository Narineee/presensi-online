<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengguna_id')
                ->unique()
                ->constrained('pengguna')
                ->cascadeOnDelete();

            $table->foreignId('pembimbing_id')
                ->nullable()
                ->constrained('pembimbing')
                ->nullOnDelete();

            $table->foreignId('divisi_id')
                ->nullable()
                ->constrained('divisi')
                ->nullOnDelete();

            $table->string('no_induk', 30)->nullable();
            $table->string('nama_lengkap', 100);
            $table->string('jurusan', 100)->nullable();
            $table->string('instansi_pendidikan', 150)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('foto')->nullable();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->enum('status', [
                'aktif',
                'selesai',
                'cuti',
            ])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magang');
    }
};
