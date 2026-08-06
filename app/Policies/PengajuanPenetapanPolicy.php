<?php

namespace App\Policies;

use App\Models\PengajuanPenetapan;
use App\Models\User;

class PengajuanPenetapanPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->slug, ['admin', 'opd'], true);
    }

    public function view(User $user, PengajuanPenetapan $pengajuan): bool
    {
        return $user->role?->slug === 'admin'
            || $user->opd_id === $pengajuan->opd_id;
    }

    public function create(User $user): bool
    {
        return $user->role?->slug === 'opd';
    }

    public function proses(User $user, PengajuanPenetapan $pengajuan): bool
    {
        return $user->role?->slug === 'admin' && ! $pengajuan->isFinal();
    }

    public function approve(User $user, PengajuanPenetapan $pengajuan): bool
    {
        return $user->role?->slug === 'admin' && ! $pengajuan->isFinal();
    }

    public function reject(User $user, PengajuanPenetapan $pengajuan): bool
    {
        return $user->role?->slug === 'admin' && ! $pengajuan->isFinal();
    }
}
