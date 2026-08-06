<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengajuanRequest;
use App\Models\Kendaraan;
use App\Models\PengajuanPenetapan;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengajuanController extends Controller
{
    public function index(Request $request): View
    {
        $pengajuan = PengajuanPenetapan::query()
            ->with('kendaraan')
            ->where('opd_id', auth()->user()->opd_id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('opd.pengajuan.index', ['pengajuan' => $pengajuan]);
    }

    public function create(): View
    {
        // Hanya kendaraan milik OPD ini yang belum punya pengajuan aktif.
        $kendaraan = Kendaraan::query()
            ->where('opd_id', auth()->user()->opd_id)
            ->whereDoesntHave('pengajuanPenetapan', fn ($q) => $q->whereNotIn('status', [PengajuanPenetapan::DITOLAK, PengajuanPenetapan::SELESAI]))
            ->orderBy('nopol')
            ->get();

        return view('opd.pengajuan.create', [
            'kendaraan' => $kendaraan,
            'tahun' => now()->year,
        ]);
    }

    public function store(PengajuanRequest $request, AuditLogService $audit): RedirectResponse
    {
        $kendaraan = Kendaraan::findOrFail($request->integer('kendaraan_id'));

        abort_if($kendaraan->opd_id !== auth()->user()->opd_id, 403, 'Kendaraan bukan milik OPD Anda.');

        $pengajuan = PengajuanPenetapan::create([
            'kendaraan_id' => $kendaraan->id,
            'opd_id' => auth()->user()->opd_id,
            'tahun_pajak' => $request->integer('tahun_pajak'),
            'catatan' => $request->input('catatan'),
            'lampiran_path' => $this->simpanLampiran($request),
            'status' => PengajuanPenetapan::MENUNGGU,
            'diajukan_oleh' => auth()->id(),
        ]);

        $audit->log('pengajuan.store', 'PengajuanPenetapan', $pengajuan->id, "Pengajuan penetapan {$kendaraan->nopol} tahun {$pengajuan->tahun_pajak}");

        return redirect()->route('opd.pengajuan.show', $pengajuan)
            ->with('status', 'Pengajuan penetapan berhasil dikirim untuk diproses.');
    }

    public function show(PengajuanPenetapan $pengajuan): View
    {
        abort_if($pengajuan->opd_id !== auth()->user()->opd_id, 403, 'Anda tidak berhak mengakses pengajuan ini.');

        return view('opd.pengajuan.show', [
            'pengajuan' => $pengajuan->load(['kendaraan', 'diajukanOleh', 'diprosesOleh', 'disetujuiOleh', 'detailPenetapan']),
        ]);
    }

    private function simpanLampiran(Request $request): ?string
    {
        if (! $request->hasFile('lampiran') || ! $request->file('lampiran')->isValid()) {
            return null;
        }

        return $request->file('lampiran')->store('lampiran', 'local');
    }
}
