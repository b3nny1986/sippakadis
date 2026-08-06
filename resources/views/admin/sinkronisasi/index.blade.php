<x-layout title="Sinkronisasi Simpator">
    <div class="space-y-5">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Sinkronisasi Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($riwayatHariIni) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Berhasil Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($berhasilHariIni) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Antrian Menunggu</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ number_format($antrian) }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.sinkronisasi.jalankan') }}" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Menjalankan...'">
            @csrf
            <div>
                <p class="text-sm font-semibold text-slate-800">Sinkronisasi Massal</p>
                <p class="text-xs text-slate-500">Proses {{ config('monitoring.simpator.batch') }} kendaraan per batch (prioritas yang belum pernah diskrap). Dapat memakan waktu beberapa menit.</p>
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Jalankan Sekarang</button>
        </form>

        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Cari NOPOL</label>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="NOPOL"
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach ([\App\Models\LogSinkronisasi::DITEMUKAN, \App\Models\LogSinkronisasi::TIDAK_DITEMUKAN, \App\Models\LogSinkronisasi::GAGAL] as $st)
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
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Pesan</th>
                        <th class="px-4 py-3 text-left">Durasi</th>
                        <th class="px-4 py-3 text-left">Dijalankan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-brand-700">{{ $log->nopol }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $log->tipe }}</td>
                            <td class="px-4 py-3"><x-badge :value="$log->status">{{ $log->status }}</x-badge></td>
                            <td class="px-4 py-3 text-slate-500">{{ \Illuminate\Support\Str::limit($log->pesan, 40) ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $log->durasi_ms ? $log->durasi_ms . ' ms' : '-' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $log->dijalankanOleh?->name ?? 'Sistem' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada riwayat sinkronisasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3">{{ $logs->links() }}</div>
        </div>
    </div>
</x-layout>
