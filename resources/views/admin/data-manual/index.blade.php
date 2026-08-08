<x-layout title="Data Manual">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">Kelola seluruh data kendaraan (<span class="font-semibold text-slate-700">master CSV</span>, <span class="font-semibold text-slate-700">manual</span>, dan <span class="font-semibold text-slate-700">Simpator</span>).</p>
            <a href="{{ route('admin.data-manual.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Tambah Data</a>
        </div>

        <form method="GET" action="{{ route('admin.data-manual.download') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">OPD</label>
                    <select name="opd_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">Semua</option>
                        @foreach ($daftarOpd as $opd)
                            <option value="{{ $opd->id }}" @selected(request('opd_id') == $opd->id)>{{ $opd->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Status Kendaraan</label>
                    <select name="status_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">Semua</option>
                        @foreach ($daftarStatus as $st)
                            <option value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Masa PKB</label>
                    <select name="masa_pkb" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">Semua</option>
                        @foreach (\App\Support\Monitoring::statuses() as $st)
                            <option value="{{ $st }}" @selected(request('masa_pkb') == $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex cursor-pointer items-center gap-2 py-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="pilih_semua" value="1" @checked(request()->boolean('pilih_semua')) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Pilih Semua
                </label>
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Download Data Master</button>
            </div>
            <p class="mt-2 text-xs text-slate-500">Unduh CSV format master (MM/DD/YYYY) yang kompatibel untuk diimpor ulang. Centang "Pilih Semua" untuk mengunduh seluruh data tanpa filter.</p>
        </form>

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="min-w-56 flex-1">
                <label class="mb-1 block text-xs font-medium text-slate-500">Cari</label>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="NOPOL / NOPOL Lama / Pemilik / No. Rangka / No. Mesin / Merk"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Sumber</label>
                <select name="sumber" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">Semua</option>
                    <option value="csv" @selected(request('sumber') === 'csv')>CSV (Master)</option>
                    <option value="manual" @selected(request('sumber') === 'manual')>Manual</option>
                    <option value="simpator" @selected(request('sumber') === 'simpator')>Simpator</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">OPD</label>
                <select name="opd_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach ($daftarOpd as $opd)
                        <option value="{{ $opd->id }}" @selected(request('opd_id') == $opd->id)>{{ $opd->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                <select name="status_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach ($daftarStatus as $st)
                        <option value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Filter</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">NOPOL</th>
                            <th class="px-4 py-3 text-left">NOPOL Lama</th>
                            <th class="px-4 py-3 text-left">OPD</th>
                            <th class="px-4 py-3 text-left">Nama Pemilik</th>
                            <th class="px-4 py-3 text-left">Jenis</th>
                            <th class="px-4 py-3 text-left">Merk</th>
                            <th class="px-4 py-3 text-left">Tipe</th>
                            <th class="px-4 py-3 text-left">Tahun</th>
                            <th class="px-4 py-3 text-left">No. Rangka</th>
                            <th class="px-4 py-3 text-left">No. Mesin</th>
                            <th class="px-4 py-3 text-left">Akhir PKB</th>
                            <th class="px-4 py-3 text-left">Akhir STNK</th>
                            <th class="px-4 py-3 text-left">Lokasi</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Sumber</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kendaraan as $k)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('admin.kendaraan.sinkronisasi', $k) }}" class="shrink-0" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = '...'">
                                            @csrf
                                            <button type="submit" title="Sinkronisasi ke Simpator: {{ $k->nopol }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-brand-50 hover:text-brand-700">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M4 10a8 8 0 0114-4.4M20 14a8 8 0 01-14 4.4"/></svg>
                                                Sinkron
                                            </button>
                                        </form>
                                        <span class="font-semibold text-brand-700">{{ $k->nopol }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-slate-500">{{ $k->nopol_lama ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $k->opd?->nama }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $k->nama_pemilik ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $k->jenis?->nama ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $k->merk ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $k->tipe ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $k->tahun ?? '-' }}</td>
                                <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $k->no_rangka ?? '-' }}</td>
                                <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $k->no_mesin ?? '-' }}</td>
                                <td class="px-4 py-2.5">
                                    @if ($k->masa_berlaku_pkb)
                                        <span class="font-mono text-sm font-medium {{ $k->pkb_status === 'LEWAT' ? 'text-red-600' : 'text-slate-700' }}">{{ $k->masa_berlaku_pkb->format('d-m-Y') }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    @if ($k->masa_berlaku_stnk)
                                        <span class="font-mono text-sm font-medium {{ $k->stnk_status === 'LEWAT' ? 'text-red-600' : 'text-slate-700' }}">{{ $k->masa_berlaku_stnk->format('d-m-Y') }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $k->lokasi ?? '-' }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex gap-1">
                                        <x-badge :value="$k->pkb_status">{{ $k->pkb_status }}</x-badge>
                                        <x-badge :value="$k->stnk_status">{{ $k->stnk_status }}</x-badge>
                                    </span>
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $sumberBadge = match ($k->sumber_data) {
                                            'csv' => 'bg-sky-100 text-sky-700 ring-sky-600/20',
                                            'simpator' => 'bg-violet-100 text-violet-700 ring-violet-600/20',
                                            'manual' => 'bg-amber-100 text-amber-700 ring-amber-600/20',
                                            default => 'bg-slate-100 text-slate-500 ring-slate-600/20',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 {{ $sumberBadge }}">{{ $k->sumber_data ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.data-manual.edit', $k) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                        <form method="POST" action="{{ route('admin.data-manual.destroy', $k) }}" onsubmit="return confirm('Hapus kendaraan {{ $k->nopol }}? Data terkait (penetapan, perubahan status, notifikasi) ikut terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="16" class="px-4 py-10 text-center text-slate-400">Tidak ada data kendaraan. Klik "Tambah Data" untuk membuat kendaraan pertama.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">{{ $kendaraan->links() }}</div>
        </div>
    </div>
</x-layout>
