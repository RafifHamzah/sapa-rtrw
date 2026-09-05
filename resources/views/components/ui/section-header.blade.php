@props([
    'title' => '',
    'subtitle' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'flex items-end justify-between gap-4 mb-4']) }}>
    <div class="flex items-center gap-3 min-w-0">
        @if ($icon)
            <span class="shrink-0 w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                <x-ui.icon :name="$icon" class="w-5 h-5" />
            </span>
        @endif
        <div class="min-w-0">
            <h2 class="text-lg font-bold text-slate-900 truncate">{{ $title }}</h2>
            @if ($subtitle)
                <p class="text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if (isset($action))
        <div class="shrink-0">{{ $action }}</div>
    @endif
</div>
