<x-layout title="Log">
    <div class="space-y-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Cari</label>
                <input type="text" name="cari" value="{{ $cari }}" placeholder="Aksi / user / detail"
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Filter</button>
            @if ($cari)
                <a href="{{ route('admin.log.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Reset</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50 px-4 py-3 text-sm">
                <span class="font-semibold text-slate-700">{{ $items->total() }} entri</span>
                <span class="ml-2 rounded-full bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-700">Audit Log</span>
                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Aktivitas User</span>
            </div>

            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                        <th class="px-4 py-3 text-left">Entitas</th>
                        <th class="px-4 py-3 text-left">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $item['waktu']?->format('d-m-Y H:i:s') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $item['user'] }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($item['jenis'] === 'Aktivitas')
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Aktivitas</span>
                                @else
                                    <span class="rounded-full bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-700">Audit</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3"><x-badge>{{ $item['aksi'] }}</x-badge></td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-500">{{ $item['entitas'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $item['detail'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada log.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3">{{ $items->links() }}</div>
        </div>
    </div>
</x-layout>
