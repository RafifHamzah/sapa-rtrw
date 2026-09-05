<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.favicons')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'RT/RW Digital') }}</title>

    {{-- Terapkan mode inklusif sedini mungkin agar tidak berkedip. --}}
    <script>
        if (localStorage.getItem('inclusive') === '1') {
            document.documentElement.classList.add('inclusive');
        }
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:500,600,700,800|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen bg-slate-50">
    <x-ui.loader />
    <x-ui.toaster />
    @php
        $nav = [
            ['route' => 'dashboard', 'label' => 'Beranda', 'icon' => 'home'],
            ['route' => 'kas.index', 'label' => 'Kas', 'icon' => 'wallet'],
            ['route' => 'iuran.index', 'label' => 'Iuran', 'icon' => 'receipt'],
            ['route' => 'letters.index', 'label' => 'Surat', 'icon' => 'document'],
            ['route' => 'announcements.index', 'label' => 'Info', 'icon' => 'megaphone'],
            ['route' => 'complaints.index', 'label' => 'Lapor', 'icon' => 'alert'],
        ];
    @endphp

    {{-- ===== Top navigation ===== --}}
    <header class="sticky top-0 z-40 glass-nav border-b border-slate-100/80">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex h-16 items-center justify-between gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0">
                    <x-app-brand-mark class="w-9 h-9" />
                    <x-app-wordmark class="h-7 sm:h-8" />
                </a>

                <nav class="hidden md:flex items-center gap-1">
                    @foreach ($nav as $item)
                        <a href="{{ route($item['route']) }}"
                           @class([
                               'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                               'bg-brand-50 text-brand-700' => request()->routeIs($item['route'] . '*'),
                               'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs($item['route'] . '*'),
                           ])>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-1.5">
                    {{-- Lonceng notifikasi --}}
                    <x-ui.notification-bell />

                    {{-- Toggle Mode Inklusif --}}
                    <button type="button"
                            x-data
                            @click="$store.inclusive.toggle()"
                            :class="$store.inclusive.on ? 'bg-brand-50 text-brand-700 ring-brand-200' : 'text-slate-500 ring-slate-200 hover:bg-slate-100'"
                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl ring-1 transition-colors"
                            title="Mode Inklusif (perbesar teks)" aria-label="Aktifkan mode inklusif">
                        <x-ui.icon name="adjust" class="w-5 h-5" />
                    </button>

                    {{-- User menu --}}
                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-xl py-1.5 pl-1.5 pr-2.5 hover:bg-slate-100 transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center font-semibold text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden sm:block text-sm font-medium text-slate-700 max-w-[8rem] truncate">{{ auth()->user()->name }}</span>
                        </button>
                        <div x-show="open" x-transition @click.outside="open = false" x-cloak
                             class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-card ring-1 ring-slate-100 p-1.5 z-50">
                            <div class="px-3 py-2 border-b border-slate-100 mb-1">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="px-3 py-1.5">
                                <x-ui.xp-bar :user="auth()->user()" />
                            </div>
                            <a href="{{ route('profile.show') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100">
                                <x-ui.icon name="trophy" class="w-5 h-5" /> Profil &amp; Prestasi
                            </a>
                            <a href="{{ route('leaderboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100">
                                <x-ui.icon name="star" class="w-5 h-5" /> Papan Peringkat
                            </a>
                            <a href="{{ route('game.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100">
                                <span class="w-5 h-5 flex items-center justify-center text-base leading-none">🎮</span> Belajar Sambil Bermain
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100">
                                <x-ui.icon name="user" class="w-5 h-5" /> Pengaturan Akun
                            </a>
                            <button type="button" onclick="window.sapaInstall && window.sapaInstall()"
                                    class="pwa-install-item w-full items-center gap-2 px-3 py-2 rounded-xl text-sm text-brand-700 bg-brand-50 hover:bg-brand-100 mt-1">
                                <x-ui.icon name="download" class="w-5 h-5" /> Pasang Aplikasi
                            </button>
                            <div class="my-1 border-t border-slate-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-red-600 hover:bg-red-50">
                                    <x-ui.icon name="logout" class="w-5 h-5" /> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- ===== Page header (optional) ===== --}}
    @isset($header)
        <div class="bg-white border-b border-slate-100">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 py-5">
                {{ $header }}
            </div>
        </div>
    @endisset

    {{-- ===== Content ===== --}}
    <main class="mx-auto max-w-6xl px-4 sm:px-6 py-6 pb-28 md:pb-10">
        {{ $slot }}
    </main>

    {{-- Floating action button + asisten AI --}}
    <x-ui.fab />
    <x-ui.assistant />

    {{-- ===== Mobile bottom tab bar ===== --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-100 pb-[env(safe-area-inset-bottom)]">
        <div class="grid grid-cols-6">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   @class([
                       'flex flex-col items-center gap-0.5 py-2 text-[10px] font-medium transition-colors',
                       'text-brand-600' => request()->routeIs($item['route'] . '*'),
                       'text-slate-400' => ! request()->routeIs($item['route'] . '*'),
                   ])>
                    <x-ui.icon :name="$item['icon']" class="w-6 h-6" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('inclusive', {
                on: localStorage.getItem('inclusive') === '1',
                toggle() {
                    this.on = !this.on;
                    localStorage.setItem('inclusive', this.on ? '1' : '0');
                    document.documentElement.classList.toggle('inclusive', this.on);
                },
            });
        });
    </script>

    {{-- Flash → toast + confetti --}}
    @if (session('status'))
        <script>
            window.addEventListener('load', () => {
                setTimeout(() => window.toast && window.toast(@json(session('status')), 'success'), 400);
                @if (session('celebrate'))
                    setTimeout(() => window.confettiBurst && window.confettiBurst(), 500);
                @endif
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            window.addEventListener('load', () => setTimeout(() => window.toast && window.toast(@json(session('error')), 'error'), 400));
        </script>
    @endif

    @stack('scripts')
</body>
</html>
