<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pekerjaan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('magang_id')
                ->constrained('magang')
                ->cascadeOnDelete();

            $table->foreignId('pembimbing_id')
                ->constrained('pembimbing')
                ->cascadeOnDelete();

            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('jenis', ['proyek', 'rutin']);
            $table->unsignedTinyInteger('progress')->nullable()->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->date('target_selesai')->nullable();
            $table->enum('status', ['aktif', 'selesai', 'nonaktif'])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pekerjaan');
    }
};
