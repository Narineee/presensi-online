<?php

namespace App\Services;

class FaceVerificationService
{
    /** Ubah JSON menjadi list descriptor (tiap descriptor = 128 angka). Null jika tidak valid. */
    public function parse(?string $json): ?array
    {
        $data = json_decode((string) $json, true);
        if (! is_array($data) || empty($data)) {
            return null;
        }

        // Dukung 1 descriptor (daftar angka) maupun banyak descriptor
        if (is_numeric($data[0] ?? null)) {
            $data = [$data];
        }

        foreach ($data as $d) {
            if (! is_array($d) || count($d) !== 128) {
                return null;
            }
            foreach ($d as $v) {
                if (! is_numeric($v)) {
                    return null;
                }
            }
        }

        return array_map(fn ($d) => array_map('floatval', $d), $data);
    }

    public function distance(array $a, array $b): float
    {
        $sum = 0.0;
        foreach ($a as $i => $v) {
            $sum += ($v - $b[$i]) ** 2;
        }

        return sqrt($sum);
    }

    /** Bandingkan wajah presensi dengan semua sampel terdaftar, ambil jarak terkecil. */
    public function match(array $enrolled, array $probe): array
    {
        $min = min(array_map(fn ($e) => $this->distance($e, $probe), $enrolled));

        return [
            'match' => $min <= config('face.threshold'),
            'distance' => round($min, 4),
        ];
    }
}