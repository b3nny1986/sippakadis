<?php

namespace Database\Seeders;

use App\Models\StatusKendaraan;
use Illuminate\Database\Seeder;

class StatusKendaraanSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['kode' => 'aktif', 'nama' => 'Aktif', 'warna_badge' => 'emerald'],
            ['kode' => 'rusak-ringan', 'nama' => 'Rusak Ringan', 'warna_badge' => 'amber'],
            ['kode' => 'rusak-berat', 'nama' => 'Rusak Berat', 'warna_badge' => 'red'],
            ['kode' => 'hilang', 'nama' => 'Hilang', 'warna_badge' => 'gray'],
            ['kode' => 'lelang', 'nama' => 'Lelang', 'warna_badge' => 'purple'],
            ['kode' => 'hibah', 'nama' => 'Hibah', 'warna_badge' => 'blue'],
            ['kode' => 'dijual', 'nama' => 'Dijual', 'warna_badge' => 'orange'],
            ['kode' => 'dipinjamkan', 'nama' => 'Dipinjamkan', 'warna_badge' => 'teal'],
            ['kode' => 'tidak-beroperasi', 'nama' => 'Tidak Beroperasi', 'warna_badge' => 'slate'],
            ['kode' => 'mutasi', 'nama' => 'Mutasi', 'warna_badge' => 'indigo'],
            ['kode' => 'lain-lain', 'nama' => 'Lain-lain', 'warna_badge' => 'gray'],
        ];

        foreach ($statuses as $status) {
            StatusKendaraan::updateOrCreate(['kode' => $status['kode']], $status);
        }
    }
}
