<x-layout title="Data Kendaraan">
    <div class="space-y-5">
        {{-- Filter --}}
        <form method="GET" action="{{ route('kendaraan.index') }}" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Cari NOPOL / Merk / Tipe</label>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="cth: KT 1234 AB"
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
            </div>

            @if (!$isOpd)
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">OPD</label>
                    <select name="opd_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">Semua OPD</option>
                        @foreach ($daftarOpd as $opd)
                            <option value="{{ $opd->id }}" @selected(request('opd_id') == $opd->id)>{{ $opd->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Status Kendaraan</label>
                <select name="status_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    @foreach ($daftarStatus as $st)
                        <option value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Status Monitoring</label>
                <select name="status_monitoring" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach (['LEWAT', 'HARI_H', 'H1', 'H7', 'H14', 'H30', 'AMAN'] as $sm)
                        <option value="{{ $sm }}" @selected(request('status_monitoring') == $sm)>{{ $sm }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                Filter
            </button>
            <a href="{{ route('kendaraan.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Reset
            </a>
        </form>

        {{-- Tabel --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">NOPOL</th>
                        @if (!$isOpd)<th class="px-4 py-3 text-left">OPD</th>@endif
                        <th class="px-4 py-3 text-left">Merk / Tipe</th>
                        <th class="px-4 py-3 text-left">Tahun</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">PKB</th>
                        <th class="px-4 py-3 text-left">STNK</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($kendaraan as $k)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-brand-700">{{ $k->nopol }}</td>
                            @if (!$isOpd)<td class="px-4 py-3 text-slate-600">{{ $k->opd?->nama }}</td>@endif
                            <td class="px-4 py-3 text-slate-700">{{ $k->merk ?? '-' }} {{ $k->tipe }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $k->tahun ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <x-badge>{{ $k->status?->nama }}</x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <x-badge :value="$k->pkb_status">{{ $k->pkb_status }}</x-badge>
                                        <span class="text-xs text-slate-500">{{ $k->masa_berlaku_pkb?->format('d-m-Y') ?? '-' }}</span>
                                    </div>
                                    @php $ketPkb = \App\Support\Monitoring::keterangan($k->masa_berlaku_pkb); @endphp
                                    <span class="text-xs {{ $ketPkb['warna'] }}">{{ $ketPkb['teks'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <x-badge :value="$k->stnk_status">{{ $k->stnk_status }}</x-badge>
                                        <span class="text-xs text-slate-500">{{ $k->masa_berlaku_stnk?->format('d-m-Y') ?? '-' }}</span>
                                    </div>
                                    @php $ketStnk = \App\Support\Monitoring::keterangan($k->masa_berlaku_stnk); @endphp
                                    <span class="text-xs {{ $ketStnk['warna'] }}">{{ $ketStnk['teks'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('kendaraan.show', $k) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                                    @auth Detail @else Login Detail @endauth
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isOpd ? 7 : 8 }}" class="px-4 py-10 text-center text-slate-400">
                                Tidak ada data kendaraan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="border-t border-slate-100 px-4 py-3">
                {{ $kendaraan->links() }}
            </div>
        </div>
    </div>
</x-layout>
