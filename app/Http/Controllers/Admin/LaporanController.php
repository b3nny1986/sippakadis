<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\PengajuanPenetapan;
use App\Services\AuditLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(): View
    {
        return view('admin.laporan.index', [
            'laporan' => [
                ['slug' => 'rekap-kendaraan', 'label' => 'Rekap Kendaraan per OPD', 'desc' => 'Daftar seluruh kendaraan berikut unit pengelolanya.'],
                ['slug' => 'status-monitoring', 'label' => 'Monitoring Status PKB & STNK', 'desc' => 'Rekapitulasi status jatuh tempo pembayaran.'],
                ['slug' => 'jatuh-tempo', 'label' => 'Kendaraan Jatuh Tempo', 'desc' => 'Kendaraan yang masa berlakunya lewat atau mendekati jatuh tempo.'],
                ['slug' => 'penetapan', 'label' => 'Rekap Pengajuan & Penetapan', 'desc' => 'Pengajuan penetapan pajak berikut statusnya per tahun pajak.'],
            ],
        ]);
    }

    public function export(Request $request, string $slug, string $format)
    {
        abort_unless(in_array($format, ['xlsx', 'pdf'], true), 404, 'Format tidak didukung.');

        [$headers, $rows, $title] = $this->dataset($slug, $request);

        abort_if(empty($rows), 404, 'Tidak ada data untuk laporan ini.');

        $filename = 'laporan-' . $slug . '-' . now()->format('YmdHis');

        if ($format === 'xlsx') {
            return Excel::download(new class($headers, $rows, $title) implements FromArray, WithHeadings, ShouldAutoSize
            {
                public function __construct(private array $headers, private array $rows, private string $title) {}

                public function array(): array
                {
                    return $this->rows;
                }

                public function headings(): array
                {
                    return $this->headers;
                }
            }, $filename . '.xlsx');
        }

        $pdf = Pdf::loadView('admin.laporan.pdf', compact('title', 'headers', 'rows'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }

    private function dataset(string $slug, Request $request): array
    {
        return match ($slug) {
            'rekap-kendaraan' => $this->rekapKendaraan($request),
            'status-monitoring' => $this->statusMonitoring(),
            'jatuh-tempo' => $this->jatuhTempo($request),
            'penetapan' => $this->penetapan($request),
            default => throw new \InvalidArgumentException('Laporan tidak dikenal: ' . $slug),
        };
    }

    private function rekapKendaraan(Request $request): array
    {
        $rows = Kendaraan::query()
            ->with('opd')
            ->when($request->filled('opd_id'), fn (Builder $q) => $q->where('opd_id', $request->integer('opd_id')))
            ->orderBy('nopol')
            ->get();

        $data = $rows->map(fn (Kendaraan $k) => [
            $k->opd?->nama ?? '-',
            $k->nopol,
            $k->no_uang,
            $k->merk,
            $k->tipe,
            $k->tahun,
            $k->status?->nama,
        ]);

        return [
            ['OPD', 'NOPOL', 'No. Uang', 'Merk', 'Tipe', 'Tahun', 'Status'],
            $data->toArray(),
            'Rekap Kendaraan per OPD',
        ];
    }

    private function statusMonitoring(): array
    {
        $rows = collect([
            'LEWAT', 'HARI_H', 'H1', 'H7', 'H14', 'H30', 'AMAN',
        ])->map(function ($status) {
            $q = fn (string $kolom) => Kendaraan::where($kolom, $status)->count();

            return [$status, $q('pkb_status'), $q('stnk_status')];
        });

        return [
            ['Status', 'Jumlah PKB', 'Jumlah STNK'],
            $rows->toArray(),
            'Monitoring Status PKB & STNK',
        ];
    }

    private function jatuhTempo(Request $request): array
    {
        $batas = max(0, (int) $request->integer('days', 90));

        $rows = Kendaraan::query()
            ->with('opd')
            ->where(function (Builder $q) use ($batas) {
                $q->where(fn ($k) => $k->whereNotNull('masa_berlaku_pkb')
                    ->where('masa_berlaku_pkb', '<=', now()->addDays($batas)))
                    ->orWhere(fn ($k) => $k->whereNotNull('masa_berlaku_stnk')
                        ->where('masa_berlaku_stnk', '<=', now()->addDays($batas)));
            })
            ->when($request->filled('opd_id'), fn (Builder $q) => $q->where('opd_id', $request->integer('opd_id')))
            ->orderByRaw('COALESCE(masa_berlaku_pkb, masa_berlaku_stnk) ASC')
            ->get();

        $data = $rows->map(fn (Kendaraan $k) => [
            $k->nopol,
            $k->opd?->nama ?? '-',
            $k->masa_berlaku_pkb?->format('d-m-Y') ?? '-',
            $k->pkb_status ?? '-',
            $k->masa_berlaku_stnk?->format('d-m-Y') ?? '-',
            $k->stnk_status ?? '-',
            number_format($k->nilai_pkb ?? 0, 0, ',', '.'),
        ]);

        return [
            ['NOPOL', 'OPD', 'Masa Berlaku PKB', 'Status PKB', 'Masa Berlaku STNK', 'Status STNK', 'Nilai PKB'],
            $data->toArray(),
            'Kendaraan Jatuh Tempo (≤ ' . $batas . ' hari)',
        ];
    }

    private function penetapan(Request $request): array
    {
        $rows = PengajuanPenetapan::query()
            ->with('kendaraan', 'opd')
            ->when($request->filled('tahun'), fn (Builder $q) => $q->where('tahun_pajak', $request->integer('tahun')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')))
            ->latest()
            ->get();

        $data = $rows->map(fn (PengajuanPenetapan $p) => [
            $p->nomor_penetapan ?? '-',
            $p->kendaraan?->nopol ?? '-',
            $p->opd?->nama ?? '-',
            $p->tahun_pajak,
            $p->status,
            $p->created_at?->format('d-m-Y'),
        ]);

        return [
            ['No. Penetapan', 'NOPOL', 'OPD', 'Tahun Pajak', 'Status', 'Tanggal Pengajuan'],
            $data->toArray(),
            'Rekap Pengajuan & Penetapan',
        ];
    }
}
