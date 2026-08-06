<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->slug === 'opd';
    }

    public function rules(): array
    {
        return [
            'kendaraan_id' => ['required', 'integer', 'exists:kendaraan,id'],
            'tahun_pajak' => ['required', 'integer', 'min:' . (now()->year - 1), 'max:' . (now()->year + 1)],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'kendaraan_id.required' => 'Pilih kendaraan terlebih dahulu.',
            'tahun_pajak.required' => 'Tahun pajak wajib diisi.',
            'lampiran.max' => 'Lampiran maksimal 2 MB.',
        ];
    }
}
