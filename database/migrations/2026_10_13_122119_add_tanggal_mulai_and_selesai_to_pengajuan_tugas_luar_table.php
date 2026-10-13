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
        Schema::table('pengajuan_tugas_luar', function (Blueprint $table) {
            $table->date('tanggal_mulai')->nullable()->after('tanggal');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            $table->time('waktu_mulai')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_tugas_luar', function (Blueprint $table) {
            $table->dropColumn(['tanggal_mulai', 'tanggal_selesai']);
        });
    }
};
