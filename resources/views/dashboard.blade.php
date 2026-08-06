<x-layout title="Dashboard">
    <div class="space-y-6" x-data="{}" x-init="initCharts($el)">

        {{-- Kartu KPI --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-kpi label="Total Kendaraan" :value="$ringkasan['total_kendaraan']" icon="car" />
            <x-kpi label="Kendaraan Aktif" :value="$ringkasan['kendaraan_aktif']" icon="check" tone="emerald" />
            <x-kpi label="PKB Lewat" :value="$ringkasan['pkb_lewat']" icon="alert" tone="red" />
            <x-kpi label="STNK Lewat" :value="$ringkasan['stnk_lewat']" icon="alert" tone="red" />
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-kpi label="PKB H-7 ke Bawah" :value="$ringkasan['pkb_h7'] + $ringkasan['pkb_h1'] + $ringkasan['pkb_harin_h']" icon="clock" tone="amber" />
            <x-kpi label="Menunggu Verifikasi" :value="$ringkasan['menunggu_verifikasi']" icon="shield" tone="amber" />
            <x-kpi label="Pengajuan Menunggu" :value="$ringkasan['pengajuan_menunggu']" icon="document" tone="sky" />
            <x-kpi label="Total OPD" :value="$ringkasan['total_opd']" icon="building" tone="brand" />
        </div>

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
