<?php

namespace Database\Seeders;

use App\Models\JenisKendaraan;
use Illuminate\Database\Seeder;

class JenisKendaraanSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = [
            ['kode' => 'B', 'nama' => 'Mobil Penumpang / Sedan', 'deskripsi' => 'Golongan B'],
            ['kode' => 'C', 'nama' => 'Jeep / Utility', 'deskripsi' => 'Golongan C'],
            ['kode' => 'D', 'nama' => 'Mini Bus / Barang Ringan', 'deskripsi' => 'Golongan D'],
            ['kode' => 'F', 'nama' => 'Truk / Angkutan Barang', 'deskripsi' => 'Golongan F'],
            ['kode' => 'G', 'nama' => 'Bus / Angkutan Orang', 'deskripsi' => 'Golongan G'],
            ['kode' => 'R', 'nama' => 'Sepeda Motor', 'deskripsi' => 'Golongan R'],
        ];

        foreach ($jenis as $item) {
            JenisKendaraan::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
