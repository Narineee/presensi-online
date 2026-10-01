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
        Schema::create('penempatan_magang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('magang_id')
                ->constrained('magang')
                ->cascadeOnDelete();

            $table->foreignId('divisi_id')
                ->constrained('divisi')
                ->cascadeOnDelete();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->timestamps();

            // Index untuk pencarian cepat riwayat penempatan berdasarkan tanggal
            $table->index(['magang_id', 'tanggal_mulai', 'tanggal_selesai'], 'idx_penempatan_magang_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penempatan_magang');
    }
};
