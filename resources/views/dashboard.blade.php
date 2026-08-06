<x-layout title="Dashboard">
    <div class="space-y-6" x-data="{}" x-init="initCharts($el)">

        {{-- Kartu KPI (klik -> daftar kendaraan) --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <a href="{{ route('kendaraan.index') }}" class="block transition hover:opacity-90">
                <x-kpi label="Total Kendaraan" :value="$ringkasan['total_kendaraan']" icon="car" />
            </a>
            <a href="{{ route('kendaraan.index', ['status_id' => $ringkasan['status_aktif_id']]) }}" class="block transition hover:opacity-90">
                <x-kpi label="Kendaraan Aktif" :value="$ringkasan['kendaraan_aktif']" icon="check" tone="emerald" />
            </a>
            <a href="{{ route('kendaraan.index', ['status_monitoring' => 'LEWAT']) }}" class="block transition hover:opacity-90">
                <x-kpi label="PKB Lewat" :value="$ringkasan['pkb_lewat']" icon="alert" tone="red" />
            </a>
            <a href="{{ route('kendaraan.index', ['status_monitoring' => 'LEWAT']) }}" class="block transition hover:opacity-90">
                <x-kpi label="STNK Lewat" :value="$ringkasan['stnk_lewat']" icon="alert" tone="red" />
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <a href="{{ route('kendaraan.index', ['status_monitoring' => 'H7']) }}" class="block transition hover:opacity-90">
                <x-kpi label="PKB H-7 ke Bawah" :value="$ringkasan['pkb_h7'] + $ringkasan['pkb_h1'] + $ringkasan['pkb_harin_h']" icon="clock" tone="amber" />
            </a>
            <a href="{{ route('kendaraan.index', ['verifikasi' => 1]) }}" class="block transition hover:opacity-90">
                <x-kpi label="Menunggu Verifikasi" :value="$ringkasan['menunggu_verifikasi']" icon="shield" tone="amber" />
            </a>
            <a href="{{ route('admin.penetapan.index') }}" class="block transition hover:opacity-90">
                <x-kpi label="Pengajuan Menunggu" :value="$ringkasan['pengajuan_menunggu']" icon="document" tone="sky" />
            </a>
            <x-kpi label="Total OPD" :value="$ringkasan['total_opd']" icon="building" tone="brand" />
        </div>

        {{-- Rekap status unit (klik kartu -> daftar kendaraan) --}}
        <x-card title="Rekap Kendaraan per Status">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
                @php
                    $warnaStatus = [
                        'emerald' => ['border' => 'border-emerald-500', 'bg' => 'bg-emerald-50', 'num' => 'text-emerald-700'],
                        'amber'   => ['border' => 'border-amber-400',   'bg' => 'bg-amber-50',   'num' => 'text-amber-700'],
                        'red'     => ['border' => 'border-red-500',     'bg' => 'bg-red-50',     'num' => 'text-red-700'],
                        'gray'    => ['border' => 'border-slate-400',   'bg' => 'bg-slate-50',   'num' => 'text-slate-700'],
                        'purple'  => ['border' => 'border-purple-400',  'bg' => 'bg-purple-50',  'num' => 'text-purple-700'],
                        'blue'    => ['border' => 'border-blue-400',    'bg' => 'bg-blue-50',    'num' => 'text-blue-700'],
                        'orange'  => ['border' => 'border-orange-400',  'bg' => 'bg-orange-50',  'num' => 'text-orange-700'],
                        'teal'    => ['border' => 'border-teal-400',    'bg' => 'bg-teal-50',    'num' => 'text-teal-700'],
                        'slate'   => ['border' => 'border-slate-400',   'bg' => 'bg-slate-50',   'num' => 'text-slate-700'],
                        'indigo'  => ['border' => 'border-indigo-400',  'bg' => 'bg-indigo-50',  'num' => 'text-indigo-700'],
                    ];
                @endphp
                @foreach ($rekapStatus as $st)
                    @php $w = $warnaStatus[$st->warna_badge] ?? $warnaStatus['slate']; @endphp
                    <a href="{{ route('kendaraan.index', ['status_id' => $st->id]) }}"
                       class="group rounded-2xl border-l-4 {{ $w['border'] }} {{ $w['bg'] }} p-4 shadow-sm transition hover:shadow-md">
                        <div class="text-3xl font-bold {{ $w['num'] }}">{{ $st->kendaraan_count }}</div>
                        <div class="mt-1 text-sm font-semibold text-slate-700">{{ $st->nama }}</div>
                    </a>
                @endforeach
            </div>
        </x-card>

        {{-- Rekap Jatuh Tempo (klik kartu -> daftar kendaraan) --}}
        <x-card title="Rekap Jatuh Tempo (PKB & STNK)">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
                @php
                    $kartuRekap = [
                        'LEWAT' => ['label' => 'Lewat Jatuh Tempo', 'border' => 'border-red-500',   'bg' => 'bg-red-50',     'num' => 'text-red-600',     'sub' => 'text-red-600'],
                        'HARI_H' => ['label' => 'Jatuh Tempo Hari Ini (H)', 'border' => 'border-orange-500', 'bg' => 'bg-orange-50', 'num' => 'text-orange-600', 'sub' => 'text-orange-600'],
                        'H1'   => ['label' => 'H-1',   'border' => 'border-amber-400', 'bg' => 'bg-amber-50',  'num' => 'text-amber-600', 'sub' => 'text-amber-600'],
                        'H7'   => ['label' => 'H-7',   'border' => 'border-yellow-400', 'bg' => 'bg-yellow-50', 'num' => 'text-yellow-600','sub' => 'text-yellow-700'],
                        'H14'  => ['label' => 'H-14',  'border' => 'border-sky-400',    'bg' => 'bg-sky-50',     'num' => 'text-sky-600',   'sub' => 'text-sky-600'],
                        'H30'  => ['label' => 'H-30',  'border' => 'border-blue-400',   'bg' => 'bg-blue-50',    'num' => 'text-blue-600',  'sub' => 'text-blue-600'],
                    ];
                @endphp
                @foreach ($kartuRekap as $status => $c)
                    @php $r = $rekapMonitoring[$status] ?? ['total' => 0, 'pkb' => 0, 'stnk' => 0]; @endphp
                    <a href="{{ route('kendaraan.index', ['status_monitoring' => $status]) }}"
                       class="group rounded-2xl border-l-4 {{ $c['border'] }} {{ $c['bg'] }} p-4 shadow-sm transition hover:shadow-md">
                        <div class="text-3xl font-bold {{ $c['num'] }}">{{ $r['total'] }}</div>
                        <div class="mt-1 text-sm font-semibold text-slate-700">{{ $c['label'] }}</div>
                        <div class="mt-1 text-xs {{ $c['sub'] }}">PKB {{ $r['pkb'] }} · STNK {{ $r['stnk'] }}</div>
                    </a>
                @endforeach
            </div>
        </x-card>

        {{-- Rekap per OPD (klik baris -> daftar kendaraan) --}}
        <x-card title="Rekap Kendaraan per OPD">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">OPD</th>
                            <th class="px-4 py-3 text-right">Total Kendaraan</th>
                            <th class="px-4 py-3 text-right">Lewat Jatuh Tempo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rekapPerOpd as $o)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('kendaraan.index', ['opd_id' => $o->id]) }}"
                                       class="font-medium text-brand-700 hover:underline">{{ $o->nama }}</a>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-700">{{ $o->kendaraan_count }}</td>
                                <td class="px-4 py-3 text-right {{ $o->lewat_count > 0 ? 'font-semibold text-red-600' : 'text-slate-400' }}">{{ $o->lewat_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-400">Tidak ada OPD.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 border-t border-slate-100 pt-3">
                {{ $rekapPerOpd->links() }}
            </div>
        </x-card>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Doughnut status --}}
            <x-card title="Kendaraan per Status">
                <canvas id="chart-status" class="max-h-72"></canvas>
            </x-card>

            {{-- Bar per OPD --}}
            <x-card title="Kendaraan per OPD" class="lg:col-span-2">
                <canvas id="chart-opd" class="max-h-72"></canvas>
            </x-card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Line jatuh tempo --}}
            <x-card title="Jatuh Tempo 12 Bulan ke Depan">
                <canvas id="chart-jatuh-tempo" class="max-h-64"></canvas>
            </x-card>

            {{-- Line penetapan --}}
            <x-card title="Penetapan Disetujui (6 Bulan)">
                <canvas id="chart-penetapan" class="max-h-64"></canvas>
            </x-card>
        </div>

        @if ($isAdmin)
            <x-card title="Estimasi Nilai PKB Jatuh Tempo (Rp)">
                <canvas id="chart-pembayaran" class="max-h-64"></canvas>
            </x-card>
        @endif
    </div>

    @push('scripts')
        <script>
            function initCharts(root) {
                const status = @json($perStatus);
                new Chart(document.getElementById('chart-status'), {
                    type: 'doughnut',
                    data: {
                        labels: status.labels,
                        datasets: [{ data: status.data, borderWidth: 2 }],
                    },
                    options: { plugins: { legend: { position: 'right' } } },
                });

                const opd = @json($perOpd);
                new Chart(document.getElementById('chart-opd'), {
                    type: 'bar',
                    data: {
                        labels: opd.labels,
                        datasets: [{ label: 'Kendaraan', data: opd.data, backgroundColor: '#2563eb' }],
                    },
                    options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } },
                });

                const jt = @json($rekapJatuhTempo);
                new Chart(document.getElementById('chart-jatuh-tempo'), {
                    type: 'line',
                    data: {
                        labels: jt.labels,
                        datasets: [
                            { label: 'PKB', data: jt.pkb, borderColor: '#2563eb', tension: 0.3 },
                            { label: 'STNK', data: jt.stnk, borderColor: '#f59e0b', tension: 0.3 },
                        ],
                    },
                    options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
                });

                const pen = @json($rekapPenetapan);
                new Chart(document.getElementById('chart-penetapan'), {
                    type: 'line',
                    data: {
                        labels: pen.labels,
                        datasets: [{ label: 'Disetujui', data: pen.data, borderColor: '#10b981', backgroundColor: '#10b981', tension: 0.3, fill: false }],
                    },
                    options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
                });

                @if ($isAdmin)
                    const bayar = @json($statistikPembayaran);
                    new Chart(document.getElementById('chart-pembayaran'), {
                        type: 'line',
                        data: {
                            labels: bayar.labels,
                            datasets: [{ label: 'Nilai PKB (Rp)', data: bayar.data, borderColor: '#1e3a8a', tension: 0.3 }],
                        },
                        options: { scales: { y: { beginAtZero: true } }, plugins: { tooltip: { callbacks: { label: (c) => 'Rp ' + c.raw.toLocaleString('id-ID') } } } },
                    });
                @endif
            }
        </script>
    @endpush
</x-layout>
