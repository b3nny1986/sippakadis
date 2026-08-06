<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    public const TIPE_PKB = 'PKB';

    public const TIPE_STNK = 'STNK';

    public const TIPE_STATUS = 'Status';

    public const TIPE_SISTEM = 'Sistem';

    public const CHANNEL_DATABASE = 'Database';

    public const CHANNEL_WHATSAPP = 'WhatsApp';

    public const CHANNEL_TELEGRAM = 'Telegram';

    public const CHANNEL_EMAIL = 'Email';

    protected $fillable = [
        'user_id',
        'opd_id',
        'kendaraan_id',
        'tipe',
        'kategori',
        'judul',
        'pesan',
        'data',
        'channel',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeForOpd(Builder $query, int $opdId): Builder
    {
        return $query->where(fn ($q) => $q->where('opd_id', $opdId)->orWhereNull('opd_id'));
    }
}
