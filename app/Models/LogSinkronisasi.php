<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogSinkronisasi extends Model
{
    use HasFactory;

    protected $table = 'log_sinkronisasi';

    public const TIPE_SCRAPING = 'scraping';

    public const TIPE_IMPORT = 'import';

    public const SUKSES = 'Sukses';

    public const GAGAL = 'Gagal';

    public const DITEMUKAN = 'Ditemukan';

    public const TIDAK_DITEMUKAN = 'Tidak Ditemukan';

    protected $fillable = [
        'tipe',
        'nopol',
        'status',
        'request_json',
        'response_json',
        'pesan',
        'durasi_ms',
        'dijalankan_oleh',
    ];

    protected function casts(): array
    {
        return [
            'request_json' => 'array',
            'response_json' => 'array',
            'durasi_ms' => 'integer',
        ];
    }

    public function dijalankanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dijalankan_oleh');
    }
}
