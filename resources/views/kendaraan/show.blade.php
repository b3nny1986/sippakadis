<x-layout title="Detail Kendaraan - {{ $kendaraan->nopol }}">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $kendaraan->nopol }}</h2>
                <p class="text-sm text-slate-500">{{ $kendaraan->opd?->nama }} &middot; {{ $kendaraan->jenis?->nama ?? '-' }} &middot; Tahun {{ $kendaraan->tahun ?? '-' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($isAdmin)
                    <a href="{{ route('admin.kendaraan.edit', $kendaraan) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Edit</a>
                    <form method="POST" action="{{ route('admin.kendaraan.sinkronisasi', $kendaraan) }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Sinkronisasi Simpator</button>
                    </form>
                @else
                    <a href="{{ route('opd.pengajuan.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Ajukan Penetapan</a>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Data kendaraan --}}
            <x-card title="Data Kendaraan" class="lg:col-span-2">
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <x-dl-item label="NOPOL" :value="$kendaraan->nopol" />
                    <x-dl-item label="NOPOL Lama" :value="$kendaraan->nopol_lama" />
                    <x-dl-item label="Pemilik" :value="$kendaraan->nama_pemilik" />
                    <x-dl-item label="Merk / Tipe" :value="trim(($kendaraan->merk ?? '') . ' ' . ($kendaraan->tipe ?? '')) ?: null" />
                    <x-dl-item label="No. Rangka" :value="$kendaraan->no_rangka" />
                    <x-dl-item label="No. Mesin" :value="$kendaraan->no_mesin" />
                    <x-dl-item label="Tahun" :value="$kendaraan->tahun" />
                    <x-dl-item label="Warna" :value="$kendaraan->warna" />
                    <x-dl-item label="Lokasi" :value="$kendaraan->lokasi" />
                    <x-dl-item label="Status" :value="$kendaraan->status?->nama" />
                    <x-dl-item label="Sumber Data" :value="strtoupper($kendaraan->sumber_data)" />
                </dl>
            </x-card>

            {{-- Status monitoring --}}
            <x-card title="Status Pajak">
                <div class="space-y-4">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-700">PKB</p>
                            <x-badge :value="$kendaraan->pkb_status">{{ $kendaraan->pkb_status }}</x-badge>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">Masa berlaku: <span class="font-semibold">{{ $kendaraan->masa_berlaku_pkb?->format('d-m-Y') ?? '-' }}</span></p>
                        <p class="text-sm text-slate-600">Nilai PKB: <span class="font-semibold">Rp {{ number_format($kendaraan->nilai_pkb ?? 0, 0, ',', '.') }}</span></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-700">STNK</p>
                            <x-badge :value="$kendaraan->stnk_status">{{ $kendaraan->stnk_status }}</x-badge>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">Masa berlaku: <span class="font-semibold">{{ $kendaraan->masa_berlaku_stnk?->format('d-m-Y') ?? '-' }}</span></p>
                        <p class="text-sm text-slate-600">SWDKLLJ: <span class="font-semibold">Rp {{ number_format($kendaraan->nilai_swdkllj ?? 0, 0, ',', '.') }}</span></p>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Riwayat --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <x-card title="Pengajuan Penetapan">
                @forelse ($kendaraan->pengajuanPenetapan->sortByDesc('created_at') as $p)
                    <div class="flex items-center justify-between border-b border-slate-100 py-3 text-sm last:border-0">
                        <div>
                            <p class="font-semibold text-slate-800">Tahun {{ $p->tahun_pajak }}</p>
                            <p class="text-xs text-slate-500">{{ $p->created_at->format('d-m-Y H:i') }}</p>
                        </div>
                        <x-badge :value="$p->status">{{ $p->status }}</x-badge>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">Belum ada pengajuan.</p>
                @endforelse
            </x-card>

            <x-card title="Riwayat Perubahan Status">
                @forelse ($kendaraan->perubahanStatus as $ps)
                    <div class="flex items-center justify-between border-b border-slate-100 py-3 text-sm last:border-0">
                        <div>
                            <p class="font-semibold text-slate-800">
                                {{ $ps->statusLama?->nama }} &rarr; {{ $ps->statusBaru?->nama }}
                            </p>
                            <p class="text-xs text-slate-500">{{ $ps->created_at->format('d-m-Y H:i') }}</p>
                        </div>
                        <x-badge :value="$ps->status">{{ $ps->status }}</x-badge>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">Belum ada perubahan status.</p>
                @endforelse
            </x-card>
        </div>
    </div>
</x-layout>
