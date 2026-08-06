<?php

namespace App\Console\Commands;

use App\Services\MonitoringService;
use Illuminate\Console\Command;

class MonitoringDaily extends Command
{
    protected $signature = 'monitoring:daily';

    protected $description = 'Perbarui status jatuh tempo semua kendaraan lalu bangun notifikasi';

    public function handle(MonitoringService $monitoring): int
    {
        $this->info('Memperbarui status kendaraan...');
        $status = $monitoring->hitungSemuaStatus();
        $this->info("{$status['scanned']} kendaraan dipindai, {$status['updated']} diperbarui.");

        $this->info('Membangun notifikasi jatuh tempo...');
        $notif = $monitoring->bangunNotifikasi();
        $this->info("Notifikasi baru: {$notif['pkb']} PKB, {$notif['stnk']} STNK.");

        return self::SUCCESS;
    }
}
