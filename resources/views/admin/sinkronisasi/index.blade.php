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

        <form method="POST" action="{{ route('admin.sinkronisasi.upload') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').textContent = 'Memproses...'">
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-800">Upload CSV Update Masa PKB</p>
                    <p class="mt-0.5 text-xs text-slate-500">Unggah file CSV untuk memperbarui masa berlaku PKB/STNK secara massal. Kolom: <span class="font-mono text-slate-700">NO POLISI</span> (wajib), <span class="font-mono text-slate-700">AKHIR_PKB</span>, <span class="font-mono text-slate-700">AKHIR_STNK</span>, <span class="font-mono text-slate-700">NO RANGKA</span> (opsional, untuk verifikasi). Format tanggal <span class="font-mono text-slate-700">DD/MM/YYYY</span>. Nopol yang tidak ditemukan di sistem akan dilewati &amp; dilaporkan.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <input type="file" name="file" accept=".csv,.txt" required
                           class="block w-full max-w-xs text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                    <a href="{{ route('admin.sinkronisasi.template') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Download Template</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Upload &amp; Proses</button>
                </div>
            </div>
            @error('file')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </form>

        <div class="flex items-center justify-end">
            <form method="GET" class="flex items-center gap-2" onchange="this.submit()">
                <label class="text-xs font-medium text-slate-500">Urutkan</label>
                <select name="sort" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="sinkron_dulu" @selected(($sort ?? 'sinkron_dulu') === 'sinkron_dulu')>Sudah sinkron dulu</option>
                    <option value="belum_dulu" @selected(($sort ?? '') === 'belum_dulu')>Belum diskrap dulu</option>
                    <option value="nopol_asc" @selected(($sort ?? '') === 'nopol_asc')>NOPOL A-Z</option>
                    <option value="nopol_desc" @selected(($sort ?? '') === 'nopol_desc')>NOPOL Z-A</option>
                    <option value="pkb_asc" @selected(($sort ?? '') === 'pkb_asc')>Masa PKB terdekat</option>
                    <option value="pkb_desc" @selected(($sort ?? '') === 'pkb_desc')>Masa PKB terjauh</option>
                    <option value="sinkron_terbaru" @selected(($sort ?? '') === 'sinkron_terbaru')>Terakhir disinkronkan</option>
                </select>
                <input type="hidden" name="cari" value="{{ request('cari') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
            </form>
        </div>

        <form method="POST" action="{{ route('admin.sinkronisasi.jalankan') }}" id="form-sinkronisasi-manual">
            @csrf
            <input type="hidden" name="manual" value="1">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Sinkronisasi Manual per NOPOL</p>
                        <p class="text-xs text-slate-500">Centang satu atau beberapa kendaraan, lalu klik "Sinkronisasi Terpilih". Rata-rata ±1,2 detik per kendaraan; pilih secukupnya agar proses tidak lama.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" id="pilih-semua" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            Pilih semua di halaman ini
                        </label>
                        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Sinkronisasi Terpilih</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="w-10 px-4 py-3"></th>
                                <th class="px-4 py-3 text-left">NOPOL</th>
                                <th class="px-4 py-3 text-left">Masa PKB</th>
                                <th class="px-4 py-3 text-left">OPD</th>
                                <th class="px-4 py-3 text-left">Kendaraan</th>
                                <th class="px-4 py-3 text-left">Status PKB / STNK</th>
                                <th class="px-4 py-3 text-left">Status Kendaraan</th>
                                <th class="px-4 py-3 text-left">Diskrap</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($kendaraan as $k)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-2.5">
                                        <input type="checkbox" name="kendaraan_ids[]" value="{{ $k->id }}" class="cek-nopol h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    </td>
                                    <td class="px-4 py-2.5 font-semibold text-brand-700">
                                        {{ $k->nopol }}
                                        @if ($k->histori_scraping_count > 0)
                                            <span class="ml-1 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20">&#10003; Sudah Sinkron</span>
                                        @else
                                            <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">Belum</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5">
                                        @if ($k->masa_berlaku_pkb)
                                            <span class="font-mono text-sm font-medium {{ $k->pkb_status === 'LEWAT' ? 'text-red-600' : 'text-slate-700' }}">{{ $k->masa_berlaku_pkb->format('d-m-Y') }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $k->opd?->nama }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">
                                        {{ $k->merk }} {{ $k->tipe }} <span class="text-slate-400">({{ $k->tahun ?? '-' }})</span>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex gap-1">
                                            <x-badge :value="$k->pkb_status">{{ $k->pkb_status }}</x-badge>
                                            <x-badge :value="$k->stnk_status">{{ $k->stnk_status }}</x-badge>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $k->status?->nama ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-slate-500">{{ $k->histori_scraping_count }}x</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">Tidak ada kendaraan dalam antrian sinkronisasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-4 py-3">{{ $kendaraan->links() }}</div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.sinkronisasi.jalankan') }}" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Menjalankan...'">
            @csrf
            <div>
                <p class="text-sm font-semibold text-slate-800">Sinkronisasi Massal (Batch)</p>
                <p class="text-xs text-slate-500">Proses {{ config('monitoring.simpator.batch') }} kendaraan per batch otomatis (prioritas yang belum pernah diskrap). Dapat memakan waktu beberapa menit.</p>
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

    @push('scripts')
    <script>
        const pilihSemua = document.getElementById('pilih-semua');
        const cekNopol = document.querySelectorAll('.cek-nopol');

        if (pilihSemua) {
            pilihSemua.addEventListener('change', () => {
                cekNopol.forEach((c) => { c.checked = pilihSemua.checked; });
            });
        }

        const formManual = document.getElementById('form-sinkronisasi-manual');
        if (formManual) {
            formManual.addEventListener('submit', (e) => {
                const cek = formManual.querySelectorAll('.cek-nopol:checked');
                if (!cek.length) {
                    e.preventDefault();
                    return;
                }
                const btn = formManual.querySelector('button[type=submit]');
                btn.disabled = true;
                btn.textContent = 'Sinkronisasi ' + cek.length + ' kendaraan...';
            });
        }
    </script>
    @endpush
</x-layout>
