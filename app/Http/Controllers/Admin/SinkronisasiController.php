<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoriScraping;
use App\Models\Kendaraan;
use App\Models\LogSinkronisasi;
use App\Services\AuditLogService;
use App\Services\ImportCsvService;
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
            ->when($request->filled('cari'), function ($q) use ($request) {
                $clean = \App\Support\NopolParser::normalize((string) $request->string('cari'));

                if ($clean !== '') {
                    $q->whereRaw("regexp_replace(nopol, '[\\s\\-.]+', '', 'g') ilike ?", ["%{$clean}%"]);
                }
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $today = now()->toDateString();

        $kendaraan = Kendaraan::query()
            ->with(['opd', 'status'])
            ->whereNotNull('nopol')
            ->whereIn('sumber_data', [Kendaraan::SUMBER_CSV, Kendaraan::SUMBER_MANUAL])
            ->withCount('historiScraping')
            ->orderBy('histori_scraping_count')
            ->orderBy('id')
            ->paginate(100)
            ->withQueryString();

        return view('admin.sinkronisasi.index', [
            'logs' => $logs,
            'kendaraan' => $kendaraan,
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
        $query = Kendaraan::query()
            ->whereNotNull('nopol')
            ->whereIn('sumber_data', [Kendaraan::SUMBER_CSV, Kendaraan::SUMBER_MANUAL])
            ->withCount('historiScraping');

        if ($request->boolean('manual')) {
            $ids = $request->input('kendaraan_ids', []);

            if (empty($ids)) {
                return back()->with('error', 'Pilih minimal satu kendaraan terlebih dahulu.');
            }

            $kendaraan = $query
                ->whereIn('id', array_values(array_map('intval', (array) $ids)))
                ->orderBy('histori_scraping_count')
                ->orderBy('id')
                ->get();
        } else {
            $batch = (int) config('monitoring.simpator.batch', 100);

            $kendaraan = $query
                ->orderBy('histori_scraping_count')
                ->orderBy('id')
                ->limit($batch)
                ->get();
        }

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
            'Sinkronisasi %d kendaraan: %d ditemukan, %d tidak ditemukan, %d gagal',
            $kendaraan->count(),
            $hasil[HistoriScraping::DITEMUKAN],
            $hasil[HistoriScraping::TIDAK_DITEMUKAN],
            $hasil[HistoriScraping::GAGAL]
        ));

        return back()->with('status', sprintf(
            'Sinkronisasi %d kendaraan selesai: %d ditemukan, %d tidak ditemukan, %d gagal.',
            $kendaraan->count(),
            $hasil[HistoriScraping::DITEMUKAN],
            $hasil[HistoriScraping::TIDAK_DITEMUKAN],
            $hasil[HistoriScraping::GAGAL]
        ));
    }

    public function template(): \Illuminate\Http\Response
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['NO POLISI', 'NO RANGKA', 'AKHIR_PKB', 'AKHIR_STNK']);
        fputcsv($stream, ['KTV 1001', 'MH4LX150GFJP05474', '31/12/2027', '31/12/2028']);
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_update_pkb.csv"',
        ]);
    }

    public function upload(Request $request, ImportCsvService $importer, AuditLogService $audit): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        try {
            $hasil = $importer->updateMasaPkb($request->file('file')->getRealPath(), auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses CSV: ' . $e->getMessage());
        }

        $audit->log('sinkronisasi.upload', 'Sinkronisasi', null, sprintf(
            'Upload CSV update masa PKB: total=%d, diperbarui=%d, tidak ditemukan=%d, tidak cocok=%d, gagal=%d',
            $hasil['total'],
            $hasil['diperbarui'],
            $hasil['tidak_ditemukan'],
            $hasil['tidak_cocok'],
            $hasil['gagal']
        ));

        if ($hasil['gagal'] > 0 || $hasil['tidak_ditemukan'] > 0 || $hasil['tidak_cocok'] > 0) {
            $detail = collect(array_slice($hasil['errors'], 0, 20))
                ->map(fn ($e) => "[{$e['nopol']}] {$e['pesan']}")
                ->implode('; ');

            return back()->with('error', sprintf(
                'CSV diproses: %d diperbarui, %d tidak ditemukan, %d tidak cocok, %d gagal.%s',
                $hasil['diperbarui'],
                $hasil['tidak_ditemukan'],
                $hasil['tidak_cocok'],
                $hasil['gagal'],
                $detail !== '' ? ' ' . $detail : ''
            ));
        }

        return back()->with('status', sprintf(
            'CSV berhasil diproses: %d dari %d kendaraan diperbarui masa PKB/STNK-nya.',
            $hasil['diperbarui'],
            $hasil['total']
        ));
    }
}
