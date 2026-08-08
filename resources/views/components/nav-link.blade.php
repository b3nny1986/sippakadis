@props([
    'active' => false,
    'href' => '#',
    'icon' => null,
])

@php
    $classes = $active
        ? 'flex items-center gap-3 rounded-lg bg-brand-800 px-3 py-2 text-white'
        : 'flex items-center gap-3 rounded-lg px-3 py-2 text-brand-200 transition hover:bg-brand-800/60 hover:text-white';

    $paths = match ($icon) {
        'grid' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
        'car' => 'M8 7h8m-8 0a5 5 0 0010 0m-10 0a5 5 0 01-10 0m10 0H4m14 0h2a2 2 0 012 2v3m0 0v5a2 2 0 01-2 2h-1m-3 0a3 3 0 01-6 0m6 0h-6m-7-2H3a2 2 0 01-2-2v-5m18 0H3m18 0V9a2 2 0 00-2-2m-16 2v3',
        'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'building' => 'M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 7h2m0 0h2m-2 0v2m0-2V5m4 5h2m0 0v2m-2-2h-2m-6 8h2v3h-2v-3zm6 0h2v3h-2v-3zm-6-4h2v2h-2v-2zm6 0h2v2h-2v-2z',
        'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'refresh' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        'sync' => 'M4 4v6h6m10 10v-6h-6M4 10a8 8 0 0114-4.4M20 14a8 8 0 01-14 4.4',
        'report' => 'M9 17v-6m6 6V9m-9-3h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2z',
        'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'login' => 'M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3',
        'pencil' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        default => 'M4 4h16v16H4z',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->class($classes) }}>
    @if ($icon)
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $paths }}"/>
        </svg>
    @endif
    <span>{{ $slot }}</span>
</a>
