@props([
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'message' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center py-12 px-6']) }}>
    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
        <x-ui.icon :name="$icon" class="w-7 h-7" />
    </div>
    <h3 class="font-semibold text-slate-700">{{ $title }}</h3>
    @if ($message)
        <p class="text-sm text-slate-500 mt-1 max-w-sm">{{ $message }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
