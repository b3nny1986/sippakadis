<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataManualRequest;
use App\Models\JenisKendaraan;
use App\Models\Kendaraan;
use App\Models\Opd;
use App\Models\StatusKendaraan;
use App\Services\AuditLogService;
use App\Support\Monitoring;
use App\Support\NopolParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DataManualController extends Controller
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function index(): View
    {
        $kendaraan = Kendaraan::query()
            ->with(['opd', 'jenis', 'status'])
            ->when(request('sumber'), fn ($q, $sumber) => $q->where('sumber_data', $sumber))
            ->when(request('cari'), function ($q, $cari) {
                $q->where(function ($q2) use ($cari) {
                    $q2->where('nopol', 'like', "%{$cari}%")
                        ->orWhere('nopol_lama', 'like', "%{$cari}%")
                        ->orWhere('nama_pemilik', 'like', "%{$cari}%")
                        ->orWhere('no_rangka', 'like', "%{$cari}%")
                        ->orWhere('no_mesin', 'like', "%{$cari}%")
                        ->orWhere('merk', 'like', "%{$cari}%");
                });
            })
            ->when(request('opd_id'), fn ($q, $opdId) => $q->where('opd_id', $opdId))
            ->when(request('status_id'), fn ($q, $statusId) => $q->where('status_id', $statusId))
            ->orderBy('nopol')
            ->paginate(15)
            ->withQueryString();

        return view('admin.data-manual.index', [
            'kendaraan' => $kendaraan,
            'daftarOpd' => Opd::orderBy('nama')->get(),
            'daftarStatus' => StatusKendaraan::where('is_active', true)->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new Kendaraan(['sumber_data' => Kendaraan::SUMBER_MANUAL]));
    }

    public function store(DataManualRequest $request): RedirectResponse
    {
        $data = $this->dataForm($request->validated());
        $data['sumber_data'] = Kendaraan::SUMBER_MANUAL;

        $kendaraan = Kendaraan::create($data);

        $this->audit->log('data-manual.store', 'Kendaraan', $kendaraan->id, "Tambah kendaraan manual {$kendaraan->nopol}", null, $kendaraan->toArray());

        return redirect()
            ->route('admin.data-manual.index')
            ->with('status', "Kendaraan {$kendaraan->nopol} berhasil ditambahkan.");
    }

    public function edit(Kendaraan $kendaraan): View
    {
        return $this->formView($kendaraan);
    }

    public function update(DataManualRequest $request, Kendaraan $kendaraan): RedirectResponse
    {
        $lama = $kendaraan->toArray();
        $kendaraan->update($this->dataForm($request->validated()));

        $this->audit->log('data-manual.update', 'Kendaraan', $kendaraan->id, "Ubah kendaraan {$kendaraan->nopol}", $lama, $kendaraan->toArray());

        return redirect()
            ->route('admin.data-manual.index')
            ->with('status', "Kendaraan {$kendaraan->nopol} berhasil diperbarui.");
    }

    public function destroy(Kendaraan $kendaraan): RedirectResponse
    {
        $this->audit->log('data-manual.destroy', 'Kendaraan', $kendaraan->id, "Hapus kendaraan {$kendaraan->nopol} (sumber {$kendaraan->sumber_data})", $kendaraan->toArray(), null);
        $kendaraan->delete();

        return redirect()
            ->route('admin.data-manual.index')
            ->with('status', 'Kendaraan berhasil dihapus.');
    }

    /**
     * Unduh data master (format CSV yang kompatibel dengan import master)
     * dengan filter status, OPD, masa PKB, atau pilih semua.
     */
    public function download(Request $request): Response
    {
        $query = Kendaraan::query()
            ->with(['opd', 'jenis', 'status'])
            ->whereNotNull('nopol');

        if (! $request->boolean('pilih_semua')) {
            $query->when($request->input('opd_id'), fn ($q, $opdId) => $q->where('opd_id', $opdId))
                ->when($request->input('status_id'), fn ($q, $statusId) => $q->where('status_id', $statusId))
                ->when($request->input('masa_pkb'), fn ($q, $masaPkb) => $q->where('pkb_status', $masaPkb));
        }

        $rows = $query->orderBy('nopol')->get();

        $this->audit->log('data-manual.download', 'Kendaraan', null, sprintf('Download data master: %d baris', $rows->count()), null, [
            'pilih_semua' => $request->boolean('pilih_semua'),
            'opd_id' => $request->input('opd_id'),
            'status_id' => $request->input('status_id'),
            'masa_pkb' => $request->input('masa_pkb'),
        ]);

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF"); // BOM agar terbaca Excel

        fputcsv($stream, [
            'NO POLISI', 'NOPOL LAMA', 'OPD', 'NAMA PEMILIK', 'JENIS_KB', 'MEREK_KB',
            'TYPE_KB', 'TAHUN_KB', 'NOKA', 'NOSIN', 'AKHIR_PKB', 'AKHIR_STNK',
            'LOKASI', 'STATUS_KB', 'STATUS_TAGIHAN', 'KETERANGAN',
        ]);

        foreach ($rows as $k) {
            fputcsv($stream, $this->exportRow($k));
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="data_master_sippakadis_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    private function formView(Kendaraan $kendaraan): View
    {
        return view('admin.data-manual.form', [
            'kendaraan' => $kendaraan,
            'daftarOpd' => Opd::orderBy('nama')->get(),
            'daftarJenis' => JenisKendaraan::orderBy('nama')->get(),
            'daftarStatus' => StatusKendaraan::where('is_active', true)->orderBy('nama')->get(),
        ]);
    }

    private function dataForm(array $data): array
    {
        $data['nopol'] = NopolParser::display($data['nopol']);

        if (! empty($data['nopol_lama'])) {
            $data['nopol_lama'] = NopolParser::display($data['nopol_lama']);
        }

        $data['pkb_status'] = Monitoring::status($data['masa_berlaku_pkb'] ?? null);
        $data['stnk_status'] = Monitoring::status($data['masa_berlaku_stnk'] ?? null);

        return $data;
    }

    /**
     * Baris CSV master (round-trip dengan ImportCsvService::import()).
     * Tanggal ditulis MM/DD/YYYY sesuai format import master.
     */
    private function exportRow(Kendaraan $kendaraan): array
    {
        [$statusKb, $statusTagihan] = $this->exportStatus($kendaraan->status?->kode);

        return [
            $kendaraan->nopol,
            $kendaraan->nopol_lama ?? '',
            $kendaraan->opd?->nama ?? '',
            $kendaraan->nama_pemilik ?? '',
            $kendaraan->jenis?->kode ?? '',
            $kendaraan->merk ?? '',
            $kendaraan->tipe ?? '',
            $kendaraan->tahun ?? '',
            $kendaraan->no_rangka ?? '',
            $kendaraan->no_mesin ?? '',
            $kendaraan->masa_berlaku_pkb ? $kendaraan->masa_berlaku_pkb->format('m/d/Y') : '',
            $kendaraan->masa_berlaku_stnk ? $kendaraan->masa_berlaku_stnk->format('m/d/Y') : '',
            $kendaraan->lokasi ?? '',
            $statusKb,
            $statusTagihan,
            $kendaraan->keterangan ?? '',
        ];
    }

    /**
     * Balikkan status kendaraan ke notasi CSV master (STATUS_KB + STATUS_TAGIHAN).
     *
     * @return array{0:string, 1:string} [STATUS_KB, STATUS_TAGIHAN]
     */
    private function exportStatus(?string $kode): array
    {
        return match ($kode) {
            'aktif' => ['BAIK', 'AKTIF'],
            'rusak-berat' => ['RUSAK BERAT', 'AKTIF'],
            'hilang' => ['HILANG', 'AKTIF'],
            'lelang' => ['LELANG', 'AKTIF'],
            'hibah' => ['HIBAH', 'AKTIF'],
            'dipinjamkan' => ['PINJAM PAKAI', 'AKTIF'],
            'tidak-beroperasi' => ['BAIK', 'TIDAK AKTIF'],
            default => ['TIDAK DIKETAHUI KEBERADAANYA', 'AKTIF'],
        };
    }
}
