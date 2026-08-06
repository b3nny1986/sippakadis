<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Monitoring terhadap masa berlaku PKB / STNK.
 *
 * Menghitung status jatuh tempo berdasarkan ambang pada config/monitoring.php.
 * Dipakai oleh kendaraan.pkb_status, kendaraan.stnk_status, dashboard,
 * notifikasi, dan laporan agar selalu konsisten.
 */
final class Monitoring
{
    /**
     * Hitung status monitoring dari sebuah tanggal masa berlaku.
     *
     * @param  string|\DateTimeInterface|null  $masaBerlaku
     * @param  string|\DateTimeInterface|null  $today  tanggal acuan (default: sekarang)
     * @return string salah satu: AMAN|H30|H14|H7|H1|HARI_H|LEWAT
     */
    public static function status(mixed $masaBerlaku, mixed $today = null): string
    {
        $today ??= CarbonImmutable::today();

        if ($masaBerlaku === null || $masaBerlaku === '') {
            return 'AMAN';
        }

        $masaBerlaku = CarbonImmutable::parse($masaBerlaku)->startOfDay();
        $diff = $today->diffInDays($masaBerlaku, false); // negatif jika sudah lewat

        foreach (config('monitoring.thresholds') as $status => $range) {
            $minOk = $range['min'] === null || $diff >= $range['min'];
            $maxOk = $range['max'] === null || $diff <= $range['max'];

            if ($minOk && $maxOk) {
                return $status;
            }
        }

        return 'AMAN';
    }

    /**
     * Warna badge Tailwind untuk setiap status monitoring.
     */
    public static function badge(string $status): string
    {
        return match (strtoupper($status)) {
            'AMAN'  => 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
            'H30'   => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
            'H14'   => 'bg-yellow-100 text-yellow-800 ring-yellow-600/30',
            'H7'    => 'bg-amber-100 text-amber-800 ring-amber-600/30',
            'H1'    => 'bg-orange-100 text-orange-800 ring-orange-600/30',
            'HARI_H'=> 'bg-red-100 text-red-800 ring-red-600/30',
            'LEWAT' => 'bg-red-600 text-white ring-red-600/40',
            default => 'bg-gray-100 text-gray-700 ring-gray-600/20',
        };
    }

    /**
     * Urutan prioritas status (untuk sorting).
     */
    public static function priority(string $status): int
    {
        $order = ['LEWAT' => 0, 'HARI_H' => 1, 'H1' => 2, 'H7' => 3, 'H14' => 4, 'H30' => 5, 'AMAN' => 6];

        return $order[strtoupper($status)] ?? 99;
    }

    /**
     * Daftar status yang tersedia.
     */
    public static function statuses(): array
    {
        return array_keys(config('monitoring.thresholds'));
    }
}
