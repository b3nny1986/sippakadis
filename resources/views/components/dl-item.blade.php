@props(['label', 'value' => null])

<dt class="text-slate-500">{{ $label }}</dt>
<dd class="font-medium text-slate-800">{{ $value ?? '-' }}</dd>
