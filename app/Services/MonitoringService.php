<?php

namespace App\Services;

use App\Models\Kendaraan;
use App\Models\Notifikasi;
use App\Support\Monitoring;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Pemantauan jatuh tempo PKB dan STNK.
 *
 * 1. Perbarui kolom pkb_status / stnk_status pada seluruh kendaraan (dijalankan
 *    scheduler harian). Nilai kolom ini dipakai dashboard & laporan agar query cepat.
 * 2. Hasilkan notifikasi untuk kendaraan yang memasuki ambang perhatian.
 */
class MonitoringService
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    /**
     * Hitung ulang status PKB & STNK seluruh kendaraan.
     *
     * @return array{updated:int, scanned:int}
     */
    public function hitungSemuaStatus(?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::today();
        $updated = 0;
        $scanned = 0;

        Kendaraan::query()
            ->select(['id', 'masa_berlaku_pkb', 'masa_berlaku_stnk', 'pkb_status', 'stnk_status'])
            ->chunkById(200, function ($kendaraan) use (&$updated, &$scanned, $today) {
                foreach ($kendaraan as $k) {
                    $scanned++;

                    $pkb = Monitoring::status($k->masa_berlaku_pkb, $today);
                    $stnk = Monitoring::status($k->masa_berlaku_stnk, $today);

                    if ($pkb !== $k->pkb_status || $stnk !== $k->stnk_status) {
                        DB::table('kendaraan')->where('id', $k->id)->update([
                            'pkb_status' => $pkb,
                            'stnk_status' => $stnk,
                        ]);
                        $updated++;
                    }
                }
            });

        return ['updated' => $updated, 'scanned' => $scanned];
    }

    /**
     * Bangun notifikasi untuk kendaraan yang memasuki ambang perhatian.
     *
     * @return array{pkb:int,stnk:int}
     */
    public function bangunNotifikasi(?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::today();
        $count = ['pkb' => 0, 'stnk' => 0];

        $target = array_diff(Monitoring::statuses(), ['AMAN']);
        $cutoff = now()->subDays((int) config('monitoring.notifikasi_min_interval_hari', 1));

        $sudahSet = [];
        $baru = [];

        Kendaraan::query()
            ->select(['id', 'opd_id', 'nopol', 'merk', 'tipe', 'masa_berlaku_pkb', 'masa_berlaku_stnk'])
            ->chunkById(200, function ($chunk) use (&$sudahSet, &$baru, &$count, $today, $target, $cutoff) {
                $ids = $chunk->pluck('id')->all();

                Notifikasi::whereIn('kendaraan_id', $ids)
                    ->where('created_at', '>=', $cutoff)
                    ->get(['kendaraan_id', 'tipe', 'kategori'])
                    ->each(function (Notifikasi $n) use (&$sudahSet) {
                        $sudahSet[$n->kendaraan_id . ':' . $n->tipe . ':' . $n->kategori] = true;
                    });

                foreach ($chunk as $kendaraan) {
                    foreach (['PKB' => 'masa_berlaku_pkb', 'STNK' => 'masa_berlaku_stnk'] as $tipe => $kolom) {
                        $status = Monitoring::status($kendaraan->{$kolom}, $today);

                        if (! in_array($status, $target, true)) {
                            continue;
                        }

                        $key = $kendaraan->id . ':' . $tipe . ':' . $status;

                        if (isset($sudahSet[$key])) {
                            continue;
                        }

                        $sudahSet[$key] = true;
                        $count[strtolower($tipe)]++;
                        $baru[] = ['kendaraan' => $kendaraan, 'tipe' => $tipe, 'status' => $status];
                    }
                }
            });

        if ($baru !== []) {
            DB::table('notifikasi')->insert(collect($baru)->map(function (array $b) {
                $data = $this->notifications->buildJatuhTempo($b['kendaraan'], $b['tipe'], $b['status']);
                $data['data'] = json_encode($data['data'], JSON_UNESCAPED_UNICODE);

                return $data;
            })->all());
        }

        return $count;
    }
}
