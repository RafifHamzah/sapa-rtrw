@props([
    'label' => '',
    'value' => '',
    'icon' => null,
    'tone' => 'brand', // brand | emerald | amber | red | slate
    'sub' => null,
])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'red' => 'bg-red-50 text-red-600',
        'slate' => 'bg-slate-100 text-slate-600',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card-surface p-5 flex items-start gap-4']) }}>
    @if ($icon)
        <div class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center {{ $tones[$tone] ?? $tones['brand'] }}">
            <x-ui.icon :name="$icon" class="w-6 h-6" />
        </div>
    @endif
    <div class="min-w-0">
        <p class="text-sm text-slate-500">{{ $label }}</p>
        <p class="text-xl sm:text-2xl font-display font-bold text-slate-900 truncate">{{ $value }}</p>
        @if ($sub)
            <p class="text-xs text-slate-400 mt-0.5">{{ $sub }}</p>
        @endif
    </div>
</div>
