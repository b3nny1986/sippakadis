<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    use HasFactory;

    protected $table = 'opd';

    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'telepon',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function kendaraan(): HasMany
    {
        return $this->hasMany(Kendaraan::class);
    }
}
