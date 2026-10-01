<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait StoresBase64Image
{
    /** Resize (maks 1000px) dan kompres ke JPG kualitas 75, lalu simpan. Mengembalikan path atau null. */
    private function compressAndStoreImage(
        string $base64Image,
        string $directory,
        string $fileName,
        string $disk = 'public'
    ): ?string {
        try {
            if (! function_exists('imagecreatefromstring')) {
                throw new \RuntimeException('Ekstensi GD PHP belum aktif.');
            }

            $parts = explode(';base64,', $base64Image, 2);
            if (count($parts) !== 2) {
                throw new \RuntimeException('Format base64 tidak valid.');
            }

            $binary = base64_decode($parts[1], true);
            if ($binary === false) {
                throw new \RuntimeException('Isi base64 tidak bisa didecode.');
            }

            $image = @imagecreatefromstring($binary);
            if ($image === false) {
                throw new \RuntimeException('Data gambar tidak bisa dibaca.');
            }

            $w = imagesx($image);
            $h = imagesy($image);
            $scale = min(1, 1000 / max($w, $h));
            if ($scale < 1) {
                $resized = imagescale($image, (int) round($w * $scale), (int) round($h * $scale));
                if ($resized !== false) {
                    $image = $resized;
                }
            }

            ob_start();
            imagejpeg($image, null, 75);
            $jpeg = ob_get_clean();

            if ($jpeg === false || $jpeg === '') {
                throw new \RuntimeException('Gagal mengubah gambar ke JPG.');
            }

            $path = $directory.'/'.$fileName.'.jpg';
            if (! Storage::disk($disk)->put($path, $jpeg)) {
                throw new \RuntimeException('Gagal menulis file ke disk '.$disk.'.');
            }

            return $path;
        } catch (\Throwable $e) {
            Log::error('Gagal memproses foto', [
                'error' => $e->getMessage(),
                'directory' => $directory,
            ]);

            return null;
        }
    }
}
