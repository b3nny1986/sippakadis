<?php

namespace App\Http\Controllers;

use App\Services\MonitoringService;
use App\Services\SimpatorService;
use Illuminate\Http\Request;

/**
 * Endpoint internal yang dipanggil GitHub Actions / scheduler eksternal.
 * Dilindungi token sederhana; sebaiknya dipanggil lewat HTTPS saja.
 */
class CronController extends Controller
{
    public function daily(Request $request, MonitoringService $monitoring, SimpatorService $simpator)
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
            $batch = (int) config('monitoring.simpator.batch', 100);

            $kendaraan = \App\Models\Kendaraan::query()
                ->whereNotNull('nopol')
                ->whereIn('sumber_data', [\App\Models\Kendaraan::SUMBER_CSV, \App\Models\Kendaraan::SUMBER_MANUAL])
                ->withCount('historiScraping')
                ->orderBy('histori_scraping_count')
                ->orderBy('id')
                ->limit($batch)
                ->get();

            $hasil = [\App\Models\HistoriScraping::DITEMUKAN => 0, \App\Models\HistoriScraping::TIDAK_DITEMUKAN => 0, \App\Models\HistoriScraping::GAGAL => 0];

            foreach ($kendaraan as $item) {
                $res = $simpator->sinkronisasiKendaraan($item);
                $hasil[$res['status']]++;
            }

            $laporan['sinkronisasi'] = $hasil;
        } catch (\Throwable $e) {
            $laporan['sinkronisasi_error'] = $e->getMessage();
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
