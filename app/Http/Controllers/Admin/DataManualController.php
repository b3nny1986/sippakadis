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
}
