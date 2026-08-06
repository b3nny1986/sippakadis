<?php

namespace App\Models;

use App\Support\Monitoring;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraan';

    public const SUMBER_CSV = 'csv';

    public const SUMBER_SIMPATOR = 'simpator';

    public const SUMBER_MANUAL = 'manual';

    protected $fillable = [
        'opd_id',
        'jenis_id',
        'status_id',
        'nopol',
        'nopol_lama',
        'nama_pemilik',
        'no_rangka',
        'no_mesin',
        'merk',
        'tipe',
        'tahun',
        'warna',
        'lokasi',
        'masa_berlaku_pkb',
        'masa_berlaku_stnk',
        'pkb_status',
        'stnk_status',
        'nilai_pkb',
        'nilai_swdkllj',
        'sumber_data',
        'is_verifikasi',
        'verified_by',
        'verified_at',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'masa_berlaku_pkb' => 'date',
            'masa_berlaku_stnk' => 'date',
            'is_verifikasi' => 'boolean',
            'verified_at' => 'datetime',
            'nilai_pkb' => 'decimal:2',
            'nilai_swdkllj' => 'decimal:2',
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Relasi                                                              */
    /* ------------------------------------------------------------------ */

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisKendaraan::class, 'jenis_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusKendaraan::class, 'status_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function pengajuanPenetapan(): HasMany
    {
        return $this->hasMany(PengajuanPenetapan::class);
    }

    public function perubahanStatus(): HasMany
    {
        return $this->hasMany(PerubahanStatus::class);
    }

    public function historiScraping(): HasMany
    {
        return $this->hasMany(HistoriScraping::class);
    }

    public function notifikasis(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    /* ------------------------------------------------------------------ */
    /* Accessor monitoring                                                 */
    /* ------------------------------------------------------------------ */

    public function getPkbStatusAttribute(?string $value): string
    {
        return $value ?: Monitoring::status($this->masa_berlaku_pkb);
    }

    public function getStnkStatusAttribute(?string $value): string
    {
        return $value ?: Monitoring::status($this->masa_berlaku_stnk);
    }

    /* ------------------------------------------------------------------ */
    /* Scope                                                               */
    /* ------------------------------------------------------------------ */

    /**
     * Batasi kueri hanya pada kendaraan milik sebuah OPD.
     * Titik kunci keamanan: selalu dipakai oleh kontroler OPD.
     */
    public function scopeForOpd(Builder $query, int $opdId): Builder
    {
        return $query->where('kendaraan.opd_id', $opdId);
    }

    public function scopeMenungguVerifikasi(Builder $query): Builder
    {
        return $query->where('is_verifikasi', false);
    }

    public function scopeStatusMonitoring(Builder $query, string $tipe, string $status): Builder
    {
        $kolom = $tipe === 'stnk' ? 'stnk_status' : 'pkb_status';

        return $query->where($kolom, $status);
    }

    /**
     * Pencocokan live terhadap satu kolom masa berlaku untuk sebuah status.
     * Tidak bergantung pada kolom pkb_status/stnk_status tersimpan.
     */
    public function scopeJatuhTempoKolum(Builder $query, string $kolom, string $status, ?CarbonImmutable $today = null): Builder
    {
        $today ??= CarbonImmutable::today();
        $range = Monitoring::dateRange($status, $today);
        $from = $range['from'];
        $to = $range['to'];

        if ($status === 'AMAN') {
            return $query->where(function (Builder $sub) use ($kolom, $from) {
                $sub->whereNull($kolom)->orWhere($kolom, '>=', $from);
            });
        }

        if ($from === null) {
            return $query->whereNotNull($kolom)->where($kolom, '<=', $to);
        }

        if ($to === null) {
            return $query->where($kolom, '>=', $from);
        }

        return $query->whereBetween($kolom, [$from, $to]);
    }

    /**
     * Kendaraan yang masuk sebuah status monitoring (live, PKB ATAU STNK).
     * Konsisten dengan filter status_monitoring pada halaman /kendaraan.
     */
    public function scopeJatuhTempo(Builder $query, string $status, ?CarbonImmutable $today = null): Builder
    {
        $today ??= CarbonImmutable::today();

        return $query->where(function (Builder $q) use ($status, $today) {
            $q->where(function (Builder $sub) use ($status, $today) {
                $sub->jatuhTempoKolum('masa_berlaku_pkb', $status, $today);
            })->orWhere(function (Builder $sub) use ($status, $today) {
                $sub->jatuhTempoKolum('masa_berlaku_stnk', $status, $today);
            });
        });
    }
}
