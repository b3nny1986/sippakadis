<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerubahanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->slug === 'opd';
    }

    public function rules(): array
    {
        return [
            'kendaraan_id' => ['required', 'integer', 'exists:kendaraan,id'],
            'status_baru_id' => ['required', 'integer', 'exists:status_kendaraan,id'],
            'alasan' => ['required', 'string', 'max:2000'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'status_baru_id.required' => 'Status baru wajib dipilih.',
            'alasan.required' => 'Alasan perubahan wajib diisi.',
        ];
    }
}
