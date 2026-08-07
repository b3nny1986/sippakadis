<?php

namespace App\Models;

use App\Models\Scopes\OpdScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanPenetapan extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new OpdScope);
    }

    protected $table = 'pengajuan_penetapan';

    public const MENUNGGU = 'Menunggu';

    public const DIPROSES = 'Diproses';

    public const DISETUJUI = 'Disetujui';

    public const DITOLAK = 'Ditolak';

    public const SELESAI = 'Selesai';

    public const STATUSES = [
        self::MENUNGGU,
        self::DIPROSES,
        self::DISETUJUI,
        self::DITOLAK,
        self::SELESAI,
    ];

    protected $fillable = [
        'kendaraan_id',
        'opd_id',
        'tahun_pajak',
        'catatan',
        'lampiran_path',
        'status',
        'nomor_penetapan',
        'diajukan_oleh',
        'diproses_oleh',
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

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function detailPenetapan(): HasMany
    {
        return $this->hasMany(DetailPenetapan::class, 'penetapan_id');
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [self::DISETUJUI, self::DITOLAK, self::SELESAI], true);
    }
}
