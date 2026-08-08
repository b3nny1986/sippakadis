<x-layout title="Data Manual">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">Kelola kendaraan sumber <span class="font-semibold text-slate-700">manual</span> (bukan dari import CSV / Simpator).</p>
            <a href="{{ route('admin.data-manual.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Tambah Data</a>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="min-w-56 flex-1">
                <label class="mb-1 block text-xs font-medium text-slate-500">Cari</label>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="NOPOL / NOPOL Lama / Pemilik / No. Rangka / No. Mesin / Merk"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
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
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kendaraan as $k)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5 font-semibold text-brand-700">{{ $k->nopol }}</td>
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
                            <tr><td colspan="15" class="px-4 py-10 text-center text-slate-400">Belum ada data manual. Klik "Tambah Data" untuk membuat kendaraan pertama.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">{{ $kendaraan->links() }}</div>
        </div>
    </div>
</x-layout>
