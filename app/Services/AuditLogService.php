<?php

namespace App\Services;

use App\Models\AktivitasUser;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditLogService
{
    /**
     * Catat audit trail untuk aksi sensitif.
     */
    public function log(
        string $aksi,
        ?string $entitasTipe = null,
        ?int $entitasId = null,
        ?string $deskripsi = null,
        mixed $dataLama = null,
        mixed $dataBaru = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'entitas_tipe' => $entitasTipe,
            'entitas_id' => $entitasId,
            'deskripsi' => $deskripsi,
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }

    /**
     * Catat aktivitas pengguna (login, ekspor, cetak, dll).
     */
    public function aktivitas(string $aktivitas, ?string $detail = null, ?int $userId = null): void
    {
        AktivitasUser::create([
            'user_id' => $userId ?? Auth::id(),
            'aktivitas' => $aktivitas,
            'detail' => $detail,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }
}
