<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Http\Requests\PerubahanStatusRequest;
use App\Models\Kendaraan;
use App\Models\PerubahanStatus;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerubahanStatusController extends Controller
{
    public function create(): View
    {
        $kendaraan = Kendaraan::query()
            ->where('opd_id', auth()->user()->opd_id)
            ->whereDoesntHave('perubahanStatus', fn ($q) => $q->where('status', PerubahanStatus::MENUNGGU))
            ->orderBy('nopol')
            ->get();

        return view('opd.perubahan-status.create', ['kendaraan' => $kendaraan]);
    }

    public function store(PerubahanStatusRequest $request, AuditLogService $audit): RedirectResponse
    {
        $kendaraan = Kendaraan::findOrFail($request->integer('kendaraan_id'));

        abort_if($kendaraan->opd_id !== auth()->user()->opd_id, 403, 'Kendaraan bukan milik OPD Anda.');

        abort_if(
            $kendaraan->status_id === $request->integer('status_baru_id'),
            422,
            'Status baru sama dengan status saat ini.'
        );

        $perubahan = PerubahanStatus::create([
            'kendaraan_id' => $kendaraan->id,
            'status_lama_id' => $kendaraan->status_id,
            'status_baru_id' => $request->integer('status_baru_id'),
            'alasan' => $request->input('alasan'),
            'lampiran_path' => $request->hasFile('lampiran') ? $request->file('lampiran')->store('lampiran', 'local') : null,
            'status' => PerubahanStatus::MENUNGGU,
            'diajukan_oleh' => auth()->id(),
        ]);

        $audit->log('perubahan-status.store', 'PerubahanStatus', $perubahan->id, "Permohonan perubahan status {$kendaraan->nopol}");

        return redirect()->route('opd.kendaraan.show', $kendaraan)
            ->with('status', 'Permohonan perubahan status dikirim untuk disetujui admin.');
    }
}
