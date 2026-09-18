<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_izin', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengguna_id')
                ->constrained('pengguna')
                ->cascadeOnDelete();

            $table->enum('jenis_izin', [
                'sakit',
                'izin',
                'cuti',
            ]);

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->text('alasan');

            // Foto/surat bukti
            $table->string('bukti_file')->nullable();

            $table->enum('status_approval', [
                'pending',
                'disetujui',
                'ditolak',
            ])->default('pending');

            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('pengguna')
                ->nullOnDelete();

            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izin');
    }
};
