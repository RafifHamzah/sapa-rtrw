@props(['user', 'light' => false])

@php
    $level = $user->level();
    $progress = $user->levelProgress();
@endphp

<div>
    <div class="flex items-center justify-between mb-1.5">
        <span class="inline-flex items-center gap-1.5 text-sm font-bold {{ $light ? 'text-white' : 'text-slate-800' }}">
            <x-ui.icon name="star" class="w-4 h-4 {{ $light ? 'text-amber-300' : 'text-amber-500' }}" />
            Level {{ $level }}
        </span>
        <span class="text-sm font-semibold {{ $light ? 'text-white/90' : 'text-brand-600' }}">{{ number_format($user->xp, 0, ',', '.') }} XP</span>
    </div>
    <div class="h-2.5 rounded-full overflow-hidden {{ $light ? 'bg-white/25' : 'bg-slate-100' }}">
        <div class="h-full rounded-full bg-gradient-to-r from-brand-400 to-brand-600 transition-all duration-700"
             style="width: {{ max(4, $progress) }}%"></div>
    </div>
    <p class="text-xs mt-1 {{ $light ? 'text-white/70' : 'text-slate-400' }}">
        {{ $user->xpToNextLevel() }} XP lagi menuju Level {{ $level + 1 }}
    </p>
</div>
