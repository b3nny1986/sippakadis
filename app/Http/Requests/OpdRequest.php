<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->slug === 'admin';
    }

    public function rules(): array
    {
        $opdId = $this->route('opd')?->id;

        return [
            'kode' => ['required', 'string', 'max:50', Rule::unique('opd', 'kode')->ignore($opdId)],
            'nama' => ['required', 'string', 'max:255', Rule::unique('opd', 'nama')->ignore($opdId)],
            'alamat' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
        ];
    }
}
