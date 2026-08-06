@props(['value' => null, 'color' => null])

@php
    if (! $color) {
        $color = match ($value) {
            'LEWAT' => 'bg-red-100 text-red-700',
            'HARI_H', 'H1' => 'bg-amber-100 text-amber-700',
            'H7', 'H14', 'H30' => 'bg-yellow-100 text-yellow-700',
            'AMAN' => 'bg-emerald-100 text-emerald-700',
            'Menunggu' => 'bg-amber-100 text-amber-700',
            'Diproses' => 'bg-sky-100 text-sky-700',
            'Disetujui' => 'bg-emerald-100 text-emerald-700',
            'Ditolak' => 'bg-red-100 text-red-700',
            'Selesai' => 'bg-slate-200 text-slate-700',
            'Sukses', 'Ditemukan' => 'bg-emerald-100 text-emerald-700',
            'Gagal' => 'bg-red-100 text-red-700',
            'Tidak Ditemukan' => 'bg-slate-200 text-slate-600',
            default => 'bg-slate-100 text-slate-600',
        };
    }
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ' . $color]) }}>
    {{ $slot }}
</span>
