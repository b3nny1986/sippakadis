<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardService $service): View
    {
        $user = auth()->user();

        // OPD hanya melihat data unitnya sendiri. Pengunjung (tanpa login)
        // melihat seluruh data dan diperlakukan bukan admin.
        $scopeOpd = $user?->role?->slug === 'opd' ? $user->opd_id : null;

        return view('dashboard', [
            ...$service->dataDashboard($scopeOpd),
            'isAdmin' => (bool) ($user?->role?->slug === 'admin'),
        ]);
    }
}
