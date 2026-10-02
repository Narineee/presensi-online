<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perbarui enum mode_kerja pada tabel presensi
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE presensi MODIFY COLUMN mode_kerja ENUM('onsite', 'wfh', 'tugas_luar') NOT NULL DEFAULT 'onsite'");
        } else {
            Schema::table('presensi', function (Blueprint $table) {
                $table->string('mode_kerja', 20)->default('onsite')->change();
            });
        }

        // 2. Buat tabel pengajuan_tugas_luar
        Schema::create('pengajuan_tugas_luar', function (Blueprint $table) {
            $table->id();

            $table->foreignId('presensi_id')
                ->nullable()
                ->constrained('presensi')
                ->nullOnDelete();

            $table->foreignId('pengguna_id')
                ->constrained('pengguna')
                ->cascadeOnDelete();

            $table->foreignId('magang_id')
                ->nullable()
                ->constrained('magang')
                ->nullOnDelete();

            $table->date('tanggal');
            $table->string('tujuan');
            $table->text('keperluan');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai')->nullable();
            $table->string('bukti')->nullable();

            $table->enum('status_verifikasi', [
                'menunggu',
                'disetujui',
                'ditolak',
            ])->default('menunggu');

            $table->text('catatan_pembimbing')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('pengguna')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_tugas_luar');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE presensi MODIFY COLUMN mode_kerja ENUM('onsite', 'wfh') NOT NULL DEFAULT 'onsite'");
        } else {
            Schema::table('presensi', function (Blueprint $table) {
                $table->string('mode_kerja', 20)->default('onsite')->change();
            });
        }
    }
};
