<?php

namespace App\Providers;

use App\Models\Kendaraan;
use App\Models\Opd;
use App\Models\PengajuanPenetapan;
use App\Models\PerubahanStatus;
use App\Models\StatusKendaraan;
use App\Observers\KendaraanObserver;
use App\Observers\OpdObserver;
use App\Observers\PengajuanPenetapanObserver;
use App\Observers\PerubahanStatusObserver;
use App\Observers\StatusKendaraanObserver;
use App\Policies\KendaraanPolicy;
use App\Policies\PengajuanPenetapanPolicy;
use App\Policies\PerubahanStatusPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Kendaraan::class, KendaraanPolicy::class);
        Gate::policy(PengajuanPenetapan::class, PengajuanPenetapanPolicy::class);
        Gate::policy(PerubahanStatus::class, PerubahanStatusPolicy::class);

        Kendaraan::observe(KendaraanObserver::class);
        Opd::observe(OpdObserver::class);
        PengajuanPenetapan::observe(PengajuanPenetapanObserver::class);
        PerubahanStatus::observe(PerubahanStatusObserver::class);
        StatusKendaraan::observe(StatusKendaraanObserver::class);
    }
}
