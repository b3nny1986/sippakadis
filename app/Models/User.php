<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'opd_id',
        'phone',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === Role::ADMIN;
    }

    public function isOpdUser(): bool
    {
        return $this->role?->slug === Role::OPD;
    }

    public function notifikasis(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(PengajuanPenetapan::class, 'diajukan_oleh');
    }
}
