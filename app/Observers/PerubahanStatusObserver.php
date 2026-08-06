<?php

namespace App\Observers;

use App\Models\PerubahanStatus;
use App\Services\DashboardService;

class PerubahanStatusObserver
{
    public function saved(PerubahanStatus $perubahan): void
    {
        DashboardService::buangCache();
    }

    public function deleted(PerubahanStatus $perubahan): void
    {
        DashboardService::buangCache();
    }
}
