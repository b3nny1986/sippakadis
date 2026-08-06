<?php

namespace App\Observers;

use App\Models\Opd;
use App\Services\DashboardService;

class OpdObserver
{
    public function saved(Opd $opd): void
    {
        DashboardService::buangCache();
    }

    public function deleted(Opd $opd): void
    {
        DashboardService::buangCache();
    }
}
