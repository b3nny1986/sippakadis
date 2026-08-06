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
     * Keterangan selisih hari terhadap masa berlaku, lengkap dengan warna teks.
     *
     * Contoh: "1 hari lewat jatuh tempo" (merah), "30 hari menuju jatuh tempo"
     * (hijau), "Jatuh tempo hari ini" (merah), "Belum diisi" (abu-abu).
     *
     * @return array{teks:string, warna:string}
     */
    public static function keterangan(mixed $masaBerlaku, mixed $today = null): array
    {
        $today ??= CarbonImmutable::today();

        if ($masaBerlaku === null || $masaBerlaku === '') {
            return ['teks' => 'Belum diisi', 'warna' => 'text-slate-400'];
        }

        $masaBerlaku = CarbonImmutable::parse($masaBerlaku)->startOfDay();
        $diff = (int) $today->diffInDays($masaBerlaku, false);

        if ($diff < 0) {
            $hari = abs($diff);

            return [
                'teks' => $hari == 1 ? '1 hari lewat jatuh tempo' : "{$hari} hari lewat jatuh tempo",
                'warna' => 'font-medium text-red-600',
            ];
        }

        if ($diff === 0) {
            return ['teks' => 'Jatuh tempo hari ini', 'warna' => 'font-medium text-red-600'];
        }

        $warna = match (self::status($masaBerlaku, $today)) {
            'H1' => 'font-medium text-orange-600',
            'H7' => 'font-medium text-amber-600',
            'H14' => 'font-medium text-yellow-600',
            default => 'font-medium text-emerald-600',
        };

        return [
            'teks' => $diff == 1 ? '1 hari menuju jatuh tempo' : "{$diff} hari menuju jatuh tempo",
            'warna' => $warna,
        ];
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
     * Rentang tanggal (masa berlaku) untuk sebuah status monitoring.
     *
     * Hasil konsisten dengan status(): sebuah kolom tanggal masuk bucket
     * `status` bila nilai diffsnya berada di ambang status tsb.
     *
     * @return array{from:?string, to:?string} format Y-m-d; null = tak terbatas
     */
    public static function dateRange(string $status, mixed $today = null): array
    {
        $today ??= CarbonImmutable::today();
        $range = config('monitoring.thresholds.'.strtoupper($status), ['min' => null, 'max' => null]);

        return [
            'from' => $range['min'] === null ? null : $today->copy()->startOfDay()->addDays($range['min'])->toDateString(),
            'to' => $range['max'] === null ? null : $today->copy()->startOfDay()->addDays($range['max'])->toDateString(),
        ];
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
