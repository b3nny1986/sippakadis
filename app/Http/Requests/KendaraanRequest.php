<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KendaraanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->slug === 'admin';
    }

    public function rules(): array
    {
        return [
            'opd_id' => ['required', 'exists:opd,id'],
            'status_id' => ['required', 'exists:status_kendaraan,id'],
            'jenis_id' => ['nullable', 'exists:jenis_kendaraan,id'],
            'nopol' => ['required', 'string', 'max:20'],
            'nopol_lama' => ['nullable', 'string', 'max:20'],
            'nama_pemilik' => ['nullable', 'string', 'max:255'],
            'no_rangka' => ['nullable', 'string', 'max:255'],
            'no_mesin' => ['nullable', 'string', 'max:255'],
            'merk' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', 'string', 'max:150'],
            'tahun' => ['nullable', 'integer', 'min:1980', 'max:' . now()->year + 1],
            'warna' => ['nullable', 'string', 'max:50'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'pkb_status' => ['nullable', Rule::in(['LEWAT', 'HARI_H', 'H1', 'H7', 'H14', 'H30', 'AMAN'])],
            'stnk_status' => ['nullable', Rule::in(['LEWAT', 'HARI_H', 'H1', 'H7', 'H14', 'H30', 'AMAN'])],
            'masa_berlaku_pkb' => ['nullable', 'date'],
            'masa_berlaku_stnk' => ['nullable', 'date'],
            'nilai_pkb' => ['nullable', 'numeric', 'min:0'],
            'nilai_swdkllj' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
