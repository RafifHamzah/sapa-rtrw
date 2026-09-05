{{-- Floating Action Button (speed-dial) — akses cepat layanan warga. --}}
<div x-data="{ open: false }" @keydown.escape.window="open = false"
     class="fixed right-4 bottom-24 md:bottom-6 z-40 flex flex-col items-end gap-3">

    {{-- Aksi cepat --}}
    <div x-show="open" x-cloak class="flex flex-col items-end gap-3"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0">
        @php
            $actions = [
                ['route' => 'complaints.index', 'label' => 'Lapor', 'icon' => 'alert'],
                ['route' => 'letters.index', 'label' => 'Ajukan Surat', 'icon' => 'document'],
                ['route' => 'iuran.index', 'label' => 'Bayar Iuran', 'icon' => 'receipt'],
            ];
        @endphp
        @foreach ($actions as $a)
            <a href="{{ route($a['route']) }}" class="flex items-center gap-2 group">
                <span class="glass text-slate-700 text-sm font-medium px-3 py-1.5 rounded-xl shadow-sm">{{ $a['label'] }}</span>
                <span class="w-11 h-11 rounded-full bg-white text-brand-600 ring-1 ring-brand-100 shadow-card flex items-center justify-center group-hover:bg-brand-50 transition-colors">
                    <x-ui.icon :name="$a['icon']" class="w-5 h-5" />
                </span>
            </a>
        @endforeach
    </div>

    {{-- Tombol utama --}}
    <button @click="open = !open" :aria-expanded="open" aria-label="Aksi cepat"
            class="w-14 h-14 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-card flex items-center justify-center hover:shadow-soft transition-all">
        <x-ui.icon name="plus" class="w-7 h-7 transition-transform duration-200" x-bind:class="open ? 'rotate-45' : ''" />
    </button>
</div>
