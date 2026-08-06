<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role?->slug === 'admin';
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'opd_id' => ['nullable', 'required_if:role_id,' . $this->opdRoleId(), 'exists:opd,id'],
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function opdRoleId(): int
    {
        return (int) \App\Models\Role::query()->where('slug', 'opd')->value('id');
    }
}
