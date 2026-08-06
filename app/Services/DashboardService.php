<?php

namespace App\Services;

use App\Models\Kendaraan;
use App\Models\Opd;
use App\Models\PengajuanPenetapan;
use App\Models\StatusKendaraan;
use App\Support\Monitoring;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Agregasi data untuk dashboard Admin & OPD.
 * Semua metode menerima opdId agar kendaraan OPD bisa dibatasi (scoping).
 */
class DashboardService
{
    /**
     * Ringkasan kartu KPI.
     */
    public function ringkasan(?int $opdId = null): array
    {
        $kendaraan = Kendaraan::query()->tap(fn ($q) => $this->scopeOpd($q, $opdId));

        $totalKendaraan = (clone $kendaraan)->count();
        $menungguVerifikasi = (clone $kendaraan)->menungguVerifikasi()->count();

        $statusCounts = (clone $kendaraan)
            ->selectRaw('status_id, count(*) as jumlah')
            ->groupBy('status_id')
            ->pluck('jumlah', 'status_id');

        $statusLabels = StatusKendaraan::pluck('nama', 'id');

        $hitungStatus = fn (array $kode) => $statusCounts
            ->only(
                StatusKendaraan::whereIn('kode', $kode)->pluck('id')
            )
            ->sum();

        $byPkb = $this->hitungMonitoring('pkb', $opdId);

        $pengajuanMenunggu = PengajuanPenetapan::query()
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->where('status', PengajuanPenetapan::MENUNGGU)
            ->count();

        return [
            'total_kendaraan' => $totalKendaraan,
            'total_opd' => Opd::where('is_active', true)->count(),
            'kendaraan_aktif' => $hitungStatus(['aktif']),
            'kendaraan_rusak' => $hitungStatus(['rusak-ringan', 'rusak-berat']),
            'kendaraan_hilang' => $hitungStatus(['hilang']),
            'kendaraan_dijual' => $hitungStatus(['dijual']),
            'kendaraan_hibah' => $hitungStatus(['hibah']),
            'kendaraan_lelang' => $hitungStatus(['lelang']),
            'menunggu_verifikasi' => $menungguVerifikasi,
            'pengajuan_menunggu' => $pengajuanMenunggu,
            'pkb_h30' => $byPkb['H30'] ?? 0,
            'pkb_h14' => $byPkb['H14'] ?? 0,
            'pkb_h7' => $byPkb['H7'] ?? 0,
            'pkb_h1' => $byPkb['H1'] ?? 0,
            'pkb_harin_h' => $byPkb['HARI_H'] ?? 0,
            'pkb_lewat' => $byPkb['LEWAT'] ?? 0,
            'stnk_lewat' => $this->hitungMonitoring('stnk', $opdId)['LEWAT'] ?? 0,
            'status_labels' => $statusLabels,
            'status_aktif_id' => StatusKendaraan::where('kode', 'aktif')->value('id'),
        ];
    }

    /**
     * Rekap kendaraan per status unit (status_kendaraan) untuk kartu rekap.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function rekapStatus(?int $opdId = null, int $perPage = 12): LengthAwarePaginator
    {
        return StatusKendaraan::query()
            ->withCount(['kendaraan' => fn ($q) => $this->scopeOpd($q, $opdId)])
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * Jumlah kendaraan per status (untuk doughnut chart).
     */
    public function kendaraanPerStatus(?int $opdId = null): array
    {
        $labels = [];
        $data = [];

        $rows = Kendaraan::query()
            ->tap(fn ($q) => $this->scopeOpd($q, $opdId))
            ->join('status_kendaraan', 'status_kendaraan.id', '=', 'kendaraan.status_id')
            ->selectRaw('status_kendaraan.nama as nama, count(*) as jumlah')
            ->groupBy('status_kendaraan.nama')
            ->orderByDesc('jumlah')
            ->get();

        foreach ($rows as $row) {
            $labels[] = $row->nama;
            $data[] = (int) $row->jumlah;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Jumlah kendaraan per OPD (bar chart), batas atas topN.
     */
    public function kendaraanPerOpd(?int $opdId = null, int $topN = 15): array
    {
        $labels = [];
        $data = [];

        $rows = Kendaraan::query()
            ->tap(fn ($q) => $this->scopeOpd($q, $opdId))
            ->join('opd', 'opd.id', '=', 'kendaraan.opd_id')
            ->selectRaw('opd.nama as nama, count(*) as jumlah')
            ->groupBy('opd.nama')
            ->orderByDesc('jumlah')
            ->limit($topN)
            ->get();

        foreach ($rows as $row) {
            $labels[] = $this->singkatNama($row->nama);
            $data[] = (int) $row->jumlah;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Rekap jatuh tempo bulanan 12 bulan ke depan (PKB & STNK).
     *
     * @return array{labels:array, pkb:array, stnk:array}
     */
    public function rekapJatuhTempo(?int $opdId = null, int $bulan = 12): array
    {
        $today = CarbonImmutable::today()->startOfMonth();
        $labels = [];
        $pkb = [];
        $stnk = [];

        for ($i = 0; $i < $bulan; $i++) {
            $labels[] = $today->copy()->addMonths($i)->translatedFormat('M Y');
            $pkb[] = 0;
            $stnk[] = 0;
        }

        Kendaraan::query()
            ->tap(fn ($q) => $this->scopeOpd($q, $opdId))
            ->whereBetween('masa_berlaku_pkb', [$today->toDateString(), $today->copy()->addMonths($bulan - 1)->endOfMonth()->toDateString()])
            ->orWhereBetween('masa_berlaku_stnk', [$today->toDateString(), $today->copy()->addMonths($bulan - 1)->endOfMonth()->toDateString()])
            ->select(['masa_berlaku_pkb', 'masa_berlaku_stnk'])
            ->get()
            ->each(function ($k) use (&$pkb, &$stnk, $today) {
                if ($k->masa_berlaku_pkb) {
                    $idx = $today->diffInMonths($k->masa_berlaku_pkb->copy()->startOfMonth());
                    if ($idx >= 0 && $idx < count($pkb)) {
                        $pkb[$idx]++;
                    }
                }

                if ($k->masa_berlaku_stnk) {
                    $idx = $today->diffInMonths($k->masa_berlaku_stnk->copy()->startOfMonth());
                    if ($idx >= 0 && $idx < count($stnk)) {
                        $stnk[$idx]++;
                    }
                }
            });

        return ['labels' => $labels, 'pkb' => $pkb, 'stnk' => $stnk];
    }

    /**
     * Rekap pengajuan penetapan berdasarkan status.
     */
    public function rekapPengajuan(?int $opdId = null): array
    {
        $rows = PengajuanPenetapan::query()
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $statuses = PengajuanPenetapan::STATUSES;

        return [
            'labels' => $statuses,
            'data' => array_map(fn ($s) => (int) ($rows[$s] ?? 0), $statuses),
        ];
    }

    /**
     * Rekap penetapan yang disetujui per bulan (line chart).
     */
    public function rekapPenetapan(?int $opdId = null, int $bulan = 12): array
    {
        $today = CarbonImmutable::today()->startOfMonth();
        $labels = [];
        $data = [];

        for ($i = $bulan - 1; $i >= 0; $i--) {
            $labels[] = $today->copy()->subMonths($i)->translatedFormat('M Y');
            $data[] = 0;
        }

        PengajuanPenetapan::query()
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->whereNotNull('disetujui_at')
            ->select(['disetujui_at'])
            ->get()
            ->each(function ($p) use (&$data, $today, $bulan) {
                $idx = $today->copy()->subMonths($bulan - 1)->diffInMonths($p->disetujui_at->startOfMonth());
                if ($idx >= 0 && $idx < count($data)) {
                    $data[$idx]++;
                }
            });

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Estimasi statistik pembayaran: total nilai PKB yang jatuh tempo per bulan.
     */
    public function statistikPembayaran(?int $opdId = null, int $bulan = 12): array
    {
        $today = CarbonImmutable::today()->startOfMonth();
        $labels = [];
        $data = [];

        for ($i = 0; $i < $bulan; $i++) {
            $labels[] = $today->copy()->addMonths($i)->translatedFormat('M Y');
            $data[] = 0;
        }

        Kendaraan::query()
            ->tap(fn ($q) => $this->scopeOpd($q, $opdId))
            ->whereNotNull('masa_berlaku_pkb')
            ->whereBetween('masa_berlaku_pkb', [$today->toDateString(), $today->copy()->addMonths($bulan - 1)->endOfMonth()->toDateString()])
            ->select(['masa_berlaku_pkb', 'nilai_pkb'])
            ->get()
            ->each(function ($k) use (&$data, $today) {
                $idx = $today->diffInMonths($k->masa_berlaku_pkb->copy()->startOfMonth());
                if ($idx >= 0 && $idx < count($data)) {
                    $data[$idx] += (float) $k->nilai_pkb;
                }
            });

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Rekap jatuh tempo gabungan PKB & STNK per status.
     *
     * Jumlah per status dihitung dengan logika OR (pkb_status = X ATAU
     * stnk_status = X) agar konsisten dengan filter status_monitoring pada
     * halaman /kendaraan — jumlah di kartu selalu sama dengan baris daftar.
     *
     * @return array<string, array{total:int, pkb:int, stnk:int}>
     */
    public function rekapMonitoring(?int $opdId = null): array
    {
        $statuses = ['LEWAT', 'HARI_H', 'H1', 'H7', 'H14', 'H30'];
        $byPkb = $this->hitungMonitoring('pkb', $opdId);
        $byStnk = $this->hitungMonitoring('stnk', $opdId);

        $rekap = [];

        foreach ($statuses as $status) {
            $jumlah = Kendaraan::query()
                ->tap(fn ($q) => $this->scopeOpd($q, $opdId))
                ->jatuhTempo($status)
                ->count();

            $rekap[$status] = [
                'total' => (int) $jumlah,
                'pkb' => (int) ($byPkb[$status] ?? 0),
                'stnk' => (int) ($byStnk[$status] ?? 0),
            ];
        }

        return $rekap;
    }

    /**
     * Rekap per OPD (ter-paginasi): total kendaraan dan jumlah yang lewat
     * jatuh tempo. Untuk role OPD hanya berisi OPD-nya sendiri.
     */
    public function rekapPerOpd(?int $opdId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Opd::query()
            ->when($opdId, fn ($q) => $q->where('id', $opdId))
            ->where('is_active', true)
            ->withCount('kendaraan')
            ->withCount(['kendaraan as lewat_count' => fn ($q) => $q->jatuhTempo('LEWAT')])
            ->orderByDesc('kendaraan_count')
            ->paginate($perPage);
    }

    /* ------------------------------------------------------------------ */
    /* Internal                                                            */
    /* ------------------------------------------------------------------ */

    private function hitungMonitoring(string $tipe, ?int $opdId): array
    {
        $kolom = $tipe === 'stnk' ? 'masa_berlaku_stnk' : 'masa_berlaku_pkb';
        $out = [];

        foreach (Monitoring::statuses() as $status) {
            $out[$status] = Kendaraan::query()
                ->tap(fn ($q) => $this->scopeOpd($q, $opdId))
                ->jatuhTempoKolum($kolom, $status)
                ->count();
        }

        return $out;
    }

    private function scopeOpd($query, ?int $opdId): void
    {
        if ($opdId) {
            $query->where('kendaraan.opd_id', $opdId);
        }
    }

    private function singkatNama(string $nama): string
    {
        $nama = str_replace(['DINAS ', 'BADAN ', 'KECAMATAN ', 'KELURAHAN ', 'SEKRETARIAT '], '', $nama);

        return mb_strlen($nama) > 28 ? mb_substr($nama, 0, 28).'...' : $nama;
    }
}
