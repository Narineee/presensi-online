<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magang', function (Blueprint $table) {
            $table->longText('face_descriptors')->nullable()->after('foto');
            $table->string('face_foto')->nullable()->after('face_descriptors');
            $table->timestamp('face_registered_at')->nullable()->after('face_foto');
        });

        Schema::table('presensi', function (Blueprint $table) {
            $table->float('face_distance_masuk')->nullable()->after('lokasi_keluar');
            $table->float('face_distance_keluar')->nullable()->after('face_distance_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('magang', function (Blueprint $table) {
            $table->dropColumn(['face_descriptors', 'face_foto', 'face_registered_at']);
        });

        Schema::table('presensi', function (Blueprint $table) {
            $table->dropColumn(['face_distance_masuk', 'face_distance_keluar']);
        });
    }
};