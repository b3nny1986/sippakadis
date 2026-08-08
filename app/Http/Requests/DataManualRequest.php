<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DataManualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->slug === 'admin';
    }

    public function rules(): array
    {
        $kendaraan = $this->route('kendaraan');

        return [
            'opd_id' => ['required', 'exists:opd,id'],
            'status_id' => ['required', 'exists:status_kendaraan,id'],
            'jenis_id' => ['nullable', 'exists:jenis_kendaraan,id'],
            'nopol' => ['required', 'string', 'max:20', Rule::unique('kendaraan', 'nopol')->ignore($kendaraan?->id)],
            'nopol_lama' => ['nullable', 'string', 'max:20'],
            'nama_pemilik' => ['nullable', 'string', 'max:255'],
            'no_rangka' => ['nullable', 'string', 'max:255'],
            'no_mesin' => ['nullable', 'string', 'max:255'],
            'merk' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', 'string', 'max:150'],
            'tahun' => ['nullable', 'integer', 'min:1980', 'max:' . (now()->year + 1)],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'masa_berlaku_pkb' => ['nullable', 'date'],
            'masa_berlaku_stnk' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'opd_id.required' => 'Pilih OPD.',
            'opd_id.exists' => 'OPD tidak valid.',
            'status_id.required' => 'Pilih status kendaraan.',
            'status_id.exists' => 'Status kendaraan tidak valid.',
            'jenis_id.exists' => 'Jenis kendaraan tidak valid.',
            'nopol.required' => 'NOPOL wajib diisi.',
            'nopol.unique' => 'NOPOL sudah terdaftar.',
        ];
    }
}
