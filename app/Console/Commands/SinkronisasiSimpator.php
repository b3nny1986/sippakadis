<?php

namespace App\Console\Commands;

use App\Models\HistoriScraping;
use App\Models\Kendaraan;
use App\Services\SimpatorService;
use Illuminate\Console\Command;

class SinkronisasiSimpator extends Command
{
    protected $signature = 'sinkronisasi:simpator
                            {--batch=100 : Jumlah kendaraan per batch}
                            {--force : Abaikan cache 24 jam}';

    protected $description = 'Sinkronkan data jatuh tempo PKB/STNK dari Simpator secara massal';

    public function handle(SimpatorService $simpator): int
    {
        $batch = (int) $this->option('batch');
        $force = (bool) $this->option('force');

        $kendaraan = Kendaraan::query()
            ->whereNotNull('nopol')
            ->whereIn('sumber_data', [Kendaraan::SUMBER_CSV, Kendaraan::SUMBER_MANUAL])
            ->withCount('historiScraping')
            ->orderBy('histori_scraping_count')
            ->orderBy('id')
            ->limit($batch)
            ->get();

        if ($kendaraan->isEmpty()) {
            $this->info('Tidak ada kendaraan yang perlu disinkronkan.');

            return self::SUCCESS;
        }

        $hasil = [
            HistoriScraping::DITEMUKAN => 0,
            HistoriScraping::TIDAK_DITEMUKAN => 0,
            HistoriScraping::GAGAL => 0,
        ];

        $bar = $this->output->createProgressBar($kendaraan->count());
        $bar->start();

        foreach ($kendaraan as $item) {
            $res = $simpator->sinkronisasiKendaraan($item, $force);
            $hasil[$res['status']]++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info(sprintf(
            'Selesai: %d ditemukan, %d tidak ditemukan, %d gagal.',
            $hasil[HistoriScraping::DITEMUKAN],
            $hasil[HistoriScraping::TIDAK_DITEMUKAN],
            $hasil[HistoriScraping::GAGAL]
        ));

        return self::SUCCESS;
    }
}
