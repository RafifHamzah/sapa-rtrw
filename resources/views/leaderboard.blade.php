<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center"><x-ui.icon name="trophy" class="w-6 h-6" /></span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Papan Peringkat Warga</h1>
                <p class="text-sm text-slate-500">Warga paling aktif berdasarkan XP.</p>
            </div>
        </div>
    </x-slot>

    @php $podium = $leaders->take(3); $rest = $leaders->slice(3)->values(); @endphp

    {{-- Podium 3 besar --}}
    @if ($podium->isNotEmpty())
        <div class="grid grid-cols-3 gap-3 sm:gap-5 items-end mb-6">
            @php $order = [1 => 'order-1', 0 => 'order-2', 2 => 'order-3']; $heights = [0 => 'sm:pt-4', 1 => 'sm:pt-10', 2 => 'sm:pt-14']; @endphp
            @foreach ($podium as $i => $leader)
                <div class="{{ $order[$i] ?? '' }} {{ $heights[$i] ?? '' }}">
                    <div @class([
                        'card-surface p-4 text-center relative',
                        'ring-2 ring-amber-300' => $i === 0,
                    ])>
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span @class([
                                'w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-extrabold shadow',
                                'bg-amber-400' => $i === 0,
                                'bg-slate-300' => $i === 1,
                                'bg-amber-700' => $i === 2,
                            ])>{{ $i + 1 }}</span>
                        </div>
                        <span class="mx-auto mt-3 w-14 h-14 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-xl font-extrabold">
                            {{ strtoupper(substr($leader->name, 0, 1)) }}
                        </span>
                        <p class="mt-2 text-sm font-bold text-slate-800 truncate">{{ $leader->name }}</p>
                        <p class="text-xs text-slate-400">Level {{ $leader->level() }} · {{ $leader->badges_count }} badge</p>
                        <p class="mt-1 text-sm font-extrabold text-brand-600">{{ number_format($leader->xp, 0, ',', '.') }} XP</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Sisa peringkat --}}
    <x-ui.card padding="p-0">
        @forelse ($rest as $i => $leader)
            <div @class([
                'flex items-center gap-4 px-5 py-3.5',
                'border-b border-slate-50' => ! $loop->last,
                'bg-brand-50/60' => $leader->id === $currentUserId,
            ])>
                <span class="w-7 text-center font-bold text-slate-400">{{ $i + 4 }}</span>
                <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-semibold shrink-0">
                    {{ strtoupper(substr($leader->name, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-800 truncate">
                        {{ $leader->name }}
                        @if ($leader->id === $currentUserId)<span class="text-xs text-brand-600 font-medium">(Anda)</span>@endif
                    </p>
                    <p class="text-xs text-slate-400">Level {{ $leader->level() }} · {{ $leader->badges_count }} badge</p>
                </div>
                <span class="text-sm font-bold text-brand-600 shrink-0">{{ number_format($leader->xp, 0, ',', '.') }} XP</span>
            </div>
        @empty
            @if ($podium->isEmpty())
                <x-ui.empty-state icon="trophy" title="Papan peringkat masih kosong"
                    message="Peringkat muncul saat warga mulai beraktivitas." />
            @endif
        @endforelse
    </x-ui.card>
</x-app-layout>
