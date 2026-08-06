<?php

namespace App\Observers;

use App\Models\Kendaraan;
use App\Services\DashboardService;

class KendaraanObserver
{
    public function saved(Kendaraan $kendaraan): void
    {
        DashboardService::buangCache();
    }

    public function deleted(Kendaraan $kendaraan): void
    {
        DashboardService::buangCache();
    }
}
