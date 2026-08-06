<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerubahanStatus;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerubahanStatusController extends Controller
{
    public function index(Request $request): View
    {
        $perubahan = PerubahanStatus::query()
            ->with(['kendaraan', 'statusLama', 'statusBaru', 'diajukanOleh'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('cari'), fn ($q) => $q->whereHas('kendaraan', fn ($k) => $k->cariNopol((string) $request->string('cari'))))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.perubahan-status.index', ['perubahan' => $perubahan]);
    }

    public function show(PerubahanStatus $perubahan): View
    {
        return view('admin.perubahan-status.show', [
            'perubahan' => $perubahan->load(['kendaraan', 'statusLama', 'statusBaru', 'diajukanOleh', 'disetujuiOleh']),
        ]);
    }

    public function approve(Request $request, PerubahanStatus $perubahan, AuditLogService $audit): RedirectResponse
    {
        abort_if($perubahan->status !== PerubahanStatus::MENUNGGU, 422, 'Permohonan sudah berstatus final.');

        $perubahan->kendaraan()->update(['status_id' => $perubahan->status_baru_id]);

        $perubahan->update([
            'status' => PerubahanStatus::DISETUJUI,
            'disetujui_oleh' => auth()->id(),
            'disetujui_at' => now(),
        ]);

        $audit->log('perubahan-status.approve', 'PerubahanStatus', $perubahan->id, "Approve perubahan status #{$perubahan->id}");

        return back()->with('status', 'Perubahan status disetujui dan diterapkan ke kendaraan.');
    }

    public function reject(Request $request, PerubahanStatus $perubahan, AuditLogService $audit): RedirectResponse
    {
        abort_if($perubahan->status !== PerubahanStatus::MENUNGGU, 422, 'Permohonan sudah berstatus final.');

        $data = $request->validate(['alasan_penolakan' => ['required', 'string', 'max:1000']]);

        $perubahan->update([
            'status' => PerubahanStatus::DITOLAK,
            'alasan_penolakan' => $data['alasan_penolakan'],
        ]);

        $audit->log('perubahan-status.reject', 'PerubahanStatus', $perubahan->id, "Tolak perubahan status #{$perubahan->id}");

        return back()->with('status', 'Permohonan perubahan status ditolak.');
    }
}
