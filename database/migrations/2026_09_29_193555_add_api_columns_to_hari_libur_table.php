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
        Schema::table('hari_libur', function (Blueprint $table) {
            $table->string('nama')->nullable()->after('tanggal');
            $table->string('jenis', 100)->default('Hari Libur Nasional')->after('nama');
            $table->string('sumber', 50)->default('manual')->after('jenis');
            $table->string('external_id', 100)->nullable()->after('sumber');
        });

        // Set nama to existing keterangan if nama is null
        DB::table('hari_libur')
            ->whereNull('nama')
            ->update([
                'nama' => DB::raw('keterangan'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hari_libur', function (Blueprint $table) {
            $table->dropColumn(['nama', 'jenis', 'sumber', 'external_id']);
        });
    }
};
