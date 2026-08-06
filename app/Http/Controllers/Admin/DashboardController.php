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

        // OPD hanya melihat data unitnya sendiri.
        $scopeOpd = $user->role?->slug === 'opd' ? $user->opd_id : null;

        return view('dashboard', [
            'ringkasan' => $service->ringkasan($scopeOpd),
            'rekapMonitoring' => $service->rekapMonitoring($scopeOpd),
            'rekapPerOpd' => $service->rekapPerOpd($scopeOpd),
            'perStatus' => $service->kendaraanPerStatus($scopeOpd),
            'perOpd' => $service->kendaraanPerOpd($scopeOpd),
            'rekapJatuhTempo' => $service->rekapJatuhTempo($scopeOpd),
            'rekapPengajuan' => $service->rekapPengajuan($scopeOpd),
            'rekapPenetapan' => $service->rekapPenetapan($scopeOpd, 6),
            'statistikPembayaran' => $service->statistikPembayaran($scopeOpd),
            'isAdmin' => $user->role?->slug === 'admin',
        ]);
    }
}
