<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengguna_id')
                ->unique()
                ->constrained('pengguna')
                ->cascadeOnDelete();

            $table->foreignId('pembimbing_id')
                ->nullable()
                ->constrained('pembimbing')
                ->nullOnDelete();

            $table->string('nik', 30)->unique();
            $table->string('nama_lengkap', 100);
            $table->string('jabatan', 50)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->date('tanggal_bergabung')->nullable();

            $table->enum('status', [
                'aktif',
                'non_aktif',
            ])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs');
    }
};
