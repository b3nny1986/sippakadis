<?php

namespace App\Services;

use App\Models\JenisKendaraan;
use App\Models\Kendaraan;
use App\Models\LogSinkronisasi;
use App\Models\Opd;
use App\Models\StatusKendaraan;
use App\Support\Monitoring;
use App\Support\NopolParser;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportCsvService
{
    /**
     * Impor data kendaraan dari CSV master.
     *
     * OPD dibuat otomatis bila belum ada (kunci: nama). Kendaraan di-upsert
     * berdasarkan nopol. Seluruh hasil dicatat ke log_sinkronisasi (tipe=import)
     * dan audit_logs.
     *
     * @return array{total:int,created:int,updated:int,skipped:int,failed:int,errors:array}
     */
    public function import(?string $path = null, ?int $userId = null): array
    {
        $path ??= (string) env('IMPORT_CSV_PATH', base_path('data_master_sippakadis.csv'));

        $probe = @fopen($path, 'r');

        if ($probe === false) {
            throw new \InvalidArgumentException("File CSV tidak ditemukan atau tidak dapat dibaca: {$path}");
        }

        fclose($probe);

        $result = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $waktuMulai = microtime(true);

        DB::beginTransaction();

        try {
            foreach ($this->readRows($path) as $row) {
                $result['total']++;

                try {
                    // Nopol disimpan sesuai format data master (dash/spasi
                    // dipertahankan) agar cocok saat dicek ulang di Simpator.
                    $nopol = NopolParser::display($row['NO POLISI'] ?? '');

                    if ($nopol === '' || $nopol === '0') {
                        $result['skipped']++;
                        continue;
                    }

                    $opd = $this->findOrCreateOpd(trim((string) ($row['OPD'] ?? 'TIDAK DIKETAHUI')));
                    $status = $this->resolveStatus($row['STATUS_KB'] ?? null, $row['STATUS_TAGIHAN'] ?? null);
                    $jenis = $this->resolveJenis($row['JENIS_KB'] ?? null);

                    $data = [
                        'opd_id' => $opd->id,
                        'jenis_id' => $jenis?->id,
                        'status_id' => $status->id,
                        'nopol' => $nopol,
                        'nopol_lama' => $this->clean($row['NOPOL LAMA'] ?? null),
                        'nama_pemilik' => $this->clean($row['NAMA PEMILIK'] ?? null),
                        'no_rangka' => $this->clean($row['NOKA'] ?? null),
                        'no_mesin' => $this->clean($row['NOSIN'] ?? null),
                        'merk' => $this->clean($row['MEREK_KB'] ?? null),
                        'tipe' => $this->clean($row['TYPE_KB'] ?? null),
                        'tahun' => $this->parseInt($row['TAHUN_KB'] ?? null),
                        'lokasi' => $this->clean($row['LOKASI'] ?? null),
                        'masa_berlaku_pkb' => $this->parseDate($row['AKHIR_PKB'] ?? null),
                        'masa_berlaku_stnk' => $this->parseDate($row['AKHIR_STNK'] ?? null),
                        'keterangan' => $this->clean($row['KETERANGAN'] ?? null),
                        'sumber_data' => Kendaraan::SUMBER_CSV,
                    ];

                    $kendaraan = Kendaraan::where('nopol', $nopol)->first();

                    if ($kendaraan) {
                        $kendaraan->update($data);
                        $result['updated']++;
                    } else {
                        Kendaraan::create($data);
                        $result['created']++;
                    }
                } catch (Throwable $e) {
                    $result['failed']++;
                    $result['errors'][] = [
                        'nopol' => $row['NO POLISI'] ?? null,
                        'pesan' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        LogSinkronisasi::create([
            'tipe' => LogSinkronisasi::TIPE_IMPORT,
            'nopol' => null,
            'status' => $result['failed'] > 0 ? LogSinkronisasi::GAGAL : LogSinkronisasi::SUKSES,
            'pesan' => sprintf(
                'Import CSV: total=%d, dibuat=%d, diperbarui=%d, dilewati=%d, gagal=%d',
                $result['total'],
                $result['created'],
                $result['updated'],
                $result['skipped'],
                $result['failed']
            ),
            'durasi_ms' => (int) ((microtime(true) - $waktuMulai) * 1000),
            'dijalankan_oleh' => $userId,
        ]);

        return $result;
    }

    /**
     * Perbarui masa berlaku PKB/STNK dari file CSV yang diunggah admin.
     *
     * Kolom yang dibaca (case-insensitive):
     *   NO POLISI (wajib), AKHIR_PKB (opsional), AKHIR_STNK (opsional),
     *   NO RANGKA (opsional, dipakai verifikasi identitas).
     *
     * Hanya kendaraan yang sudah ada yang diperbarui (dicocokkan via nopol,
     * fallback ke nopol_lama). Bila kolom NO RANGKA terisi namun berbeda
     * dengan data tersimpan, baris dilewati sebagai tidak cocok.
     *
     * @return array{total:int, diperbarui:int, tidak_ditemukan:int, tidak_cocok:int, gagal:int, errors:array}
     */
    public function updateMasaPkb(string $path, ?int $userId = null): array
    {
        $probe = @fopen($path, 'r');

        if ($probe === false) {
            throw new \InvalidArgumentException("File CSV tidak ditemukan atau tidak dapat dibaca: {$path}");
        }

        fclose($probe);

        $result = [
            'total' => 0,
            'diperbarui' => 0,
            'tidak_ditemukan' => 0,
            'tidak_cocok' => 0,
            'gagal' => 0,
            'errors' => [],
        ];

        $waktuMulai = microtime(true);

        DB::beginTransaction();

        try {
            foreach ($this->readRows($path) as $row) {
                $result['total']++;

                try {
                    $nopol = NopolParser::display($row['NO POLISI'] ?? '');

                    if ($nopol === '' || $nopol === '0') {
                        $result['gagal']++;
                        continue;
                    }

                    $kendaraan = $this->findKendaraanByNopol($nopol);

                    if (! $kendaraan) {
                        $result['tidak_ditemukan']++;
                        $result['errors'][] = ['nopol' => $nopol, 'pesan' => 'Nopol tidak ditemukan di sistem'];
                        continue;
                    }

                    $rangkaCsv = $this->clean($row['NO RANGKA'] ?? null);

                    if ($rangkaCsv !== null && $kendaraan->no_rangka !== null
                        && NopolParser::normalize($rangkaCsv) !== NopolParser::normalize($kendaraan->no_rangka)) {
                        $result['tidak_cocok']++;
                        $result['errors'][] = ['nopol' => $nopol, 'pesan' => 'No rangka tidak cocok dengan data tersimpan'];
                        continue;
                    }

                    $pkb = $this->parseTanggal($row['AKHIR_PKB'] ?? null);
                    $stnk = $this->parseTanggal($row['AKHIR_STNK'] ?? null);

                    if ($pkb === null && $stnk === null) {
                        $result['gagal']++;
                        $result['errors'][] = ['nopol' => $nopol, 'pesan' => 'Tanggal akhir PKB/STNK tidak valid'];
                        continue;
                    }

                    $data = [];

                    if ($pkb !== null) {
                        $data['masa_berlaku_pkb'] = $pkb;
                        $data['pkb_status'] = Monitoring::status($pkb);
                    }

                    if ($stnk !== null) {
                        $data['masa_berlaku_stnk'] = $stnk;
                        $data['stnk_status'] = Monitoring::status($stnk);
                    }

                    if ($rangkaCsv !== null && $kendaraan->no_rangka === null) {
                        $data['no_rangka'] = $rangkaCsv;
                    }

                    $kendaraan->update($data);
                    $result['diperbarui']++;
                } catch (Throwable $e) {
                    $result['gagal']++;
                    $result['errors'][] = [
                        'nopol' => $row['NO POLISI'] ?? null,
                        'pesan' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        LogSinkronisasi::create([
            'tipe' => LogSinkronisasi::TIPE_IMPORT,
            'nopol' => null,
            'status' => $result['gagal'] > 0 ? LogSinkronisasi::GAGAL : LogSinkronisasi::SUKSES,
            'pesan' => sprintf(
                'Update masa PKB CSV: total=%d, diperbarui=%d, tidak ditemukan=%d, tidak cocok=%d, gagal=%d',
                $result['total'],
                $result['diperbarui'],
                $result['tidak_ditemukan'],
                $result['tidak_cocok'],
                $result['gagal']
            ),
            'durasi_ms' => (int) ((microtime(true) - $waktuMulai) * 1000),
            'dijalankan_oleh' => $userId,
        ]);

        return $result;
    }

    /**
     * Baca baris CSV secara streaming.
     */
    private function readRows(string $path): \Generator
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Gagal membuka file: {$path}");
        }

        $header = null;

        try {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                if ($header === null) {
                    $header = array_map(fn ($h) => mb_strtoupper(trim($h)), $row);
                    continue;
                }

                $assoc = [];

                foreach ($header as $i => $nama) {
                    $assoc[$nama] = $row[$i] ?? null;
                }

                yield $assoc;
            }
        } finally {
            fclose($handle);
        }
    }

    private function findOrCreateOpd(string $nama): Opd
    {
        $nama = mb_strtoupper($nama);

        return Opd::firstOrCreate(
            ['nama' => $nama],
            ['kode' => 'OPD-'.substr(md5($nama), 0, 8)]
        );
    }

    private function resolveStatus(?string $statusKb, ?string $statusTagihan): StatusKendaraan
    {
        $raw = mb_strtoupper(trim((string) $statusKb));

        $kode = match ($raw) {
            'BAIK' => 'aktif',
            'RUSAK BERAT' => 'rusak-berat',
            'HILANG' => 'hilang',
            'LELANG' => 'lelang',
            'HIBAH', 'BELANJA HIBAH' => 'hibah',
            'PINJAM PAKAI' => 'dipinjamkan',
            'TIDAK DIKETAHUI KEBERADAANYA' => 'lain-lain',
            default => 'lain-lain',
        };

        // Jika tagihan tidak aktif namun status fisik baik, kendaraan dianggap
        // tidak beroperasi secara administratif.
        if (mb_strtoupper(trim((string) $statusTagihan)) === 'TIDAK AKTIF' && $raw === 'BAIK') {
            $kode = 'tidak-beroperasi';
        }

        return StatusKendaraan::firstOrCreate(
            ['kode' => $kode],
            ['nama' => ucwords(str_replace('-', ' ', $kode))]
        );
    }

    private function resolveJenis(?string $kode): ?JenisKendaraan
    {
        $kode = mb_strtoupper(trim((string) $kode));

        if ($kode === '') {
            return null;
        }

        return JenisKendaraan::firstOrCreate(
            ['kode' => $kode],
            ['nama' => "Golongan {$kode}"]
        );
    }

    /**
     * Tanggal pada CSV master berformat MM/DD/YYYY (Amerika).
     */
    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('m/d/Y', $value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Cari kendaraan berdasarkan nopol (abaikan spasi/titik/hubung),
     * fallback ke nopol_lama bila tidak ditemukan.
     */
    private function findKendaraanByNopol(string $nopol): ?Kendaraan
    {
        $clean = NopolParser::normalize($nopol);

        if ($clean === '') {
            return null;
        }

        return Kendaraan::query()
            ->whereRaw("regexp_replace(nopol, '[\\s\\-.]+', '', '') = ?", [$clean])
            ->orWhere(function ($q) use ($clean) {
                $q->whereNotNull('nopol_lama')
                    ->whereRaw("regexp_replace(nopol_lama, '[\\s\\-.]+', '', '') = ?", [$clean]);
            })
            ->first();
    }

    /**
     * Parsing tanggal fleksibel: DD/MM/YYYY (format template upload),
     * MM/DD/YYYY (bila bulan > 12), Y-m-d, dan d-m-Y.
     * Ambiguitas (kedua bagian <= 12) dianggap DD/MM/YYYY.
     */
    private function parseTanggal(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $m)) {
            [$y, $mo, $d] = [(int) $m[1], (int) $m[2], (int) $m[3]];

            return checkdate($mo, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $mo, $d) : null;
        }

        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $value, $m)) {
            $a = (int) $m[1];
            $b = (int) $m[2];
            $y = (int) $m[3];

            if ($a > 12 && $b <= 12 && checkdate($b, $a, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $b, $a);
            }

            if ($b > 12 && $a <= 12 && checkdate($a, $b, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $a, $b);
            }

            if ($a <= 12 && $b <= 12 && checkdate($b, $a, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $b, $a);
            }
        }

        return null;
    }

    private function parseInt(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        return (int) $value;
    }

    private function clean(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
