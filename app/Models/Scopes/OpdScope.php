<?php

namespace App\Models\Scopes;

use App\Models\Kendaraan;
use App\Models\PengajuanPenetapan;
use App\Models\PerubahanStatus;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope isolasi OPD: saat user ber-role OPD login, seluruh query
 * Kendaraan, PengajuanPenetapan, dan PerubahanStatus otomatis dibatasi ke
 * OPD-nya sendiri. Admin, pengunjung publik (tanpa login), dan proses CLI
 * (cron/queue) tidak terpengaruh karena scope hanya aktif bila ada user OPD.
 */
class OpdScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user || $user->role?->slug !== Role::OPD || ! $user->opd_id) {
            return;
        }

        $opdId = $user->opd_id;

        if ($model instanceof Kendaraan) {
            $builder->where('kendaraan.opd_id', $opdId);

            return;
        }

        if ($model instanceof PengajuanPenetapan) {
            $builder->where('pengajuan_penetapan.opd_id', $opdId);

            return;
        }

        if ($model instanceof PerubahanStatus) {
            $builder->whereHas('kendaraan', fn ($q) => $q->where('kendaraan.opd_id', $opdId));
        }
    }
}
