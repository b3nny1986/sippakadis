<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ambang Monitoring Jatuh Tempo
    |--------------------------------------------------------------------------
    | Selisih hari dihitung dari Masa Berlaku (PKB/STNK) terhadap hari ini.
    | Rentang dibuat saling lepas (non-overlap) agar status selalu tunggal.
    |
    | diff < 0           -> LEWAT
    | diff == 0          -> HARI_H
    | diff == 1          -> H1
    | 2   <= diff <= 7   -> H7
    | 8   <= diff <= 14  -> H14
    | 15  <= diff <= 30  -> H30
    | diff > 30          -> AMAN
    */
    'thresholds' => [
        'LEWAT'  => ['min' => null, 'max' => -1],
        'HARI_H' => ['min' => 0, 'max' => 0],
        'H1'     => ['min' => 1, 'max' => 1],
        'H7'     => ['min' => 2, 'max' => 7],
        'H14'    => ['min' => 8, 'max' => 14],
        'H30'    => ['min' => 15, 'max' => 30],
        'AMAN'   => ['min' => 31, 'max' => null],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sinkronisasi Simpator
    |--------------------------------------------------------------------------
    */
    'simpator' => [
        'url'               => env('SIMPATOR_URL', 'http://simpator.kaltimprov.go.id/cari.php'),
        'wilayah'           => env('SIMPATOR_WILAYAH', 'KT'),
        'timeout'           => (int) env('SIMPATOR_TIMEOUT', 30),
        'retry'             => (int) env('SIMPATOR_RETRY', 3),
        'retry_delay_ms'    => (int) env('SIMPATOR_RETRY_DELAY_MS', 1500),
        'rate_limit_ms'     => (int) env('SIMPATOR_RATE_LIMIT_MS', 1200),
        'cache_ttl_hours'   => (int) env('SIMPATOR_CACHE_TTL_HOURS', 24),
        'batch'             => (int) env('SIMPATOR_BATCH_SIZE', 100),
        'user_agent'        => env('SIMPATOR_USER_AGENT', 'Mozilla/5.0 (compatible; SIPPAKADIS/1.0)'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token cron internal
    |--------------------------------------------------------------------------
    | Dipakai endpoint POST /cron/daily. Set ke string acak di .env / Vercel.
    */
    'cron_token' => env('CRON_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Notifikasi
    |--------------------------------------------------------------------------
    | Batas minimum interval pengiriman notifikasi untuk kombinasi
    | kendaraan + tipe + kategori agar tidak terjadi spam harian.
    */
    'notifikasi_min_interval_hari' => (int) env('NOTIFIKASI_MIN_INTERVAL_HARI', 1),

];
