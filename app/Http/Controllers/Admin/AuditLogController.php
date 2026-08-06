<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\AktivitasUser;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($request->filled('aksi'), fn ($q) => $q->where('aksi', 'like', '%' . $request->string('aksi') . '%'))
            ->when($request->filled('cari'), fn ($q) => $q->where('deskripsi', 'ilike', '%' . $request->string('cari') . '%'))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $aktivitas = AktivitasUser::query()
            ->with('user')
            ->when($request->filled('cari'), fn ($q) => $q->where('keterangan', 'ilike', '%' . $request->string('cari') . '%'))
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.audit-log.index', [
            'logs' => $logs,
            'aktivitas' => $aktivitas,
        ]);
    }
}
