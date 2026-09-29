<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_libur';

    protected $fillable = [
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Daftar tanggal merah / hari libur nasional Indonesia (sebagai fallback & acuan standar).
     * Format: 'Y-m-d' => 'Keterangan Libur'
     */
    public static function getDaftarLiburNasionalBawaan(): array
    {
        return [
            // Tahun 2025
            '2025-01-01' => 'Tahun Baru 2025 Masehi',
            '2025-01-27' => "Isra Mi'raj Nabi Muhammad SAW",
            '2025-01-29' => 'Tahun Baru Imlek 2576 Kongzili',
            '2025-03-29' => 'Hari Suci Nyepi (Tahun Baru Saka 1947)',
            '2025-03-31' => 'Hari Raya Idul Fitri 1446 H',
            '2025-04-01' => 'Hari Raya Idul Fitri 1446 H',
            '2025-04-18' => 'Wafat Yesus Kristus',
            '2025-04-20' => 'Kebangkitan Yesus Kristus (Paskah)',
            '2025-05-01' => 'Hari Buruh Internasional',
            '2025-05-12' => 'Hari Raya Waisak 2569 BE',
            '2025-05-29' => 'Kenaikan Yesus Kristus',
            '2025-06-01' => 'Hari Lahir Pancasila',
            '2025-06-07' => 'Hari Raya Idul Adha 1446 H',
            '2025-06-27' => 'Tahun Baru Islam 1447 H',
            '2025-08-17' => 'Hari Kemerdekaan RI ke-80',
            '2025-09-05' => 'Maulid Nabi Muhammad SAW',
            '2025-12-25' => 'Hari Raya Natal',

            // Tahun 2026
            '2026-01-01' => 'Tahun Baru 2026 Masehi',
            '2026-01-16' => "Isra Mi'raj Nabi Muhammad SAW",
            '2026-02-17' => 'Tahun Baru Imlek 2577 Kongzili',
            '2026-03-19' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)',
            '2026-03-20' => 'Hari Raya Idul Fitri 1447 H',
            '2026-03-21' => 'Hari Raya Idul Fitri 1447 H',
            '2026-04-03' => 'Wafat Yesus Kristus',
            '2026-04-05' => 'Kebangkitan Yesus Kristus (Paskah)',
            '2026-05-01' => 'Hari Buruh Internasional',
            '2026-05-14' => 'Kenaikan Yesus Kristus',
            '2026-05-27' => 'Hari Raya Idul Adha 1447 H',
            '2026-05-31' => 'Hari Raya Waisak 2570 BE',
            '2026-06-01' => 'Hari Lahir Pancasila',
            '2026-06-16' => 'Tahun Baru Islam 1448 H',
            '2026-08-17' => 'Hari Kemerdekaan RI ke-81',
            '2026-08-25' => 'Maulid Nabi Muhammad SAW',
            '2026-12-25' => 'Hari Raya Natal',

            // Tahun 2027
            '2027-01-01' => 'Tahun Baru 2027 Masehi',
            '2027-02-06' => 'Tahun Baru Imlek 2578 Kongzili',
            '2027-03-10' => 'Hari Raya Idul Fitri 1448 H',
            '2027-03-11' => 'Hari Raya Idul Fitri 1448 H',
            '2027-05-01' => 'Hari Buruh Internasional',
            '2027-08-17' => 'Hari Kemerdekaan RI ke-82',
            '2027-12-25' => 'Hari Raya Natal',
        ];
    }

    /**
     * Memeriksa apakah suatu tanggal merupakan hari libur nasional / tanggal merah (di luar weekend).
     */
    public static function isTanggalMerah(CarbonInterface|string $date): bool
    {
        $dateStr = $date instanceof CarbonInterface ? $date->toDateString() : Carbon::parse($date)->toDateString();

        // Cek di database
        if (static::whereDate('tanggal', $dateStr)->exists()) {
            return true;
        }

        // Cek fallback daftar libur nasional bawaan
        $defaultHolidays = static::getDaftarLiburNasionalBawaan();

        return array_key_exists($dateStr, $defaultHolidays);
    }

    /**
     * Mendapatkan keterangan libur untuk tanggal tertentu jika ada.
     */
    public static function getKeteranganLibur(CarbonInterface|string $date): ?string
    {
        $dateStr = $date instanceof CarbonInterface ? $date->toDateString() : Carbon::parse($date)->toDateString();

        $liburDb = static::whereDate('tanggal', $dateStr)->first();
        if ($liburDb) {
            return $liburDb->keterangan;
        }

        $defaultHolidays = static::getDaftarLiburNasionalBawaan();

        return $defaultHolidays[$dateStr] ?? null;
    }
}
