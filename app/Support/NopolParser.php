<?php

namespace App\Support;

/**
 * Parser Nomor Polisi untuk keperluan scraping Simpator.
 *
 * Simpator mengharapkan tiga bagian terpisah:
 *   - kt     : kode wilayah (mis. "KT")
 *   - nomor  : bagian angka (maks 4 digit, mis. "1072")
 *   - seri   : huruf sebelum & sesudah angka (maks 3, mis. "V", "VP")
 *
 * Contoh:
 *   "KTV     1"   -> kt=KT, nomor=1,    seri=V
 *   "KTV  1072"   -> kt=KT, nomor=1072, seri=V
 *   "KTVP 1147"   -> kt=KT, nomor=1147, seri=VP
 */
final class NopolParser
{
    /**
     * Ubah nomor polisi menjadi bentuk kanonik (uppercase, tanpa spasi).
     */
    public static function normalize(string $nopol): string
    {
        return mb_strtoupper((string) preg_replace('/[\s\-.]+/', '', trim($nopol)));
    }

    /**
     * Ambil kode wilayah (2 huruf pertama, default "KT").
     */
    public static function wilayah(string $nopol): string
    {
        $clean = self::normalize($nopol);

        return substr($clean, 0, 2) ?: config('monitoring.simpator.wilayah', 'KT');
    }

    /**
     * Pecah nomor polisi menjadi [nomor, seri].
     *
     * @return array{nomor: string|null, seri: string|null}  null bila tidak valid
     */
    public static function parse(string $nopol): array
    {
        $clean = self::normalize($nopol);

        // Ambil bagian angka pertama dan huruf di sekitarnya.
        if (! preg_match('/^([A-Z]*)(\d+)([A-Z]*)$/', $clean, $m)) {
            return ['nomor' => null, 'seri' => null];
        }

        $seri = trim($m[1].$m[3]);

        // Nomor maksimal 4 digit sesuai form Simpator.
        $nomor = substr($m[2], 0, 4);

        return ['nomor' => $nomor, 'seri' => $seri !== '' ? $seri : null];
    }

    /**
     * Deteksi apakah nomor polisi valid (memuat bagian angka).
     */
    public static function isValid(string $nopol): bool
    {
        return self::parse($nopol)['nomor'] !== null;
    }
}
