@props([
    'label',
    'name',
    'type' => 'text',
    'value' => '',
    'required' => false,
])

<div {{ $attributes->merge(['class' => '']) }}>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" @required($required)
           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
