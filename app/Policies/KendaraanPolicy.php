<?php

namespace App\Policies;

use App\Models\Kendaraan;
use App\Models\User;

class KendaraanPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->slug, ['admin', 'opd'], true);
    }

    public function view(User $user, Kendaraan $kendaraan): bool
    {
        return $user->role?->slug === 'admin'
            || $user->opd_id === $kendaraan->opd_id;
    }

    public function update(User $user, Kendaraan $kendaraan): bool
    {
        return $user->role?->slug === 'admin';
    }

    public function sinkronisasi(User $user, Kendaraan $kendaraan): bool
    {
        return $user->role?->slug === 'admin';
    }
}
