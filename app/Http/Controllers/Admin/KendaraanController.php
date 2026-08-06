<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KendaraanRequest;
use App\Models\Kendaraan;
use App\Models\Opd;
use App\Models\StatusKendaraan;
use App\Services\AuditLogService;
use App\Services\SimpatorService;
use App\Support\Monitoring;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KendaraanController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isOpd = $user?->role?->slug === 'opd';

        $query = Kendaraan::query()
            ->with(['opd', 'status', 'jenis'])
            ->when($isOpd, fn ($q) => $q->forOpd($user->opd_id));

        $query->when($request->filled('opd_id'), fn ($q) => $q->where('opd_id', $request->integer('opd_id')))
            ->when($request->filled('status_id'), fn ($q) => $q->where('status_id', $request->integer('status_id')))
            ->when($request->filled('jenis_id'), fn ($q) => $q->where('jenis_id', $request->integer('jenis_id')))
            ->when($request->boolean('verifikasi'), fn ($q) => $q->menungguVerifikasi())
            ->when($request->filled('cari'), function ($q) use ($request) {
                $term = trim($request->string('cari'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('nopol', 'ilike', "%{$term}%")
                        ->orWhere('nopol_lama', 'ilike', "%{$term}%")
                        ->orWhere('merk', 'ilike', "%{$term}%")
                        ->orWhere('tipe', 'ilike', "%{$term}%");
                });
            })
            ->when($request->filled('status_monitoring'), fn ($q) => $q->jatuhTempo($request->string('status_monitoring')));

        return view('kendaraan.index', [
            'kendaraan' => $query->latest('updated_at')->paginate(25)->withQueryString(),
            'daftarOpd' => Opd::orderBy('nama')->get(),
            'daftarStatus' => StatusKendaraan::orderBy('id')->get(),
            'isAdmin' => (bool) ($user?->role?->slug === 'admin'),
            'isOpd' => $isOpd,
        ]);
    }

    public function show(Kendaraan $kendaraan): View
    {
        $this->authorizeKendaraan($kendaraan);

        return view('kendaraan.show', [
            'kendaraan' => $kendaraan->load([
                'opd', 'status', 'jenis', 'verifikator', 'pengajuanPenetapan',
                'perubahanStatus.statusLama', 'perubahanStatus.statusBaru',
            ]),
            'isAdmin' => auth()->user()->role?->slug === 'admin',
        ]);
    }

    public function edit(Kendaraan $kendaraan): View
    {
        return view('kendaraan.edit', [
            'kendaraan' => $kendaraan,
            'daftarStatus' => StatusKendaraan::orderBy('id')->get(),
        ]);
    }

    public function update(KendaraanRequest $request, Kendaraan $kendaraan, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validated();

        if (array_key_exists('masa_berlaku_pkb', $data)) {
            $data['pkb_status'] = Monitoring::status($data['masa_berlaku_pkb']);
        }

        if (array_key_exists('masa_berlaku_stnk', $data)) {
            $data['stnk_status'] = Monitoring::status($data['masa_berlaku_stnk']);
        }

        $kendaraan->update($data);

        $audit->log('kendaraan.update', 'Kendaraan', $kendaraan->id, "Update kendaraan {$kendaraan->nopol}");

        return redirect()->route('admin.kendaraan.show', $kendaraan)
            ->with('status', 'Data kendaraan berhasil diperbarui.');
    }

    public function verifikasi(Request $request, Kendaraan $kendaraan, AuditLogService $audit): RedirectResponse
    {
        $kendaraan->update([
            'is_verifikasi' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $audit->log('kendaraan.verifikasi', 'Kendaraan', $kendaraan->id, "Verifikasi kendaraan {$kendaraan->nopol}");

        return back()->with('status', 'Kendaraan berhasil diverifikasi.');
    }

    public function sinkronisasi(Request $request, Kendaraan $kendaraan, SimpatorService $simpator, AuditLogService $audit): RedirectResponse
    {
        if (! $kendaraan->nopol) {
            return back()->with('error', 'Nopol tidak tersedia untuk sinkronisasi.');
        }

        try {
            $hasil = $simpator->cek($kendaraan->nopol);
        } catch (\Throwable $e) {
            return back()->with('error', 'Sinkronisasi gagal: ' . $e->getMessage());
        }

        if (! $hasil['found']) {
            return back()->with('error', 'Data tidak ditemukan di Simpator.');
        }

        $data = $hasil['data'];

        $kendaraan->update([
            'pkb_status' => Monitoring::status($data['masa_berlaku_pkb'] ?? null),
            'stnk_status' => Monitoring::status($data['masa_berlaku_stnk'] ?? null),
            'masa_berlaku_pkb' => $data['masa_berlaku_pkb'] ?? $kendaraan->masa_berlaku_pkb,
            'masa_berlaku_stnk' => $data['masa_berlaku_stnk'] ?? $kendaraan->masa_berlaku_stnk,
            'nilai_pkb' => $data['nilai_pkb'] ?? $kendaraan->nilai_pkb,
            'nilai_swdkllj' => $data['nilai_swdkllj'] ?? $kendaraan->nilai_swdkllj,
            'sumber_data' => Kendaraan::SUMBER_SIMPATOR,
        ]);

        $audit->log('kendaraan.sinkronisasi', 'Kendaraan', $kendaraan->id, "Sinkronisasi Simpator {$kendaraan->nopol}");

        $pesan = $hasil['cached']
            ? 'Sinkronisasi (dari cache) berhasil.'
            : 'Sinkronisasi berhasil.';

        return back()->with('status', $pesan . ' Masa berlaku PKB: ' . ($kendaraan->masa_berlaku_pkb?->format('d-m-Y') ?? '-') . '.');
    }

    private function authorizeKendaraan(Kendaraan $kendaraan): void
    {
        abort_if(
            auth()->user()->role?->slug === 'opd' && $kendaraan->opd_id !== auth()->user()->opd_id,
            403,
            'Anda tidak berhak mengakses kendaraan OPD lain.'
        );
    }
}
