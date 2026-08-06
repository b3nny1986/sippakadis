<x-layout title="{{ $opd->exists ? 'Edit OPD' : 'Tambah OPD' }}">
    <div class="mx-auto max-w-2xl">
        <x-card title="{{ $opd->exists ? 'Edit OPD' : 'Tambah OPD' }}">
            <form method="POST" action="{{ $opd->exists ? route('admin.opd.update', $opd) : route('admin.opd.store') }}" class="space-y-4">
                @csrf
                @if ($opd->exists) @method('PUT') @endif

                <x-field label="Kode OPD" name="kode" :value="$opd->kode" required />
                <x-field label="Nama OPD" name="nama" :value="$opd->nama" required />
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
