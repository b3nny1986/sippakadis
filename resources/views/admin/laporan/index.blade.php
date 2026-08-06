<x-layout title="Laporan & Ekspor">
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($laporan as $lap)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-bold text-slate-900">{{ $lap['label'] }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $lap['desc'] }}</p>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.laporan.export', [$lap['slug'], 'xlsx']) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Ekspor Excel
                    </a>
                    <a href="{{ route('admin.laporan.export', [$lap['slug'], 'pdf']) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        Ekspor PDF
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</x-layout>
