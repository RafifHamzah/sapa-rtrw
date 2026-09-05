@props(['badge', 'owned' => false])

@php
    $ring = [
        'primary' => 'ring-brand-200',
        'success' => 'ring-emerald-200',
        'info' => 'ring-sky-200',
        'warning' => 'ring-amber-200',
        'danger' => 'ring-red-200',
        'gray' => 'ring-slate-200',
    ][$badge->getColor()] ?? 'ring-brand-200';
@endphp

<div class="flex flex-col items-center text-center gap-2" title="{{ $badge->description() }}">
    <span @class([
        'w-16 h-16 rounded-2xl flex items-center justify-center transition-transform',
        'bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-soft ring-2 ' . $ring . ' hover:scale-105' => $owned,
        'bg-slate-100 text-slate-300' => ! $owned,
    ])>
        <x-ui.icon :name="$badge->icon()" class="w-8 h-8" />
    </span>
    <div class="min-h-[2rem]">
        <p @class([
            'text-xs font-semibold leading-tight',
            'text-slate-800' => $owned,
            'text-slate-400' => ! $owned,
        ])>{{ $badge->getLabel() }}</p>
        @unless ($owned)
            <p class="text-[10px] text-slate-300">Terkunci</p>
        @endunless
    </div>
</div>
