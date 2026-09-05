<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-xl">🎮</span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Belajar Sambil Bermain</h1>
                <p class="text-sm text-slate-500">Main game edukasi, kumpulkan XP, jadi warga cerdas lingkungan.</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($games as $game)
            @if ($game['available'])
                <a href="{{ $game['route'] }}" class="card-surface p-6 hover:shadow-soft hover:-translate-y-1 transition-all">
                    <div class="text-4xl mb-3">{{ $game['emoji'] }}</div>
                    <h3 class="font-bold text-slate-900">{{ $game['title'] }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ $game['desc'] }}</p>
                    <span class="inline-flex items-center gap-1.5 mt-4 text-sm font-semibold text-brand-600">
                        Main sekarang <x-ui.icon name="chevron-right" class="w-4 h-4" />
                    </span>
                </a>
            @else
                <div class="card-surface p-6 opacity-60">
                    <div class="text-4xl mb-3 grayscale">{{ $game['emoji'] }}</div>
                    <h3 class="font-bold text-slate-700">{{ $game['title'] }}</h3>
                    <p class="text-sm text-slate-400 mt-1">{{ $game['desc'] }}</p>
                    <span class="inline-block mt-4 text-xs font-medium text-slate-400 bg-slate-100 rounded-full px-2.5 py-1">Segera hadir</span>
                </div>
            @endif
        @endforeach
    </div>
</x-app-layout>
