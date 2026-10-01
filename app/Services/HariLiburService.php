<?php

namespace App\Services;

use App\Models\HariLibur;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class HariLiburService
{
    /**
     * Memeriksa apakah konfigurasi API Hari Libur sudah lengkap.
     */
    public function isConfigured(): bool
    {
        $enabled = config('services.hari_libur.enabled', true);
        $key = config('services.hari_libur.key');

        return $enabled && ! empty($key);
    }

    /**
     * Mendapatkan daftar tahun yang relevan untuk filter & sinkronisasi.
     * Menggabungkan tahun dari database dengan rentang tahun saat ini.
     *
     * @return array<int>
     */
    public function getAvailableYears(): array
    {
        $currentYear = (int) now()->format('Y');

        $dbYears = HariLibur::query()
            ->pluck('tanggal')
            ->map(fn ($d) => (int) Carbon::parse($d)->year)
            ->unique()
            ->values()
            ->all();

        $defaultYears = [
            $currentYear - 1,
            $currentYear,
            $currentYear + 1,
            $currentYear + 2,
        ];

        $years = array_unique(array_merge($defaultYears, $dbYears));
        rsort($years);

        return array_values($years);
    }

    /**
     * Sinkronisasi data hari libur dari API Indonesia untuk tahun tertentu.
     *
     * @return array{
     *     success: bool,
     *     tahun: int,
     *     total_api: int,
     *     inserted: int,
     *     updated: int,
     *     skipped_manual: int,
     *     message: string
     * }
     *
     * @throws RuntimeException
     */
    public function syncByYear(int $tahun): array
    {
        $enabled = config('services.hari_libur.enabled', true);
        if (! $enabled) {
            throw new RuntimeException('Integrasi API Hari Libur sedang dinonaktifkan di konfigurasi.');
        }

        $url = config('services.hari_libur.url', 'https://use.apiindonesia.id/api/v1/libur');
        $apiKey = config('services.hari_libur.key');
        $timeout = (int) config('services.hari_libur.timeout', 15);

        if (empty($apiKey)) {
            throw new RuntimeException('API Key Hari Libur belum dikonfigurasi. Silakan isi HARI_LIBUR_API_KEY pada file .env.');
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout($timeout)->get($url, [
                'tahun' => $tahun,
            ]);
        } catch (Exception $e) {
            Log::error('HariLiburService: Gagal request API Hari Libur.', [
                'tahun' => $tahun,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Tidak dapat terhubung ke server API Hari Libur. Periksa koneksi internet atau coba beberapa saat lagi.');
        }

        if ($response->failed()) {
            $status = $response->status();

            Log::warning('HariLiburService: API Hari Libur merespons dengan status error.', [
                'tahun' => $tahun,
                'status' => $status,
                'body' => $response->json(),
            ]);

            if ($status === 401 || $status === 403) {
                throw new RuntimeException('API Key Hari Libur tidak valid atau tidak memiliki izin akses (HTTP '.$status.').');
            }

            if ($status === 404) {
                return [
                    'success' => true,
                    'tahun' => $tahun,
                    'total_api' => 0,
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped_manual' => 0,
                    'message' => 'Data hari libur untuk tahun '.$tahun.' tidak ditemukan di API.',
                ];
            }

            throw new RuntimeException('API Hari Libur mengalami kendala (HTTP '.$status.'). Data tersimpan di database tetap digunakan.');
        }

        $json = $response->json();
        $items = $json['data'] ?? (is_array($json) && isset($json[0]) ? $json : []);

        if (! is_array($items)) {
            $items = [];
        }

        $totalApi = count($items);
        $inserted = 0;
        $updated = 0;
        $skippedManual = 0;

        foreach ($items as $item) {
            $dateRaw = $item['date'] ?? null;
            if (! $dateRaw) {
                continue;
            }

            try {
                $dateStr = Carbon::parse($dateRaw)->toDateString();
            } catch (Exception) {
                continue;
            }

            $name = trim($item['name'] ?? 'Hari Libur Nasional');
            $description = trim($item['description'] ?? '');
            $keterangan = $description !== '' ? $description : $name;
            $externalId = isset($item['id']) ? (string) $item['id'] : null;

            $isJointLeave = ! empty($item['is_joint_leave'])
                || (isset($item['type']) && str_contains(strtolower($item['type']), 'cuti'));

            $jenis = $isJointLeave ? 'Cuti Bersama' : 'Hari Libur Nasional';

            $existing = HariLibur::whereDate('tanggal', $dateStr)->first();

            if ($existing) {
                // Jangan timpa data manual yang dibuat atau diedit oleh admin
                if ($existing->sumber === 'manual') {
                    $skippedManual++;

                    continue;
                }

                $existing->update([
                    'nama' => $name,
                    'jenis' => $jenis,
                    'keterangan' => $keterangan,
                    'external_id' => $externalId,
                    'sumber' => 'api',
                ]);
                $updated++;
            } else {
                HariLibur::create([
                    'tanggal' => $dateStr,
                    'nama' => $name,
                    'jenis' => $jenis,
                    'keterangan' => $keterangan,
                    'external_id' => $externalId,
                    'sumber' => 'api',
                ]);
                $inserted++;
            }
        }

        Log::info('HariLiburService: Sinkronisasi hari libur tahun '.$tahun.' berhasil.', [
            'tahun' => $tahun,
            'total_api' => $totalApi,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped_manual' => $skippedManual,
        ]);

        $message = "Sinkronisasi tahun {$tahun} berhasil. {$inserted} data baru ditambahkan, {$updated} diperbarui";
        if ($skippedManual > 0) {
            $message .= ", {$skippedManual} data manual dipertahankan";
        }
        $message .= '.';

        return [
            'success' => true,
            'tahun' => $tahun,
            'total_api' => $totalApi,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped_manual' => $skippedManual,
            'message' => $message,
        ];
    }
}
