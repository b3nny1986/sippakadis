<?php

namespace App\Services;

use App\Models\HistoriScraping;
use App\Models\Kendaraan;
use App\Models\LogSinkronisasi;
use App\Support\NopolParser;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * Scraping data pajak kendaraan dari website Simpator Bapenda Kaltim.
 *
 * Sumber: POST http://simpator.kaltimprov.go.id/cari.php
 * Parameter: kt (kode wilayah), nomor (bagian angka), seri (huruf), pkb.
 * Respons berupa HTML dengan input berisi nilai data kendaraan.
 *
 * Catatan data & etika scraping:
 *  - Rate limit antar request (default 1200 ms) untuk tidak membebani server publik.
 *  - Retry dengan backoff saat gagal/timeout.
 *  - Hasil sukses dicache 24 jam (histori_scraping) untuk menghindari request berulang.
 *  - NOKA/NOSIN/alamat dari Simpator ter-mask; nilai lengkap tetap berasal dari CSV,
 *    sehingga field tersebut TIDAK ditimpa saat sinkronisasi.
 */
class SimpatorService
{
    /** Waktu request terakhir (untuk rate limit lintas instance service). */
    private static ?float $lastRequestAt = null;

    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    /**
     * Cek nomor polisi ke Simpator.
     *
     * @return array{
     *   found: bool,
     *   cached: bool,
     *   data: array<string, mixed>,
     *   raw: array<string, string|null>
     * }
     *
     * @throws \InvalidArgumentException bila nomor polisi tidak valid
     */
    public function cek(string $nopol, bool $force = false): array
    {
        $nopol = NopolParser::normalize($nopol);
        $parsed = NopolParser::parse($nopol);

        if ($parsed['nomor'] === null) {
            throw new \InvalidArgumentException("Nomor polisi tidak valid: {$nopol}");
        }

        // Gunakan cache bila tersedia dan tidak dipaksa refresh.
        if (! $force) {
            $cached = $this->fromCache($nopol);

            if ($cached !== null) {
                return $cached;
            }
        }

        $start = microtime(true);

        try {
            $html = $this->request($nopol, $parsed);
            $raw = $this->parse($html);
            $found = $this->isFound($raw);

            $data = $found ? $this->normalize($raw) : [];

            $this->logSinkronisasi($nopol, $found, $parsed, $raw, $start);

            return [
                'found' => $found,
                'cached' => false,
                'data' => $data,
                'raw' => $raw,
            ];
        } catch (Throwable $e) {
            $this->logSinkronisasi($nopol, false, $parsed, [], $start, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Sinkronisasi satu kendaraan: cek ke Simpator lalu perbarui data bila berubah.
     *
     * @return array{status:string, perubahan:array}
     */
    public function sinkronisasiKendaraan(Kendaraan $kendaraan, bool $force = false): array
    {
        $sebelum = [
            'pkb' => $kendaraan->masa_berlaku_pkb?->toDateString(),
            'stnk' => $kendaraan->masa_berlaku_stnk?->toDateString(),
        ];

        try {
            $result = $this->cek($kendaraan->nopol, $force);
        } catch (Throwable $e) {
            HistoriScraping::create([
                'kendaraan_id' => $kendaraan->id,
                'nopol' => $kendaraan->nopol,
                'status' => HistoriScraping::GAGAL,
                'payload' => ['error' => $e->getMessage()],
            ]);

            return ['status' => HistoriScraping::GAGAL, 'perubahan' => []];
        }

        if (! $result['found']) {
            HistoriScraping::create([
                'kendaraan_id' => $kendaraan->id,
                'nopol' => $kendaraan->nopol,
                'status' => HistoriScraping::TIDAK_DITEMUKAN,
                'payload' => $result['raw'],
            ]);

            return ['status' => HistoriScraping::TIDAK_DITEMUKAN, 'perubahan' => []];
        }

        $data = $result['data'];

        // Hanya perbarui field yang aman: tanggal masa berlaku & nilai PKB.
        $update = [];

        if (isset($data['masa_berlaku_pkb']) && $data['masa_berlaku_pkb'] !== $sebelum['pkb']) {
            $update['masa_berlaku_pkb'] = $data['masa_berlaku_pkb'];
        }

        if (isset($data['masa_berlaku_stnk']) && $data['masa_berlaku_stnk'] !== $sebelum['stnk']) {
            $update['masa_berlaku_stnk'] = $data['masa_berlaku_stnk'];
        }

        if (isset($data['nilai_pkb'])) {
            $update['nilai_pkb'] = $data['nilai_pkb'];
        }

        if ($update !== []) {
            $kendaraan->update($update);
            $this->auditLog->log(
                'kendaraan.sinkronisasi',
                Kendaraan::class,
                $kendaraan->id,
                "Sinkronisasi Simpator {$kendaraan->nopol}",
                $sebelum,
                $update
            );
        }

        $sesudah = [
            'pkb' => $kendaraan->masa_berlaku_pkb?->toDateString(),
            'stnk' => $kendaraan->masa_berlaku_stnk?->toDateString(),
        ];

        $adaPerubahan = $sebelum !== $sesudah;

        HistoriScraping::create([
            'kendaraan_id' => $kendaraan->id,
            'nopol' => $kendaraan->nopol,
            'status' => HistoriScraping::DITEMUKAN,
            'payload' => $result['raw'],
            'pkb_sebelum' => $sebelum['pkb'],
            'pkb_sesudah' => $sesudah['pkb'],
            'stnk_sebelum' => $sebelum['stnk'],
            'stnk_sesudah' => $sesudah['stnk'],
            'ada_perubahan' => $adaPerubahan,
        ]);

        return ['status' => HistoriScraping::DITEMUKAN, 'perubahan' => $update];
    }

    /* ------------------------------------------------------------------ */
    /* Internal                                                            */
    /* ------------------------------------------------------------------ */

    private function request(string $nopol, array $parsed): string
    {
        $url = config('monitoring.simpator.url');
        $retry = (int) config('monitoring.simpator.retry');
        $backoff = (int) config('monitoring.simpator.retry_delay_ms');

        $client = new Client([
            'timeout' => (int) config('monitoring.simpator.timeout'),
            'connect_timeout' => 15,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => config('monitoring.simpator.user_agent'),
                'Accept' => 'text/html,application/xhtml+xml',
            ],
        ]);

        $attempt = 0;

        while (true) {
            $this->throttle();

            try {
                $response = $client->post($url, [
                    'form_params' => [
                        'kt' => config('monitoring.simpator.wilayah'),
                        'nomor' => $parsed['nomor'],
                        'seri' => $parsed['seri'] ?? '',
                        'pkb' => 'Process',
                    ],
                ]);

                $body = (string) $response->getBody();

                // Respons non-2xx tetap dianggap gagal kecuali halaman tetap menampilkan data.
                if ($response->getStatusCode() >= 500) {
                    throw new \RuntimeException("Server Simpator mengembalikan HTTP {$response->getStatusCode()} untuk {$nopol}");
                }

                return $body;
            } catch (GuzzleException $e) {
                $attempt++;

                if ($attempt > $retry) {
                    throw new \RuntimeException("Gagal mengambil data {$nopol}: {$e->getMessage()}", 0, $e);
                }

                usleep($backoff * $attempt * 1000);
            }
        }
    }

    /**
     * Rate limit antar request (millisecond).
     */
    private function throttle(): void
    {
        $rateLimit = (int) config('monitoring.simpator.rate_limit_ms');

        if (self::$lastRequestAt !== null) {
            $elapsedMs = (microtime(true) - self::$lastRequestAt) * 1000;

            if ($elapsedMs < $rateLimit) {
                usleep((int) (($rateLimit - $elapsedMs) * 1000));
            }
        }

        self::$lastRequestAt = microtime(true);
    }

    /**
     * Ekstrak nilai input dari HTML respons.
     *
     * @return array<string, string|null>
     */
    private function parse(string $html): array
    {
        $crawler = new Crawler($html);
        $fields = [
            'nopol', 'kode', 'nama', 'alamat', 'merk', 'tipe', 'thn', 'milik',
            'noka', 'nosin', 'tg_pkb', 'tg_stnk', 'pkb_pok', 'pkb_den',
            'swd_pok', 'swd_den', 'pnbp', 'tnkb', 'total',
        ];

        $values = [];

        foreach ($fields as $id) {
            $node = $crawler->filter("#{$id}");
            $values[$id] = $node->count() ? trim((string) $node->attr('value')) : null;
        }

        return $values;
    }

    /**
     * Deteksi apakah data ditemukan (nama pemilik terisi dan bukan tanggal default).
     */
    private function isFound(array $raw): bool
    {
        $nama = trim((string) ($raw['nama'] ?? ''));

        return $nama !== '' && ($raw['tg_pkb'] ?? '') !== '01-01-1970';
    }

    /**
     * Normalisasi data Simpator menjadi struktur aplikasi.
     *
     * @return array<string, mixed>
     */
    private function normalize(array $raw): array
    {
        return [
            'nopol' => trim((string) ($raw['nopol'] ?? '')),
            'kode_bayar' => trim((string) ($raw['kode'] ?? '')),
            'nama_pemilik' => trim((string) ($raw['nama'] ?? '')),
            'alamat' => trim((string) ($raw['alamat'] ?? '')),
            'merk' => trim((string) ($raw['merk'] ?? '')),
            'tipe' => trim((string) ($raw['tipe'] ?? '')),
            'tahun' => $this->toInt($raw['thn'] ?? null),
            'milik_ke' => $this->toInt($raw['milik'] ?? null),
            'no_rangka' => trim((string) ($raw['noka'] ?? '')),
            'no_mesin' => trim((string) ($raw['nosin'] ?? '')),
            'masa_berlaku_pkb' => $this->toDate($raw['tg_pkb'] ?? null),
            'masa_berlaku_stnk' => $this->toDate($raw['tg_stnk'] ?? null),
            'nilai_pkb' => $this->toDecimal($raw['pkb_pok'] ?? null),
            'nilai_denda_pkb' => $this->toDecimal($raw['pkb_den'] ?? null),
            'nilai_swdkllj' => $this->toDecimal($raw['swd_pok'] ?? null),
            'total_bayar' => $this->toDecimal($raw['total'] ?? null),
        ];
    }

    private function toInt(mixed $value): ?int
    {
        $value = trim((string) $value);

        return $value !== '' && is_numeric($value) ? (int) $value : null;
    }

    private function toDecimal(mixed $value): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // "1.234.567" atau "1234567" -> float
        $cleaned = str_replace('.', '', $value);

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * Tanggal dari Simpator berformat DD-MM-YYYY.
     */
    private function toDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '01-01-1970') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', $value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function fromCache(string $nopol): ?array
    {
        $ttl = (int) config('monitoring.simpator.cache_ttl_hours');

        $latest = HistoriScraping::where('nopol', $nopol)
            ->where('status', HistoriScraping::DITEMUKAN)
            ->latest('created_at')
            ->first();

        if ($latest && $latest->created_at->gte(now()->subHours($ttl))) {
            return [
                'found' => true,
                'cached' => true,
                'data' => $this->normalize($latest->payload ?? []),
                'raw' => $latest->payload ?? [],
            ];
        }

        return null;
    }

    private function logSinkronisasi(
        string $nopol,
        bool $found,
        array $parsed,
        array $raw,
        float $start,
        ?string $pesan = null,
    ): void {
        LogSinkronisasi::create([
            'tipe' => LogSinkronisasi::TIPE_SCRAPING,
            'nopol' => $nopol,
            'status' => $pesan ? LogSinkronisasi::GAGAL : ($found ? LogSinkronisasi::DITEMUKAN : LogSinkronisasi::TIDAK_DITEMUKAN),
            'request_json' => [
                'kt' => config('monitoring.simpator.wilayah'),
                'nomor' => $parsed['nomor'],
                'seri' => $parsed['seri'] ?? '',
            ],
            'response_json' => $raw,
            'pesan' => $pesan,
            'durasi_ms' => (int) ((microtime(true) - $start) * 1000),
            'dijalankan_oleh' => auth()->id(),
        ]);
    }
}
