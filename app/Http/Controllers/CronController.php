<?php

namespace App\Http\Controllers;

use App\Services\MonitoringService;
use Illuminate\Http\Request;

/**
 * Endpoint internal yang dipanggil GitHub Actions / scheduler eksternal.
 * Dilindungi token sederhana; sebaiknya dipanggil lewat HTTPS saja.
 */
class CronController extends Controller
{
    public function daily(Request $request, MonitoringService $monitoring)
    {
        $token = config('monitoring.cron_token');

        if (! $token || ! hash_equals($token, (string) $request->bearerToken())) {
            abort(401, 'Token cron tidak valid.');
        }

        $laporan = [];

        try {
            $laporan['status_diperbarui'] = $monitoring->hitungSemuaStatus();
        } catch (\Throwable $e) {
            $laporan['status_error'] = $e->getMessage();
        }

        try {
            $laporan['notifikasi'] = $monitoring->bangunNotifikasi();
        } catch (\Throwable $e) {
            $laporan['notifikasi_error'] = $e->getMessage();
        }

        return response()->json([
            'status' => 'ok',
            'waktu' => now()->toIso8601String(),
            ...$laporan,
        ]);
    }
}
