<?php

namespace App\Observers;

use App\Models\StatusKendaraan;
use App\Services\DashboardService;

class StatusKendaraanObserver
{
    public function saved(StatusKendaraan $status): void
    {
        DashboardService::buangCache();
    }

    public function deleted(StatusKendaraan $status): void
    {
        DashboardService::buangCache();
    }
}
