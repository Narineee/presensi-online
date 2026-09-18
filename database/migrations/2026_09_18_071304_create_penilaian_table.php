<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();

            $table->foreignId('magang_id')
                ->constrained('magang')
                ->cascadeOnDelete();

            $table->foreignId('pembimbing_id')
                ->constrained('pembimbing')
                ->cascadeOnDelete();

            $table->unsignedInteger('total_nilai')->nullable();

            $table->timestamps();

            $table->unique('magang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
