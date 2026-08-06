<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerubahanStatus extends Model
{
    use HasFactory;

    protected $table = 'perubahan_status';

    public const MENUNGGU = 'Menunggu';

    public const DISETUJUI = 'Disetujui';

    public const DITOLAK = 'Ditolak';

    public const STATUSES = [self::MENUNGGU, self::DISETUJUI, self::DITOLAK];

    protected $fillable = [
        'kendaraan_id',
        'status_lama_id',
        'status_baru_id',
        'alasan',
        'lampiran_path',
        'status',
        'diajukan_oleh',
        'disetujui_oleh',
        'disetujui_at',
        'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'disetujui_at' => 'datetime',
        ];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }

    public function statusLama(): BelongsTo
    {
        return $this->belongsTo(StatusKendaraan::class, 'status_lama_id');
    }

    public function statusBaru(): BelongsTo
    {
        return $this->belongsTo(StatusKendaraan::class, 'status_baru_id');
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
