<x-layout title="Pengajuan Penetapan #{{ $pengajuan->id }}">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Pengajuan #{{ $pengajuan->id }} &middot; {{ $pengajuan->kendaraan?->nopol }}</h2>
            <p class="text-sm text-slate-500">Tahun Pajak {{ $pengajuan->tahun_pajak }} &middot; Diajukan {{ $pengajuan->created_at?->format('d-m-Y H:i') }}</p>
        </div>

        <x-card title="Detail Pengajuan">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <x-dl-item label="NOPOL" :value="$pengajuan->kendaraan?->nopol" />
                <x-dl-item label="Status" :value="$pengajuan->status" />
                <x-dl-item label="Nomor Penetapan" :value="$pengajuan->nomor_penetapan" />
                <x-dl-item label="Diajukan Oleh" :value="$pengajuan->diajukanOleh?->name" />
                <x-dl-item label="Diproses Oleh" :value="$pengajuan->diprosesOleh?->name" />
                <x-dl-item label="Disetujui Pada" :value="$pengajuan->disetujui_at?->format('d-m-Y H:i')" />
            </dl>
            <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-semibold text-slate-700">Catatan:</p>
                <p>{{ $pengajuan->catatan ?? '-' }}</p>
            </div>
            @if ($pengajuan->alasan_penolakan)
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Alasan Penolakan:</p>
                    <p>{{ $pengajuan->alasan_penolakan }}</p>
                </div>
            @endif
        </x-card>
    </div>
</x-layout>
