<?php

namespace App\Services;

use App\Models\JenisKendaraan;
use App\Models\Kendaraan;
use App\Models\LogSinkronisasi;
use App\Models\Opd;
use App\Models\StatusKendaraan;
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
                        'is_verifikasi' => false,
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
