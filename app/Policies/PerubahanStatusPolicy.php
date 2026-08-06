<?php

namespace App\Policies;

use App\Models\PerubahanStatus;
use App\Models\User;

class PerubahanStatusPolicy
{
    public function view(User $user, PerubahanStatus $perubahan): bool
    {
        return $user->role?->slug === 'admin'
            || $user->opd_id === $perubahan->kendaraan?->opd_id;
    }

    public function create(User $user): bool
    {
        return $user->role?->slug === 'opd';
    }

    public function approve(User $user, PerubahanStatus $perubahan): bool
    {
        return $user->role?->slug === 'admin' && $perubahan->status === PerubahanStatus::MENUNGGU;
    }

    public function reject(User $user, PerubahanStatus $perubahan): bool
    {
        return $user->role?->slug === 'admin' && $perubahan->status === PerubahanStatus::MENUNGGU;
    }
}
