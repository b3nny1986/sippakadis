<x-layout title="Ajukan Perubahan Status">
    <div class="mx-auto max-w-2xl">
        @if ($kendaraan->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-400 shadow-sm">
                Tidak ada kendaraan yang dapat diajukan perubahan statusnya (semua sedang dalam permohonan).
            </div>
        @else
            <x-card title="Ajukan Perubahan Status Kendaraan">
                <form method="POST" action="{{ route('opd.perubahan-status.store') }}" class="space-y-4" enctype="multipart/form-data" x-data="{ statusLama: '' }">
                    @csrf

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kendaraan</label>
                        <select name="kendaraan_id" required x-model="statusLama" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">-- Pilih Kendaraan --</option>
                            @foreach ($kendaraan as $k)
                                <option value="{{ $k->id }}" data-status="{{ $k->status_id }}">{{ $k->nopol }} &middot; {{ $k->status?->nama }}</option>
                            @endforeach
                        </select>
                        @error('kendaraan_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Status Baru</label>
                        <select name="status_baru_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">-- Pilih Status Baru --</option>
                            @foreach (\App\Models\StatusKendaraan::orderBy('id')->get() as $st)
                                <option value="{{ $st->id }}" @selected(old('status_baru_id') == $st->id)>{{ $st->nama }}</option>
                            @endforeach
                        </select>
                        @error('status_baru_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Alasan Perubahan <span class="text-red-500">*</span></label>
                        <textarea name="alasan" rows="4" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">{{ old('alasan') }}</textarea>
                        @error('alasan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Lampiran (opsional, maks 2MB)</label>
                        <input type="file" name="lampiran" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700 focus:border-brand-500 focus:outline-none">
                        @error('lampiran')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('kendaraan.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Kirim Permohonan</button>
                    </div>
                </form>
            </x-card>
        @endif
    </div>
</x-layout>
