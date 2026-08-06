<?php

namespace App\Console\Commands;

use App\Services\ImportCsvService;
use Illuminate\Console\Command;

class ImportCsv extends Command
{
    protected $signature = 'import:csv
        {--path= : Lokasi file CSV (default: data_master_sippakadis.csv di root proyek)}
        {--userId= : ID pengguna yang menjalankan (untuk audit)}';

    protected $description = 'Impor data kendaraan dari file CSV master';

    public function handle(ImportCsvService $service): int
    {
        $path = $this->option('path') ?: (string) env('IMPORT_CSV_PATH', base_path('data_master_sippakadis.csv'));
        $userId = $this->option('userId') ? (int) $this->option('userId') : null;

        $this->info("Mulai impor dari: {$path}");

        try {
            $result = $service->import($path, $userId);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Selesai. Total=%d, Dibuat=%d, Diperbarui=%d, Dilewati=%d, Gagal=%d',
            $result['total'],
            $result['created'],
            $result['updated'],
            $result['skipped'],
            $result['failed']
        ));

        foreach (array_slice($result['errors'], 0, 20) as $error) {
            $this->warn("[{$error['nopol']}] {$error['pesan']}");
        }

        return self::SUCCESS;
    }
}
