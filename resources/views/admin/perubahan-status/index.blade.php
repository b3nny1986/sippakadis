<x-layout title="Permohonan Perubahan Status">
    <div class="space-y-5">
        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Cari NOPOL</label>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="NOPOL"
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach (\App\Models\PerubahanStatus::STATUSES as $st)
                        <option value="{{ $st }}" @selected(request('status') == $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Filter</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">NOPOL</th>
                        <th class="px-4 py-3 text-left">Perubahan</th>
                        <th class="px-4 py-3 text-left">Pengaju</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($perubahan as $ps)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-brand-700">{{ $ps->kendaraan?->nopol }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $ps->statusLama?->nama }} &rarr; {{ $ps->statusBaru?->nama }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $ps->diajukanOleh?->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $ps->created_at?->format('d-m-Y') }}</td>
                            <td class="px-4 py-3"><x-badge :value="$ps->status">{{ $ps->status }}</x-badge></td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.perubahan-status.show', $ps) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada permohonan.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3">{{ $perubahan->links() }}</div>
        </div>
    </div>
</x-layout>
