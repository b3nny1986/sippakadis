<x-layout title="Permohonan Perubahan Status">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ $perubahan->kendaraan?->nopol }}</h2>
            <p class="text-sm text-slate-500">Diajukan {{ $perubahan->created_at?->format('d-m-Y H:i') }} oleh {{ $perubahan->diajukanOleh?->name }}</p>
        </div>

        <x-card title="Detail Permohonan">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <x-dl-item label="Status Lama" :value="$perubahan->statusLama?->nama" />
                <x-dl-item label="Status Baru" :value="$perubahan->statusBaru?->nama" />
                <x-dl-item label="Status Permohonan" :value="$perubahan->status" />
                <x-dl-item label="Disetujui Pada" :value="$perubahan->disetujui_at?->format('d-m-Y H:i')" />
                <x-dl-item label="Disetujui Oleh" :value="$perubahan->disetujuiOleh?->name" />
                <x-dl-item label="Alasan Penolakan" :value="$perubahan->alasan_penolakan" />
            </dl>
            <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-semibold text-slate-700">Alasan Pengaju:</p>
                <p>{{ $perubahan->alasan }}</p>
            </div>
        </x-card>

        @if ($perubahan->status === \App\Models\PerubahanStatus::MENUNGGU)
            <div class="flex flex-wrap justify-end gap-2">
                <form method="POST" action="{{ route('admin.perubahan-status.approve', $perubahan) }}" onsubmit="return confirm('Setujui dan terapkan perubahan status?')">
                    @csrf
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Setujui & Terapkan</button>
                </form>
                <form method="POST" action="{{ route('admin.perubahan-status.reject', $perubahan) }}" class="flex gap-2" x-data>
                    @csrf
                    <input type="text" name="alasan_penolakan" placeholder="Alasan penolakan" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Tolak</button>
                </form>
            </div>
        @endif
    </div>
</x-layout>
