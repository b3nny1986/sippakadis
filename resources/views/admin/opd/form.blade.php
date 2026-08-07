<x-layout title="{{ $opd->exists ? 'Edit OPD' : 'Tambah OPD' }}">
    <div class="mx-auto max-w-2xl">
        <x-card title="{{ $opd->exists ? 'Edit OPD' : 'Tambah OPD' }}">
            <form method="POST" action="{{ $opd->exists ? route('admin.opd.update', $opd) : route('admin.opd.store') }}" class="space-y-4"
                  x-data="{ kode: {{ Js::from(old('kode', $opd->kode)) }}, nama: {{ Js::from(old('nama', $opd->nama)) }}, generateKode() { if (!this.kode) { this.kode = 'OPD-' + this.nama.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 46); } } }">
                @csrf
                @if ($opd->exists) @method('PUT') @endif

                <div>
                    <label for="kode" class="mb-1 block text-sm font-medium text-slate-700">Kode OPD</label>
                    <input id="kode" type="text" name="kode" x-model="kode"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
                    <p class="mt-1 text-xs text-slate-400">Terisi otomatis dari nama; bisa diubah.</p>
                    @error('kode')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama" class="mb-1 block text-sm font-medium text-slate-700">Nama OPD<span class="text-red-500">*</span></label>
                    <input id="nama" type="text" name="nama" x-model="nama" x-on:input="generateKode()"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
                    @error('nama')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <x-field label="Alamat" name="alamat" :value="$opd->alamat" />
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Email" name="email" :value="$opd->email" />
                    <x-field label="Telepon" name="telepon" :value="$opd->telepon" />
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.opd.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Simpan</button>
                </div>
            </form>
        </x-card>
    </div>
</x-layout>
