<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AktivitasUser extends Model
{
    use HasFactory;

    protected $table = 'aktivitas_user';

    protected $fillable = [
        'user_id',
        'aktivitas',
        'detail',
        'ip',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
