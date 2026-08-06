<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\PengajuanPenetapan;
use App\Services\AuditLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenetapanController extends Controller
{
    public function index(Request $request): View
    {
        $pengajuan = PengajuanPenetapan::query()
            ->with(['kendaraan', 'opd', 'diajukanOleh'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun_pajak', $request->integer('tahun')))
            ->when($request->filled('cari'), fn ($q) => $q->whereHas('kendaraan', fn ($k) => $k->cariNopol((string) $request->string('cari'))))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.penetapan.index', ['pengajuan' => $pengajuan]);
    }

    public function show(PengajuanPenetapan $pengajuan): View
    {
        return view('admin.penetapan.show', [
            'pengajuan' => $pengajuan->load(['kendaraan', 'opd', 'diajukanOleh', 'diprosesOleh', 'disetujuiOleh', 'detailPenetapan']),
        ]);
    }

    public function proses(Request $request, PengajuanPenetapan $pengajuan, AuditLogService $audit): RedirectResponse
    {
        abort_if($pengajuan->isFinal(), 422, 'Pengajuan sudah berstatus final.');

        $pengajuan->update([
            'status' => PengajuanPenetapan::DIPROSES,
            'diproses_oleh' => auth()->id(),
        ]);

        $audit->log('penetapan.proses', 'PengajuanPenetapan', $pengajuan->id, "Proses pengajuan #{$pengajuan->id}");

        return back()->with('status', 'Pengajuan ditandai sedang diproses.');
    }

    public function approve(Request $request, PengajuanPenetapan $pengajuan, AuditLogService $audit): RedirectResponse
    {
        abort_if($pengajuan->isFinal(), 422, 'Pengajuan sudah berstatus final.');

        $pengajuan->update([
            'status' => PengajuanPenetapan::DISETUJUI,
            'nomor_penetapan' => $this->generateNomor($pengajuan),
            'disetujui_oleh' => auth()->id(),
            'disetujui_at' => now(),
        ]);

        $audit->log('penetapan.approve', 'PengajuanPenetapan', $pengajuan->id, "Approve pengajuan #{$pengajuan->id}");

        return back()->with('status', 'Pengajuan disetujui.');
    }

    public function reject(Request $request, PengajuanPenetapan $pengajuan, AuditLogService $audit): RedirectResponse
    {
        abort_if($pengajuan->isFinal(), 422, 'Pengajuan sudah berstatus final.');

        $data = $request->validate(['alasan_penolakan' => ['required', 'string', 'max:1000']]);

        $pengajuan->update([
            'status' => PengajuanPenetapan::DITOLAK,
            'alasan_penolakan' => $data['alasan_penolakan'],
        ]);

        $audit->log('penetapan.reject', 'PengajuanPenetapan', $pengajuan->id, "Tolak pengajuan #{$pengajuan->id}: {$data['alasan_penolakan']}");

        return back()->with('status', 'Pengajuan ditolak.');
    }

    public function cetak(PengajuanPenetapan $pengajuan)
    {
        abort_if($pengajuan->status !== PengajuanPenetapan::DISETUJUI, 403, 'Cetak hanya untuk pengajuan yang disetujui.');

        $pengajuan->load(['kendaraan', 'opd', 'detailPenetapan', 'disetujuiOleh']);

        $pdf = Pdf::loadView('admin.penetapan.pdf', ['pengajuan' => $pengajuan])
            ->setPaper('a4');

        return $pdf->download('penetapan-' . $pengajuan->nomor_penetapan . '.pdf');
    }

    private function generateNomor(PengajuanPenetapan $pengajuan): string
    {
        return sprintf(
            'STP-%s-%04d-%s',
            $pengajuan->tahun_pajak,
            $pengajuan->id,
            strtoupper(\Illuminate\Support\Str::random(3))
        );
    }
}
