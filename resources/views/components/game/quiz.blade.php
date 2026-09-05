@props([
    'title',
    'subtitle' => '',
    'emoji' => '📋',
    'questions' => [],
    'completeUrl',
    'passEmoji' => '🎓',
    'passTitle' => 'Cerdas!',
    'failEmoji' => '💪',
    'failTitle' => 'Terus Belajar!',
])

<div class="max-w-2xl mx-auto" x-data="quizGame(@js($questions), @js($completeUrl))">
    <a href="{{ route('game.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-4">
        <x-ui.icon name="arrow-left" class="w-4 h-4" /> Belajar Sambil Bermain
    </a>

    <div class="flex items-center gap-3 mb-5">
        <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-xl">{{ $emoji }}</span>
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="text-sm text-slate-500">{{ $subtitle }}</p>
        </div>
    </div>

    {{-- ===== Kuis ===== --}}
    <template x-if="!done">
        <div>
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="font-medium text-slate-600">Soal <span x-text="index + 1"></span> / <span x-text="queue.length"></span></span>
                <span class="font-semibold text-brand-600">✓ <span x-text="correct"></span> benar</span>
            </div>
            <div class="h-2 rounded-full bg-slate-100 overflow-hidden mb-6">
                <div class="h-full bg-brand-500 transition-all duration-300" :style="`width: ${(index / queue.length) * 100}%`"></div>
            </div>

            <x-ui.card class="mb-4">
                <p class="text-lg font-bold text-slate-900 leading-snug" x-text="current.q"></p>
            </x-ui.card>

            <div class="space-y-3">
                <template x-for="(opt, i) in current.options" :key="opt">
                    <button type="button" @click="choose(opt)" :disabled="answered"
                            :class="{
                                'ring-emerald-400 bg-emerald-50 text-emerald-800': answered && opt === current.answer,
                                'ring-red-400 bg-red-50 text-red-800': answered && selected === opt && opt !== current.answer,
                                'ring-slate-200 bg-white hover:ring-brand-300 hover:bg-brand-50/50': !answered,
                                'ring-slate-200 bg-white opacity-60': answered && opt !== current.answer && selected !== opt,
                            }"
                            class="w-full flex items-center gap-3 rounded-2xl ring-2 px-4 py-3.5 text-left transition-all">
                        <span class="shrink-0 w-8 h-8 rounded-lg bg-slate-100 text-slate-500 font-bold flex items-center justify-center text-sm"
                              :class="{ 'bg-emerald-500 text-white': answered && opt === current.answer, 'bg-red-500 text-white': answered && selected === opt && opt !== current.answer }"
                              x-text="String.fromCharCode(65 + i)"></span>
                        <span class="font-medium text-slate-700 flex-1" x-text="opt"></span>
                        <span x-show="answered && opt === current.answer" x-cloak class="text-emerald-600">
                            <x-ui.icon name="check" class="w-5 h-5" />
                        </span>
                    </button>
                </template>
            </div>
        </div>
    </template>

    {{-- ===== Hasil ===== --}}
    <template x-if="done">
        <x-ui.card class="text-center py-10">
            <div class="text-6xl mb-3" x-text="correct >= passMark ? @js($passEmoji) : @js($failEmoji)"></div>
            <h2 class="text-2xl font-extrabold text-slate-900" x-text="correct >= passMark ? @js($passTitle) : @js($failTitle)"></h2>
            <p class="text-slate-500 mt-1">Kamu menjawab <span class="font-bold text-brand-600" x-text="correct"></span> dari <span x-text="queue.length"></span> soal dengan benar.</p>
            <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-brand-50 text-brand-700 px-4 py-1.5 text-sm font-semibold" x-show="xpMessage" x-cloak>
                <x-ui.icon name="star" class="w-4 h-4 text-amber-500" /> <span x-text="xpMessage"></span>
            </div>
            <div class="mt-6 flex items-center justify-center gap-3">
                <x-ui.button type="button" x-on:click="start()"><x-ui.icon name="sparkles" class="w-5 h-5" /> Main Lagi</x-ui.button>
                <x-ui.button href="{{ route('game.index') }}" variant="outline">Kembali</x-ui.button>
            </div>
        </x-ui.card>
    </template>
</div>
