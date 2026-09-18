<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengguna_id')
                ->constrained('pengguna')
                ->cascadeOnDelete();

            $table->date('tanggal');

            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();

            $table->enum('status', [
                'hadir',
                'izin',
                'sakit',
                'alpa',
                'cuti',
            ])->default('hadir');

            $table->enum('mode_kerja', [
                'onsite',
                'wfh',
            ])->default('onsite');

            $table->string('foto_masuk')->nullable();
            $table->string('foto_keluar')->nullable();

            $table->string('lokasi_masuk')->nullable();
            $table->string('lokasi_keluar')->nullable();

            $table->string('keterangan')->nullable();

            $table->timestamps();

            $table->unique(['pengguna_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
