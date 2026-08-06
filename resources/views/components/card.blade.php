@props(['title' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm']) }}>
    @if ($title)
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-bold text-slate-800">{{ $title }}</h2>
        </div>
    @endif
    <div class="p-5">
        {{ $slot }}
    </div>
    @if ($footer)
        <div class="border-t border-slate-100 px-5 py-3 text-sm">
            {{ $footer }}
        </div>
    @endif
</div>
