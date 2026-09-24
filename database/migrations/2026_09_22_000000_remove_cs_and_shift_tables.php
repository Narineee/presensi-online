<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menghapus seluruh tabel dan data terkait CS & Shift.
     */
    public function up(): void
    {
        // 1. Bersihkan catatan presensi, aktivitas, dan izin milik CS jika ada
        $csUserIds = DB::table('pengguna')->where('role', 'cs')->pluck('id');

        if ($csUserIds->isNotEmpty()) {
            DB::table('presensi')->whereIn('pengguna_id', $csUserIds)->delete();
            DB::table('aktivitas')->whereIn('pengguna_id', $csUserIds)->delete();
            DB::table('pengajuan_izin')->whereIn('pengguna_id', $csUserIds)->delete();
        }

        // 2. Hapus foreign keys dan tabel jadwal_shift_cs, cs, serta shift
        Schema::dropIfExists('jadwal_shift_cs');
        Schema::dropIfExists('cs');
        Schema::dropIfExists('shift');

        // 3. Hapus seluruh akun login dengan role cs
        DB::table('pengguna')->where('role', 'cs')->delete();

        // 4. Perbarui tipe kolom role pada tabel pengguna di database MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pengguna MODIFY COLUMN role ENUM('admin', 'pembimbing', 'magang') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Fitur CS dihapus permanen untuk sistem informasi manajemen magang.
    }
};
