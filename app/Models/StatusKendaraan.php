<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusKendaraan extends Model
{
    use HasFactory;

    protected $table = 'status_kendaraan';

    protected $fillable = [
        'kode',
        'nama',
        'warna_badge',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function kendaraan(): HasMany
    {
        return $this->hasMany(Kendaraan::class, 'status_id');
    }
}
