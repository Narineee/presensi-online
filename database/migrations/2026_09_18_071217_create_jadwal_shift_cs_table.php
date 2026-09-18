<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_shift_cs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cs_id')
                ->constrained('cs')
                ->cascadeOnDelete();

            $table->foreignId('shift_id')
                ->constrained('shift')
                ->cascadeOnDelete();

            $table->date('tanggal');

            $table->enum('status', [
                'terjadwal',
                'libur',
                'izin',
                'cuti',
            ])->default('terjadwal');

            $table->string('keterangan', 255)->nullable();

            $table->timestamps();

            $table->unique(['cs_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_shift_cs');
    }
};
