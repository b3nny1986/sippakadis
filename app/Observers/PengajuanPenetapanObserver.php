<?php

namespace App\Observers;

use App\Models\PengajuanPenetapan;
use App\Services\DashboardService;

class PengajuanPenetapanObserver
{
    public function saved(PengajuanPenetapan $pengajuan): void
    {
        DashboardService::buangCache();
    }

    public function deleted(PengajuanPenetapan $pengajuan): void
    {
        DashboardService::buangCache();
    }
}
