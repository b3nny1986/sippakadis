<x-layout title="OPD">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET">
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama OPD"
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
            </form>
            <a href="{{ route('admin.opd.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">+ Tambah OPD</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama OPD</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Telepon</th>
                        <th class="px-4 py-3 text-right">Kendaraan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($opds as $opd)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $opd->kode }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $opd->nama }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $opd->email ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $opd->telepon ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-700">{{ number_format($opd->kendaraan_count) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.opd.edit', $opd) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">Edit</a>
                                    <form method="POST" action="{{ route('admin.opd.destroy', $opd) }}" onsubmit="return confirm('Hapus OPD ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada OPD.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3">{{ $opds->links() }}</div>
        </div>
    </div>
</x-layout>
