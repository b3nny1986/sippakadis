<?php

namespace Database\Seeders;

use App\Services\ImportCsvService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            StatusKendaraanSeeder::class,
            JenisKendaraanSeeder::class,
            UserSeeder::class,
        ]);

        $path = (string) env('IMPORT_CSV_PATH', base_path('data_master_sippakadis.csv'));

        if (is_file($path)) {
            Artisan::call('import:csv', ['--path' => $path]);
            $this->command?->info(Artisan::output());
        }
    }
}
