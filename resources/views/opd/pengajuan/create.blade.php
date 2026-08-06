<x-layout title="Ajukan Penetapan">
    <div class="mx-auto max-w-2xl">
        @if ($kendaraan->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-400 shadow-sm">
                Semua kendaraan OPD Anda sudah memiliki pengajuan aktif. Silakan tunggu hingga pengajuan selesai diproses.
            </div>
        @else
            <x-card title="Ajukan Pengajuan Penetapan">
                <form method="POST" action="{{ route('opd.pengajuan.store') }}" class="space-y-4" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kendaraan</label>
                        <select name="kendaraan_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">-- Pilih Kendaraan --</option>
                            @foreach ($kendaraan as $k)
                                <option value="{{ $k->id }}" @selected(old('kendaraan_id') == $k->id)>
                                    {{ $k->nopol }} &middot; {{ $k->merk }} {{ $k->tipe }} ({{ $k->tahun ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('kendaraan_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <x-field label="Tahun Pajak" name="tahun_pajak" type="number" :value="old('tahun_pajak', $tahun)" required />

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea name="catatan" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">{{ old('catatan') }}</textarea>
                        @error('catatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Lampiran (opsional, maks 2MB)</label>
                        <input type="file" name="lampiran" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700 focus:border-brand-500 focus:outline-none">
                        @error('lampiran')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('opd.pengajuan.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Kirim Pengajuan</button>
                    </div>
                </form>
            </x-card>
        @endif
    </div>
</x-layout>
