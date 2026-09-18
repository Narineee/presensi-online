<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengguna_id')
                ->constrained('pengguna')
                ->cascadeOnDelete();

            $table->date('tanggal');
            $table->text('isi');
            $table->unsignedTinyInteger('progress')->default(0);

            $table->enum('status', [
                'pending',
                'approve',
                'revisi',
            ])->default('pending');

            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('pengguna')
                ->nullOnDelete();

            $table->timestamp('validated_at')->nullable();

            $table->text('catatan_validasi')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas');
    }
};
