<?php

namespace App\Console\Commands;

use App\Services\HariLiburService;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-hari-libur {tahun? : Tahun yang ingin disinkronkan} {--all : Sinkronkan tahun berjalan dan tahun berikutnya}')]
#[Description('Sinkronisasi data hari libur nasional dan cuti bersama dari API')]
class SyncHariLiburCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(HariLiburService $service): int
    {
        $inputTahun = $this->argument('tahun');
        $syncAll = $this->option('all');
        $currentYear = (int) now()->format('Y');

        $years = [];
        if ($inputTahun) {
            $years[] = (int) $inputTahun;
        } elseif ($syncAll) {
            $years = [$currentYear, $currentYear + 1];
        } else {
            $years = [$currentYear];
        }

        $this->info('Memulai sinkronisasi hari libur dari API...');

        $hasError = false;

        foreach ($years as $year) {
            $this->line("-> Memproses sinkronisasi tahun {$year}...");

            try {
                $result = $service->syncByYear($year);
                $this->info("   [OK] {$result['message']}");
            } catch (Exception $e) {
                $hasError = true;
                $this->error("   [GAGAL] Tahun {$year}: {$e->getMessage()}");
            }
        }

        if ($hasError) {
            $this->warn('Sinkronisasi selesai dengan beberapa peringatan/kegagalan.');

            return self::FAILURE;
        }

        $this->info('Sinkronisasi hari libur selesai dengan sukses.');

        return self::SUCCESS;
    }
}
