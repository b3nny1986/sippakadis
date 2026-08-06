<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriScraping extends Model
{
    use HasFactory;

    protected $table = 'histori_scraping';

    public const DITEMUKAN = 'Ditemukan';

    public const TIDAK_DITEMUKAN = 'Tidak Ditemukan';

    public const GAGAL = 'Gagal';

    protected $fillable = [
        'kendaraan_id',
        'nopol',
        'status',
        'payload',
        'pkb_sebelum',
        'pkb_sesudah',
        'stnk_sebelum',
        'stnk_sesudah',
        'ada_perubahan',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'pkb_sebelum' => 'date',
            'pkb_sesudah' => 'date',
            'stnk_sebelum' => 'date',
            'stnk_sesudah' => 'date',
            'ada_perubahan' => 'boolean',
        ];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }
}
