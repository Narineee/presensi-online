<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kriteria', function (Blueprint $table) {
            $table->boolean('is_presensi')->default(false)->after('bobot');
        });

        // Set kriteria Kedisiplinan atau Presensi pertama sebagai kriteria presensi otomatis
        $kriteriaPresensi = DB::table('kriteria')
            ->where('nama', 'like', '%kedisiplinan%')
            ->orWhere('nama', 'like', '%presensi%')
            ->orderBy('id', 'asc')
            ->first();

        if ($kriteriaPresensi) {
            DB::table('kriteria')
                ->where('id', $kriteriaPresensi->id)
                ->update(['is_presensi' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kriteria', function (Blueprint $table) {
            $table->dropColumn('is_presensi');
        });
    }
};
