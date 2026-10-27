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
        Schema::table('rencana_kerja', function (Blueprint $table) {
            $table->foreignId('pembimbing_id')->nullable()->after('magang_id')->constrained('pembimbing')->nullOnDelete();
        });

        // Backfill data rencana_kerja lama berdasarkan pembimbing default magang (kompatibel MySQL & SQLite)
        if (Schema::hasTable('magang')) {
            DB::table('rencana_kerja')
                ->whereNull('pembimbing_id')
                ->update([
                    'pembimbing_id' => DB::raw('(SELECT pembimbing_id FROM magang WHERE magang.id = rencana_kerja.magang_id)'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rencana_kerja', function (Blueprint $table) {
            $table->dropForeign(['pembimbing_id']);
            $table->dropColumn('pembimbing_id');
        });
    }
};
