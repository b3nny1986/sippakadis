<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenetapan extends Model
{
    use HasFactory;

    protected $table = 'detail_penetapan';

    protected $fillable = [
        'penetapan_id',
        'uraian',
        'volume',
        'satuan',
        'nominal',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'volume' => 'decimal:2',
            'nominal' => 'decimal:2',
        ];
    }

    public function penetapan(): BelongsTo
    {
        return $this->belongsTo(PengajuanPenetapan::class, 'penetapan_id');
    }
}
