@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-brand-500 disabled:opacity-50 disabled:pointer-events-none';

    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-700 active:bg-brand-800',
        'accent' => 'bg-accent-500 text-white shadow-sm hover:bg-accent-600',
        'outline' => 'bg-white text-brand-700 ring-1 ring-inset ring-brand-200 hover:bg-brand-50',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
        'soft' => 'bg-brand-50 text-brand-700 hover:bg-brand-100',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-700',
    ];

    $sizes = [
        'sm' => 'text-sm px-3 py-1.5',
        'md' => 'text-sm px-4 py-2.5',
        'lg' => 'text-base px-6 py-3',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
