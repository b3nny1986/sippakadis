<x-layout title="Pengajuan Penetapan #{{ $pengajuan->id }}">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900">
                    Penetapan #{{ $pengajuan->id }} &middot; {{ $pengajuan->kendaraan?->nopol }}
                </h2>
                <p class="text-sm text-slate-500">Tahun Pajak {{ $pengajuan->tahun_pajak }} &middot; Diajukan {{ $pengajuan->created_at?->format('d-m-Y H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($pengajuan->status === \App\Models\PengajuanPenetapan::MENUNGGU)
                    <form method="POST" action="{{ route('admin.penetapan.proses', $pengajuan) }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Proses</button>
                    </form>
                @endif
                @if (! $pengajuan->isFinal())
                    <form method="POST" action="{{ route('admin.penetapan.approve', $pengajuan) }}" onsubmit="return confirm('Setujui pengajuan ini?')">
                        @csrf
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Setujui</button>
                    </form>
                @endif
                @if ($pengajuan->status === \App\Models\PengajuanPenetapan::DISETUJUI)
                    <a href="{{ route('admin.penetapan.cetak', $pengajuan) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Cetak PDF</a>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <x-card title="Data Pengajuan" class="lg:col-span-2">
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <x-dl-item label="NOPOL" :value="$pengajuan->kendaraan?->nopol" />
                    <x-dl-item label="OPD" :value="$pengajuan->opd?->nama" />
                    <x-dl-item label="Tahun Pajak" :value="$pengajuan->tahun_pajak" />
                    <x-dl-item label="Status" :value="$pengajuan->status" />
                    <x-dl-item label="Nomor Penetapan" :value="$pengajuan->nomor_penetapan" />
                    <x-dl-item label="Diajukan Oleh" :value="$pengajuan->diajukanOleh?->name" />
                    <x-dl-item label="Diproses Oleh" :value="$pengajuan->diprosesOleh?->name" />
                    <x-dl-item label="Disetujui Oleh" :value="$pengajuan->disetujuiOleh?->name" />
                    <x-dl-item label="Disetujui Pada" :value="$pengajuan->disetujui_at?->format('d-m-Y H:i')" />
                    <x-dl-item label="Alasan Penolakan" :value="$pengajuan->alasan_penolakan" />
                </dl>
                <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-700">Catatan Pengaju:</p>
                    <p>{{ $pengajuan->catatan ?? '-' }}</p>
                </div>
            </x-card>

            <x-card title="Aksi Admin">
                <form method="POST" action="{{ route('admin.penetapan.reject', $pengajuan) }}" class="space-y-3">
                    @csrf
                    <label class="mb-1 block text-sm font-medium text-slate-700">Alasan Penolakan</label>
                    <textarea name="alasan_penolakan" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none" {{ $pengajuan->isFinal() ? 'disabled' : '' }}></textarea>
                    <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700" {{ $pengajuan->isFinal() ? 'disabled' : '' }}>
                        Tolak Pengajuan
                    </button>
                </form>
            </x-card>
        </div>
    </div>
</x-layout>
