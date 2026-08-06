<?php

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Jadwal Tugas (Scheduler)
|--------------------------------------------------------------------------
| Di produksi (Vercel) tidak ada cron OS, sehingga jadwal di bawah dieksekusi
| melalui endpoint POST /cron/daily yang dipanggil GitHub Actions setiap hari.
| Jadwal tetap didaftarkan di sini agar perilaku sama bila scheduler
| dijalankan oleh `php artisan schedule:work` pada lingkungan non-Vercel.
*/
Schedule::command('monitoring:daily')->daily()->at('22:30');
