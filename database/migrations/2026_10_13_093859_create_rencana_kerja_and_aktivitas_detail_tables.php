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
        Schema::create('rencana_kerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('magang_id')->constrained('magang')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['aktif', 'selesai', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        Schema::create('aktivitas_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aktivitas_id')->constrained('aktivitas')->cascadeOnDelete();
            $table->foreignId('rencana_kerja_id')->nullable()->constrained('rencana_kerja')->restrictOnDelete();
            $table->string('rencana_kerja_snapshot')->nullable();
            $table->text('progres');
            $table->timestamps();
        });

        Schema::table('aktivitas', function (Blueprint $table) {
            $table->text('isi')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            $table->text('isi')->nullable(false)->change();
        });

        Schema::dropIfExists('aktivitas_detail');
        Schema::dropIfExists('rencana_kerja');
    }
};
