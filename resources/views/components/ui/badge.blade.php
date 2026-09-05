@props([
    'color' => 'gray',
    'icon' => null,
])

@php
    $map = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'primary' => 'bg-brand-50 text-brand-700 ring-brand-600/20',
        'gray' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
    ];
    $classes = $map[$color] ?? $map['gray'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ' . $classes]) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" class="w-3.5 h-3.5" />
    @endif
    {{ $slot }}
</span>
