<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasUser;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        $cari = $request->string('cari')->trim()->toString();

        $logs = AuditLog::query()
            ->with('user')
            ->when($cari !== '', fn ($q) => $q
                ->where(fn ($w) => $w
                    ->where('aksi', 'like', "%{$cari}%")
                    ->orWhere('deskripsi', 'ilike', "%{$cari}%")))
            ->latest()
            ->limit(300)
            ->get()
            ->map(fn ($l) => [
                'waktu' => $l->created_at,
                'user' => $l->user?->name ?? 'Sistem',
                'jenis' => 'Audit',
                'aksi' => $l->aksi,
                'entitas' => $l->entitas_tipe ? trim(class_basename($l->entitas_tipe).' #'.$l->entitas_id) : null,
                'detail' => $l->deskripsi,
            ]);

        $aktivitas = AktivitasUser::query()
            ->with('user')
            ->when($cari !== '', fn ($q) => $q
                ->where(fn ($w) => $w
                    ->where('aktivitas', 'like', "%{$cari}%")
                    ->orWhere('detail', 'ilike', "%{$cari}%")))
            ->latest()
            ->limit(300)
            ->get()
            ->map(fn ($a) => [
                'waktu' => $a->created_at,
                'user' => $a->user?->name ?? 'Sistem',
                'jenis' => 'Aktivitas',
                'aksi' => $a->aktivitas,
                'entitas' => null,
                'detail' => $a->detail,
            ]);

        $items = $logs
            ->concat($aktivitas)
            ->sortByDesc('waktu')
            ->values();

        $perPage = 40;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        return view('admin.log.index', [
            'items' => $paginator,
            'cari' => $cari,
        ]);
    }
}
