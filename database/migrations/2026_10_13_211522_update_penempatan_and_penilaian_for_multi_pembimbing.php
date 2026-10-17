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
        // 1. Tambah pembimbing_id ke tabel penempatan_magang
        Schema::table('penempatan_magang', function (Blueprint $table) {
            $table->foreignId('pembimbing_id')
                ->nullable()
                ->after('divisi_id')
                ->constrained('pembimbing')
                ->nullOnDelete();
        });

        // Isi default pembimbing_id pada penempatan_magang yang sudah ada berdasarkan pembimbing_id peserta magang
        if (DB::table('penempatan_magang')->whereNull('pembimbing_id')->exists()) {
            $items = DB::table('penempatan_magang')
                ->join('magang', 'penempatan_magang.magang_id', '=', 'magang.id')
                ->whereNull('penempatan_magang.pembimbing_id')
                ->whereNotNull('magang.pembimbing_id')
                ->select('penempatan_magang.id', 'magang.pembimbing_id')
                ->get();

            foreach ($items as $item) {
                DB::table('penempatan_magang')
                    ->where('id', $item->id)
                    ->update(['pembimbing_id' => $item->pembimbing_id]);
            }
        }

        // 2. Modifikasi tabel penilaian: izinkan multiple penilaian per magang & relasikan ke penempatan_magang
        Schema::table('penilaian', function (Blueprint $table) {
            $table->index('magang_id', 'penilaian_magang_id_index');
            $table->dropUnique('penilaian_magang_id_unique');

            $table->foreignId('penempatan_magang_id')
                ->nullable()
                ->after('pembimbing_id')
                ->constrained('penempatan_magang')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropForeign(['penempatan_magang_id']);
            $table->dropColumn('penempatan_magang_id');

            $table->unique('magang_id', 'penilaian_magang_id_unique');
            $table->dropIndex('penilaian_magang_id_index');
        });

        Schema::table('penempatan_magang', function (Blueprint $table) {
            $table->dropForeign(['pembimbing_id']);
            $table->dropColumn('pembimbing_id');
        });
    }
};
