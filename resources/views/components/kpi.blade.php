@props([
    'label' => '',
    'value' => 0,
    'tone' => 'brand',
])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700',
        'emerald' => 'bg-emerald-50 text-emerald-700',
        'red' => 'bg-red-50 text-red-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'sky' => 'bg-sky-50 text-sky-700',
    ];
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($value) }}</p>
        </div>
        <div class="rounded-xl {{ $tones[$tone] }} p-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon ?? 'M4 4v16h16M8 16l4-4 4 4' }}"/>
            </svg>
        </div>
    </div>
</div>
