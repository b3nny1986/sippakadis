<x-layout title="Edit Kendaraan - {{ $kendaraan->nopol }}">
    <div class="mx-auto max-w-3xl">
        <x-card title="Edit Data Kendaraan">
            <form method="POST" action="{{ route('admin.kendaraan.update', $kendaraan) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')

                <x-field label="NOPOL" name="nopol" :value="$kendaraan->nopol" required />
                <x-field label="NOPOL Lama" name="nopol_lama" :value="$kendaraan->nopol_lama" />
                <x-field label="Nama Pemilik" name="nama_pemilik" :value="$kendaraan->nama_pemilik" class="sm:col-span-2" />
                <x-field label="Merk" name="merk" :value="$kendaraan->merk" />
                <x-field label="Tipe" name="tipe" :value="$kendaraan->tipe" />
                <x-field label="Tahun" name="tahun" type="number" :value="$kendaraan->tahun" />
                <x-field label="Warna" name="warna" :value="$kendaraan->warna" />
                <x-field label="No. Rangka" name="no_rangka" :value="$kendaraan->no_rangka" class="sm:col-span-2" />
                <x-field label="No. Mesin" name="no_mesin" :value="$kendaraan->no_mesin" class="sm:col-span-2" />
                <x-field label="Lokasi" name="lokasi" :value="$kendaraan->lokasi" class="sm:col-span-2" />

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Status Kendaraan</label>
                    <select name="status_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
                        @foreach ($daftarStatus as $st)
                            <option value="{{ $st->id }}" @selected($kendaraan->status_id == $st->id)>{{ $st->nama }}</option>
                        @endforeach
                    </select>
                    @error('status_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <x-field label="Masa Berlaku PKB" name="masa_berlaku_pkb" type="date" :value="$kendaraan->masa_berlaku_pkb?->format('Y-m-d')" />
                <x-field label="Masa Berlaku STNK" name="masa_berlaku_stnk" type="date" :value="$kendaraan->masa_berlaku_stnk?->format('Y-m-d')" />
                <x-field label="Nilai PKB (Rp)" name="nilai_pkb" type="number" step="0.01" :value="$kendaraan->nilai_pkb" />
                <x-field label="SWDKLLJ (Rp)" name="nilai_swdkllj" type="number" step="0.01" :value="$kendaraan->nilai_swdkllj" />

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">{{ $kendaraan->keterangan }}</textarea>
                </div>

                <div class="flex justify-end gap-2 sm:col-span-2">
                    <a href="{{ route('kendaraan.show', $kendaraan) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Simpan</button>
                </div>
            </form>
        </x-card>
    </div>
</x-layout>
