<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoriScraping;
use App\Models\Kendaraan;
use App\Models\LogSinkronisasi;
use App\Services\AuditLogService;
use App\Services\SimpatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SinkronisasiController extends Controller
{
    public function index(Request $request): View
    {
        $logs = LogSinkronisasi::query()
            ->with('dijalankanOleh')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('cari'), fn ($q) => $q->where('nopol', 'ilike', '%' . $request->string('cari') . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $today = now()->toDateString();

        return view('admin.sinkronisasi.index', [
            'logs' => $logs,
            'riwayatHariIni' => LogSinkronisasi::whereDate('created_at', $today)->count(),
            'berhasilHariIni' => LogSinkronisasi::whereDate('created_at', $today)
                ->where('status', LogSinkronisasi::DITEMUKAN)->count(),
            'antrian' => Kendaraan::whereNotNull('nopol')
                ->whereIn('sumber_data', [Kendaraan::SUMBER_CSV, Kendaraan::SUMBER_MANUAL])
                ->count(),
        ]);
    }

    public function jalankan(Request $request, SimpatorService $simpator, AuditLogService $audit): RedirectResponse
    {
        $batch = (int) config('monitoring.simpator.batch', 100);

        // Prioritaskan yang belum pernah diskrap (histori_scraping kosong) lalu id terkecil.
        $kendaraan = Kendaraan::query()
            ->whereNotNull('nopol')
            ->whereIn('sumber_data', [Kendaraan::SUMBER_CSV, Kendaraan::SUMBER_MANUAL])
            ->withCount('historiScraping')
            ->orderBy('histori_scraping_count')
            ->orderBy('id')
            ->limit($batch)
            ->get();

        if ($kendaraan->isEmpty()) {
            return back()->with('info', 'Tidak ada kendaraan yang memerlukan sinkronisasi.');
        }

        $hasil = [
            HistoriScraping::DITEMUKAN => 0,
            HistoriScraping::TIDAK_DITEMUKAN => 0,
            HistoriScraping::GAGAL => 0,
        ];

        foreach ($kendaraan as $item) {
            $res = $simpator->sinkronisasiKendaraan($item);
            $hasil[$res['status']]++;
        }

        $audit->log('sinkronisasi.massal', 'Sinkronisasi', null, sprintf(
            'Sinkronisasi massal: %d ditemukan, %d tidak ditemukan, %d gagal',
            $hasil[HistoriScraping::DITEMUKAN],
            $hasil[HistoriScraping::TIDAK_DITEMUKAN],
            $hasil[HistoriScraping::GAGAL]
        ));

        return back()->with('status', sprintf(
            'Sinkronisasi selesai: %d ditemukan, %d tidak ditemukan, %d gagal.',
            $hasil[HistoriScraping::DITEMUKAN],
            $hasil[HistoriScraping::TIDAK_DITEMUKAN],
            $hasil[HistoriScraping::GAGAL]
        ));
    }
}
