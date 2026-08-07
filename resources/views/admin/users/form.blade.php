<x-layout title="{{ $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna' }}">
    <div class="mx-auto max-w-2xl">
        <x-card title="{{ $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna' }}">
            <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-4" x-data="{ roleId: {{ old('role_id', $user->role_id ?? 1) }} }">
                @csrf
                @if ($user->exists) @method('PUT') @endif

                <x-field label="Nama Lengkap" name="name" :value="$user->name" required />
                <x-field label="Email" name="email" type="email" :value="$user->email" required />
                <x-field label="No. HP" name="phone" :value="$user->phone" />

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Role</label>
                    <select name="role_id" x-model.number="roleId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div x-show="roleId === {{ $opdRoleId ?? $roles->firstWhere('slug','opd')?->id }}" x-cloak>
                    <label class="mb-1 block text-sm font-medium text-slate-700">OPD</label>
                    <select name="opd_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">-- Pilih OPD --</option>
                        @foreach ($opds as $opd)
                            <option value="{{ $opd->id }}" @selected(old('opd_id', $user->opd_id) == $opd->id)>{{ $opd->nama }}</option>
                        @endforeach
                    </select>
                    @error('opd_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-field label="Password" name="password" type="password" :required="!$user->exists" :value="''" />
                    <x-field label="Konfirmasi Password" name="password_confirmation" type="password" :required="!$user->exists" :value="''" />
                </div>
                @if ($user->exists)
                    <p class="text-xs text-slate-400">Kosongkan password jika tidak ingin mengubahnya.</p>
                @endif

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Simpan</button>
                </div>
            </form>
        </x-card>
    </div>
</x-layout>
