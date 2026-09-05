{{-- Kontainer toast global (Alpine store 'toasts'). Panggil window.toast(pesan, tipe). --}}
<div class="fixed top-4 right-4 z-[90] flex flex-col gap-2 w-[calc(100%-2rem)] max-w-sm pointer-events-none"
     x-data
     aria-live="polite" aria-atomic="true">
    <template x-for="t in $store.toasts.items" :key="t.id">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 translate-x-4"
             class="pointer-events-auto flex items-start gap-3 rounded-2xl px-4 py-3 shadow-card ring-1 glass"
             :class="{
                 'ring-emerald-200': t.type === 'success',
                 'ring-red-200': t.type === 'error',
                 'ring-sky-200': t.type === 'info',
             }">
            <span class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-white"
                  :class="{
                      'bg-emerald-500': t.type === 'success',
                      'bg-red-500': t.type === 'error',
                      'bg-sky-500': t.type === 'info',
                  }">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <template x-if="t.type === 'success'"><path d="M20 6 9 17l-5-5" /></template>
                    <template x-if="t.type === 'error'"><path d="M18 6 6 18M6 6l12 12" /></template>
                    <template x-if="t.type === 'info'"><path d="M12 16v-4m0-4h.01" /></template>
                </svg>
            </span>
            <p class="text-sm text-slate-700 font-medium flex-1" x-text="t.message"></p>
            <button @click="$store.toasts.remove(t.id)" class="text-slate-400 hover:text-slate-600 shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
