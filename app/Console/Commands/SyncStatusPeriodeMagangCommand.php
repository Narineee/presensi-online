<?php

namespace App\Console\Commands;

use App\Models\Magang;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('magang:sync-periode')]
#[Description('Menandai otomatis status peserta magang menjadi selesai apabila periodenya telah berakhir')]
class SyncStatusPeriodeMagangCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();

        // Cari seluruh magang yang tanggal selesainya telah lewat hari ini dan status masih 'aktif'
        $expiredList = Magang::whereDate('tanggal_selesai', '<', $today)
            ->where('status', 'aktif')
            ->get();

        $count = 0;
        foreach ($expiredList as $magang) {
            $magang->update(['status' => 'selesai']);
            $count++;
        }

        $this->info("Sinkronisasi selesai: {$count} peserta magang yang periodenya berakhir telah ditandai dengan status selesai.");

        return self::SUCCESS;
    }
}
