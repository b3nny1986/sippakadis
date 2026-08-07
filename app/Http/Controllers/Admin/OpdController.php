<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpdRequest;
use App\Models\Opd;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OpdController extends Controller
{
    public function index(Request $request): View
    {
        $opds = Opd::query()
            ->withCount('kendaraan')
            ->when($request->filled('cari'), fn ($q) => $q->where('nama', 'ilike', '%' . $request->string('cari') . '%'))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('admin.opd.index', ['opds' => $opds]);
    }

    public function create(): View
    {
        return view('admin.opd.create', ['opd' => new Opd]);
    }

    public function store(OpdRequest $request, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['kode'])) {
            $data['kode'] = $this->generateKode($data['nama']);
        }

        $opd = Opd::create($data);

        $audit->log('opd.create', 'Opd', $opd->id, "Tambah OPD {$opd->nama}");

        return redirect()->route('admin.opd.index')
            ->with('status', 'OPD berhasil ditambahkan.');
    }

    public function edit(Opd $opd): View
    {
        return view('admin.opd.edit', ['opd' => $opd]);
    }

    public function update(OpdRequest $request, Opd $opd, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['kode'])) {
            unset($data['kode']);
        }

        $opd->update($data);

        $audit->log('opd.update', 'Opd', $opd->id, "Update OPD {$opd->nama}");

        return redirect()->route('admin.opd.index')
            ->with('status', 'OPD berhasil diperbarui.');
    }

    public function destroy(Request $request, Opd $opd, AuditLogService $audit): RedirectResponse
    {
        abort_if($opd->kendaraan()->exists(), 403, 'OPD masih memiliki kendaraan, tidak dapat dihapus.');

        $audit->log('opd.delete', 'Opd', $opd->id, "Hapus OPD {$opd->nama}");

        $opd->delete();

        return back()->with('status', 'OPD berhasil dihapus.');
    }

    private function generateKode(string $nama): string
    {
        $base = mb_substr('OPD-' . Str::slug($nama, '-'), 0, 50);

        $kode = $base;
        $suffix = 2;

        while (Opd::where('kode', $kode)->exists()) {
            $kode = mb_substr($base, 0, 50 - strlen((string) $suffix)) . $suffix;
            $suffix++;
        }

        return $kode;
    }
}
