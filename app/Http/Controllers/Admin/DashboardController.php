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
            'ringkasan' => $service->ringkasan($scopeOpd),
            'rekapMonitoring' => $service->rekapMonitoring($scopeOpd),
            'rekapStatus' => $service->rekapStatus($scopeOpd),
            'rekapPerOpd' => $service->rekapPerOpd($scopeOpd),
            'perStatus' => $service->kendaraanPerStatus($scopeOpd),
            'perOpd' => $service->kendaraanPerOpd($scopeOpd),
            'rekapJatuhTempo' => $service->rekapJatuhTempo($scopeOpd),
            'rekapPengajuan' => $service->rekapPengajuan($scopeOpd),
            'rekapPenetapan' => $service->rekapPenetapan($scopeOpd, 6),
            'statistikPembayaran' => $service->statistikPembayaran($scopeOpd),
            'isAdmin' => (bool) ($user?->role?->slug === 'admin'),
        ]);
    }
}
