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
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->string('judul')->nullable()->after('pekerjaan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->dropColumn('judul');
        });
    }
};
