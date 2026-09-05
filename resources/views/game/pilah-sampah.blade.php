<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('game.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-4">
            <x-ui.icon name="arrow-left" class="w-4 h-4" /> Belajar Sambil Bermain
        </a>

        <div class="flex items-center gap-3 mb-5">
            <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-xl">🗑️</span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Pilah Sampah 3D</h1>
                <p class="text-sm text-slate-500">Seret sampah ke tempat yang benar — organik, anorganik, atau B3.</p>
            </div>
        </div>

        <div id="ps-game" data-complete-url="{{ route('game.complete', 'pilah-sampah') }}">
            {{-- ===== Panggung permainan ===== --}}
            <div id="ps-stage">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="font-medium text-slate-600">Sampah <span id="ps-progress-text">1 / 9</span></span>
                    <span class="font-semibold text-brand-600">✓ <span id="ps-correct">0</span> benar</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden mb-4">
                    <div id="ps-progress-bar" class="h-full bg-brand-500 transition-all duration-300" style="width: 0%"></div>
                </div>

                {{-- Kanvas 3D --}}
                <div id="ps-canvas" class="relative w-full h-[62vh] min-h-[380px] max-h-[560px] rounded-3xl overflow-hidden ring-1 ring-slate-200 shadow-card bg-brand-50">
                    <div id="ps-fallback" class="hidden absolute inset-0 flex flex-col items-center justify-center text-center p-6">
                        <div class="text-5xl mb-3">🌐</div>
                        <p class="font-semibold text-slate-700">Mode 3D tidak didukung di perangkat ini.</p>
                        <p class="text-sm text-slate-500 mt-1">Coba buka lewat browser lain (Chrome/Safari terbaru) untuk pengalaman penuh.</p>
                    </div>
                </div>

                <p id="ps-hint" class="text-sm font-medium text-center text-slate-500 mt-4">Seret sampah ke tempat yang benar</p>
            </div>

            {{-- ===== Hasil ===== --}}
            <div id="ps-results" class="hidden">
                <x-ui.card class="text-center py-10">
                    <div id="ps-results-emoji" class="text-6xl mb-3">🏆</div>
                    <h2 id="ps-results-title" class="text-2xl font-extrabold text-slate-900">Hebat!</h2>
                    <p id="ps-results-sub" class="text-slate-500 mt-1"></p>
                    <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-brand-50 text-brand-700 px-4 py-1.5 text-sm font-semibold">
                        <x-ui.icon name="star" class="w-4 h-4 text-amber-500" /> <span id="ps-xp"></span>
                    </div>
                    <div class="mt-6 flex items-center justify-center gap-3">
                        <button id="ps-restart" type="button"
                                class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700">
                            <x-ui.icon name="sparkles" class="w-5 h-5" /> Main Lagi
                        </button>
                        <x-ui.button href="{{ route('game.index') }}" variant="outline">Kembali</x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/games/pilah-sampah-3d.js')
    @endpush
</x-app-layout>
