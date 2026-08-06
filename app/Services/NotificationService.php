<?php

namespace App\Services;

use App\Models\Kendaraan;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\DB;

/**
 * Pengelola notifikasi aplikasi.
 *
 * Saat ini seluruh notifikasi disimpan ke tabel `notifikasi` (channel Database).
 * Arsitektur channel dibuat longgar (interface + config) agar di masa depan
 * mudah ditambahkan WhatsApp / Telegram / Email tanpa mengubah pemanggil.
 */
class NotificationService
{
    /**
     * Daftar channel aktif.
     */
    public function channels(): array
    {
        return ['Database'];
    }

    /**
     * Buat notifikasi jatuh tempo PKB/STNK.
     */
    public function jatuhTempo(Kendaraan $kendaraan, string $tipe, string $status): Notifikasi
    {
        $kategori = $status; // AMAN/H30/H14/H7/H1/HARI_H/LEWAT
        $judul = match ($tipe) {
            Notifikasi::TIPE_PKB => $this->judul($status, 'PKB'),
            Notifikasi::TIPE_STNK => $this->judul($status, 'STNK'),
            default => 'Pengingat kendaraan',
        };

        $pesan = sprintf(
            'Kendaraan %s (%s, %s) %s: %s.',
            $kendaraan->nopol,
            $kendaraan->merk ?: '-',
            $kendaraan->tipe ?: '-',
            $tipe === Notifikasi::TIPE_PKB ? 'masa berlaku PKB' : 'masa berlaku STNK',
            $this->deskripsiStatus($status, $tipe)
        );

        return Notifikasi::create([
            'opd_id' => $kendaraan->opd_id,
            'kendaraan_id' => $kendaraan->id,
            'tipe' => $tipe,
            'kategori' => $kategori,
            'judul' => $judul,
            'pesan' => $pesan,
            'data' => [
                'nopol' => $kendaraan->nopol,
                'masa_berlaku' => $tipe === Notifikasi::TIPE_PKB
                    ? $kendaraan->masa_berlaku_pkb?->toDateString()
                    : $kendaraan->masa_berlaku_stnk?->toDateString(),
            ],
            'channel' => Notifikasi::CHANNEL_DATABASE,
            'is_read' => false,
        ]);
    }

    /**
     * Cek apakah notifikasi dengan kombinasi yang sama sudah dibuat baru-baru ini.
     */
    public function sudahAdaBaruBaruIni(Kendaraan $kendaraan, string $tipe, string $status): bool
    {
        $interval = (int) config('monitoring.notifikasi_min_interval_hari', 1);

        return Notifikasi::where('kendaraan_id', $kendaraan->id)
            ->where('tipe', $tipe)
            ->where('kategori', $status)
            ->where('created_at', '>=', now()->subDays($interval))
            ->exists();
    }

    public function tandaiDibaca(Notifikasi $notifikasi): void
    {
        $notifikasi->update(['is_read' => true, 'read_at' => now()]);
    }

    private function judul(string $status, string $tipe): string
    {
        return match ($status) {
            'LEWAT' => "Kendaraan {$tipe} LEWAT JATUH TEMPO",
            'HARI_H' => "Kendaraan {$tipe} Jatuh Tempo Hari Ini",
            'H1' => "Kendaraan {$tipe} Jatuh Tempo H-1",
            'H7' => "Kendaraan {$tipe} Jatuh Tempo H-7",
            'H14' => "Kendaraan {$tipe} Jatuh Tempo H-14",
            'H30' => "Kendaraan {$tipe} Jatuh Tempo H-30",
            default => "Status {$tipe} kendaraan",
        };
    }

    private function deskripsiStatus(string $status, string $tipe): string
    {
        return match ($status) {
            'LEWAT' => 'telah melewati jatuh tempo',
            'HARI_H' => 'jatuh tempo hari ini',
            'H1' => 'jatuh tempo besok (H-1)',
            'H7' => 'akan jatuh tempo dalam 7 hari',
            'H14' => 'akan jatuh tempo dalam 14 hari',
            'H30' => 'akan jatuh tempo dalam 30 hari',
            default => 'aman',
        };
    }
}
