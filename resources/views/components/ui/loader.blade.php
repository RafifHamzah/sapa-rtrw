{{-- Loading screen interaktif: rumah SAPA dengan daun tumbuh. Dihapus oleh app.js saat load. --}}
<div id="app-loader" aria-hidden="true">
    <div class="relative w-24 h-24 flex items-center justify-center">
        <svg class="loader-ring absolute inset-0 w-24 h-24 text-brand-200" viewBox="0 0 100 100" fill="none">
            <circle cx="50" cy="50" r="44" stroke="currentColor" stroke-width="4" stroke-dasharray="60 200" stroke-linecap="round" />
        </svg>
        <div class="relative">
            <x-app-brand-mark class="w-14 h-14" />
            {{-- Daun tumbuh --}}
            <svg class="leaf absolute -top-2 -right-2 w-7 h-7 text-brand-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 4C10 4 4 10 4 20c8 0 16-6 16-16Z" />
                <path d="M6 18c4-4 8-6 12-8" stroke="#ffffff" stroke-width="1.2" fill="none" stroke-linecap="round" />
            </svg>
        </div>
    </div>
    <p class="font-display font-bold text-brand-700 tracking-wide">SAPA</p>
    <p class="text-xs text-slate-400 -mt-2">Menyiapkan lingkungan warga…</p>
</div>
