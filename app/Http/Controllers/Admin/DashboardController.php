<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function index(DashboardService $service): Response
    {
        $user = auth()->user();

        // OPD hanya melihat data unitnya sendiri. Pengunjung (tanpa login)
        // melihat seluruh data dan diperlakukan bukan admin.
        $scopeOpd = $user?->role?->slug === 'opd' ? $user->opd_id : null;

        // Instrumentasi timing (muncul di Vercel Logs via LOG_CHANNEL=stderr).
        $t0 = microtime(true);
        $data = $service->dataDashboard($scopeOpd);
        $dataMs = round((microtime(true) - $t0) * 1000);

        $t1 = microtime(true);
        $html = view('dashboard', [
            ...$data,
            'isAdmin' => (bool) ($user?->role?->slug === 'admin'),
        ])->render();
        $renderMs = round((microtime(true) - $t1) * 1000);

        logger()->info('dashboard timing', [
            'scope' => $scopeOpd ? "opd:{$scopeOpd}" : 'semua',
            'data_ms' => $dataMs,
            'render_ms' => $renderMs,
            'cache_store' => config('cache.default'),
        ]);

        return response($html);
    }
}
