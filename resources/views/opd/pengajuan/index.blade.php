<x-layout title="Pengajuan Penetapan">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex gap-2">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    @foreach (\App\Models\PengajuanPenetapan::STATUSES as $st)
                        <option value="{{ $st }}" @selected(request('status') == $st)>{{ $st }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Filter</button>
            </form>
            <a href="{{ route('opd.pengajuan.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">+ Ajukan Penetapan</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">NOPOL</th>
                        <th class="px-4 py-3 text-left">Tahun Pajak</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">No. Penetapan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pengajuan as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-brand-700">{{ $p->kendaraan?->nopol }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $p->tahun_pajak }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $p->created_at?->format('d-m-Y') }}</td>
                            <td class="px-4 py-3"><x-badge :value="$p->status">{{ $p->status }}</x-badge></td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $p->nomor_penetapan ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('opd.pengajuan.show', $p) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3">{{ $pengajuan->links() }}</div>
        </div>
    </div>
</x-layout>
