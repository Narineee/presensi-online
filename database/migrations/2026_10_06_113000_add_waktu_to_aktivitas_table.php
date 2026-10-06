<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            if (! Schema::hasColumn('aktivitas', 'waktu_mulai')) {
                $table->time('waktu_mulai')->nullable()->after('tanggal');
            }
            if (! Schema::hasColumn('aktivitas', 'waktu_selesai')) {
                $table->time('waktu_selesai')->nullable()->after('waktu_mulai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            if (Schema::hasColumn('aktivitas', 'waktu_selesai')) {
                $table->dropColumn('waktu_selesai');
            }
            if (Schema::hasColumn('aktivitas', 'waktu_mulai')) {
                $table->dropColumn('waktu_mulai');
            }
        });
    }
};
