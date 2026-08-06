<x-layout title="Audit Log">
    <div class="space-y-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Cari</label>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Deskripsi / aksi"
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Filter</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                        <th class="px-4 py-3 text-left">Entitas</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $log->created_at?->format('d-m-Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="px-4 py-3"><x-badge>{{ $log->aksi }}</x-badge></td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ class_basename($log->entitas_tipe) }} #{{ $log->entitas_id }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $log->deskripsi }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada audit log.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3">{{ $logs->links() }}</div>
        </div>

        <x-card title="Aktivitas Terakhir Pengguna">
            <div class="divide-y divide-slate-100">
                @forelse ($aktivitas as $a)
                    <div class="flex items-center justify-between py-2 text-sm">
                        <div>
                            <p class="font-medium text-slate-800">{{ $a->user?->name ?? 'Sistem' }}</p>
                            <p class="text-xs text-slate-500">{{ $a->aktivitas }} &middot; {{ $a->detail }}</p>
                        </div>
                        <p class="text-xs text-slate-400">{{ $a->created_at?->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="py-4 text-center text-slate-400">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </x-card>
    </div>
</x-layout>
