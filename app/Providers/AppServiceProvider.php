<?php

namespace App\Providers;

use App\Models\Kendaraan;
use App\Models\PengajuanPenetapan;
use App\Models\PerubahanStatus;
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
    }
}
