<x-layout title="{{ $kendaraan->exists ? 'Edit Data Manual' : 'Tambah Data Manual' }}">
    <div class="mx-auto max-w-3xl">
        <x-card title="{{ $kendaraan->exists ? 'Edit Data Manual - ' . $kendaraan->nopol : 'Tambah Data Manual' }}">
            <form method="POST" action="{{ $kendaraan->exists ? route('admin.data-manual.update', $kendaraan) : route('admin.data-manual.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                @if ($kendaraan->exists) @method('PUT') @endif

                <div class="sm:col-span-2">
                    <label for="opd_id" class="mb-1 block text-sm font-medium text-slate-700">OPD<span class="text-red-500">*</span></label>
                    <select id="opd_id" name="opd_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
                        <option value="">-- Pilih OPD --</option>
                        @foreach ($daftarOpd as $opd)
                            <option value="{{ $opd->id }}" @selected(old('opd_id', $kendaraan->opd_id) == $opd->id)>{{ $opd->nama }}</option>
                        @endforeach
                    </select>
                    @error('opd_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jenis_id" class="mb-1 block text-sm font-medium text-slate-700">Jenis Kendaraan</label>
                    <select id="jenis_id" name="jenis_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach ($daftarJenis as $jenis)
                            <option value="{{ $jenis->id }}" @selected(old('jenis_id', $kendaraan->jenis_id) == $jenis->id)>{{ $jenis->nama }}</option>
                        @endforeach
                    </select>
                    @error('jenis_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status_id" class="mb-1 block text-sm font-medium text-slate-700">Status Kendaraan<span class="text-red-500">*</span></label>
                    <select id="status_id" name="status_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
                        <option value="">-- Pilih Status --</option>
                        @foreach ($daftarStatus as $st)
                            <option value="{{ $st->id }}" @selected(old('status_id', $kendaraan->status_id) == $st->id)>{{ $st->nama }}</option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-field label="NOPOL" name="nopol" :value="$kendaraan->nopol" required />
                <x-field label="NOPOL Lama" name="nopol_lama" :value="$kendaraan->nopol_lama" />
                <x-field label="Nama Pemilik" name="nama_pemilik" :value="$kendaraan->nama_pemilik" class="sm:col-span-2" />
                <x-field label="Merk" name="merk" :value="$kendaraan->merk" />
                <x-field label="Tipe" name="tipe" :value="$kendaraan->tipe" />
                <x-field label="Tahun" name="tahun" type="number" :value="$kendaraan->tahun" />
                <x-field label="No. Rangka" name="no_rangka" :value="$kendaraan->no_rangka" class="sm:col-span-2" />
                <x-field label="No. Mesin" name="no_mesin" :value="$kendaraan->no_mesin" class="sm:col-span-2" />
                <x-field label="Akhir PKB" name="masa_berlaku_pkb" type="date" :value="$kendaraan->masa_berlaku_pkb?->format('Y-m-d')" />
                <x-field label="Akhir STNK" name="masa_berlaku_stnk" type="date" :value="$kendaraan->masa_berlaku_stnk?->format('Y-m-d')" />
                <x-field label="Lokasi" name="lokasi" :value="$kendaraan->lokasi" class="sm:col-span-2" />

                <div class="sm:col-span-2">
                    <label for="keterangan" class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">{{ old('keterangan', $kendaraan->keterangan) }}</textarea>
                </div>

                <div class="flex justify-end gap-2 sm:col-span-2">
                    <a href="{{ route('admin.data-manual.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ $kendaraan->exists ? 'Simpan Perubahan' : 'Simpan' }}</button>
                </div>
            </form>
        </x-card>
    </div>
</x-layout>
