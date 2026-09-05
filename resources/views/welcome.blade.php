<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    @include('partials.favicons')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Progressive enhancement: set .js sedini mungkin agar state awal scroll-reveal
         (opacity:0) berlaku tanpa "flash" konten. Tanpa JS → class ini tak pernah ada → konten tampil. --}}
    <script>document.documentElement.classList.add('js');</script>
    <title>SAPA — Semua Urusan Warga, dalam Satu Sapa</title>
    @include('partials.meta')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:500,600,700,800|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-slate-800">
    <x-ui.loader />

    {{-- ===== Nav ===== --}}
    <header x-data="{ open: false, scrolled: false }" x-init="scrolled = window.scrollY > 40"
            @scroll.window="scrolled = window.scrollY > 40"
            class="fixed inset-x-0 top-0 z-40 transition-colors duration-300"
            :class="scrolled || open ? 'glass-nav border-b border-slate-100/80' : 'bg-transparent'">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex h-16 items-center justify-between">
                <a href="#" class="flex items-center gap-2.5">
                    <x-app-brand-mark class="w-9 h-9" />
                    {{--
                        WORDMARK "SAPA" — selalu versi WARNA penuh (public/images/sapa-wordmark.png), tanpa alas.
                        `drop-shadow` glow putih tipis dipakai agar sisi hijau-tua tetap kebaca di atas hero gelap;
                        di nav putih glow ini tak terlihat. Ganti wordmark: cukup timpa file itu lalu refresh.
                        Alt "SAPA" jadi cadangan bila gambar gagal muat.
                    --}}
                    <img src="{{ asset('images/sapa-wordmark.png') }}" alt="SAPA"
                         class="h-8 sm:h-9 w-auto drop-shadow-[0_1px_5px_rgba(255,255,255,0.5)]">
                </a>
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium transition-colors" :class="scrolled ? 'text-slate-600' : 'text-white/90'">
                    <a href="#masalah" class="nav-link hover:text-brand-400">Kenapa</a>
                    <a href="#fitur" class="nav-link hover:text-brand-400">Fitur</a>
                    <a href="#cara" class="nav-link hover:text-brand-400">Cara Kerja</a>
                </nav>
                <div class="hidden md:flex items-center gap-2">
                    <x-ui.button href="{{ route('login') }}" variant="ghost" size="sm" x-bind:class="!scrolled && '!text-white hover:!bg-white/10'">Masuk</x-ui.button>
                    <x-ui.button href="{{ route('register') }}" size="sm">Daftar Warga</x-ui.button>
                </div>
                <button @click="open = !open" class="md:hidden p-2 transition-colors" :class="scrolled || open ? 'text-slate-600' : 'text-white'" aria-label="Menu">
                    <x-ui.icon name="menu" class="w-6 h-6" />
                </button>
            </div>
        </div>
        <div x-show="open" x-cloak class="md:hidden border-t border-slate-100 bg-white px-4 py-4 space-y-2">
            <a href="#fitur" @click="open=false" class="block py-2 text-slate-600">Fitur</a>
            <a href="#cara" @click="open=false" class="block py-2 text-slate-600">Cara Kerja</a>
            <div class="flex gap-2 pt-2">
                <x-ui.button href="{{ route('login') }}" variant="outline" class="flex-1">Masuk</x-ui.button>
                <x-ui.button href="{{ route('register') }}" class="flex-1">Daftar</x-ui.button>
            </div>
        </div>
    </header>

    {{-- ===== Hero ===== --}}
    <section class="relative overflow-hidden bg-slate-900 min-h-screen flex items-center">
        {{-- Foto latar: petani di sawah --}}
        <div class="absolute inset-0" style="background-image:url('{{ asset('images/hero-petani.jpg') }}');background-size:cover;background-position:center 22%;background-repeat:no-repeat"></div>
        {{-- Scrim gelap: kiri pekat agar teks putih kontras, plus vignette bawah --}}
        <div class="absolute inset-0" style="background:linear-gradient(to right, rgba(11,18,30,0.92) 0%, rgba(11,18,30,0.72) 38%, rgba(11,18,30,0.25) 66%, rgba(11,18,30,0) 100%)"></div>
        <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(11,18,30,0.6) 0%, rgba(11,18,30,0) 42%)"></div>

        <div class="relative mx-auto max-w-6xl px-4 sm:px-6 py-20 sm:py-28 w-full">
            <div class="max-w-xl animate-fade-in-up">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 text-white ring-1 ring-white/25 backdrop-blur px-3 py-1 text-xs font-semibold">
                    <x-ui.icon name="shield" class="w-4 h-4" /> SAPA · Transparan &amp; Inklusif
                </span>
                <h1 class="mt-5 text-4xl sm:text-6xl font-extrabold leading-[1.05] text-white drop-shadow">
                    Semua urusan warga,<br><span class="text-brand-300">dalam satu sapa.</span>
                </h1>
                <p class="mt-5 text-lg text-slate-200 max-w-lg">
                    <strong class="text-white">SAPA</strong> — Sistem Administrasi dan Pelayanan Antarwarga. Kas transparan, iuran online, surat pengantar ber-QR, pengumuman, dan lapor warga dalam satu aplikasi.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-ui.button href="{{ route('register') }}" size="lg">
                        Mulai Sekarang <x-ui.icon name="arrow-right" class="w-5 h-5" />
                    </x-ui.button>
                    <x-ui.button href="{{ route('login') }}" variant="outline" size="lg">Masuk Akun</x-ui.button>
                </div>

                {{-- Statistik jadi chip kaca --}}
                <div class="mt-10 grid grid-cols-3 gap-3 max-w-md">
                    <div class="rounded-2xl bg-white/10 ring-1 ring-white/20 backdrop-blur px-4 py-3">
                        <p class="text-2xl font-extrabold text-white">{{ $stats['residents'] }}</p>
                        <p class="text-xs text-slate-300">Warga terdata</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 ring-1 ring-white/20 backdrop-blur px-4 py-3">
                        <p class="text-2xl font-extrabold text-white">{{ $stats['transactions'] }}</p>
                        <p class="text-xs text-slate-300">Transaksi kas</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 ring-1 ring-white/20 backdrop-blur px-4 py-3">
                        <p class="text-2xl font-extrabold text-white">{{ $stats['letters'] }}</p>
                        <p class="text-xs text-slate-300">Surat terbit</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grafis kaca mengambang (desktop) --}}
        <div class="hidden lg:flex flex-col gap-3 absolute bottom-10 right-8 xl:right-16 animate-fade-in-up">
            <div class="glass rounded-2xl px-5 py-4 w-60">
                <p class="text-xs text-slate-500">Saldo Kas RT</p>
                <p class="text-2xl font-extrabold text-slate-900">Rp 12.450.000</p>
            </div>
            <div class="glass rounded-2xl px-4 py-3 w-60 flex items-center gap-2 self-end -mr-4">
                <span class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0"><x-ui.icon name="check" class="w-5 h-5" /></span>
                <div><p class="text-xs font-semibold text-slate-800">Surat Terverifikasi</p><p class="text-[10px] text-slate-500">001/DOM/RT04/2026</p></div>
            </div>
        </div>
    </section>

    {{-- ===== Masalah → Solusi ===== --}}
    <section id="masalah" class="mx-auto max-w-6xl px-4 sm:px-6 py-16 sm:py-20">
        <div data-reveal class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100 px-3 py-1 text-xs font-semibold">
                <x-ui.icon name="sparkles" class="w-4 h-4" /> Kenapa SAPA
            </span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">Dari ribet jadi rapi</h2>
            <p class="mt-3 text-slate-600">Tiap keluhan warga hari ini sudah ada jawabannya di SAPA. Lihat perbandingannya langsung.</p>
        </div>

        @php
            // Tiap keluhan dipasangkan 1:1 dengan solusinya; 'icon' = kategori layanan.
            $pairs = [
                ['icon' => 'chart',     'problem' => 'Kas dicatat manual, warga sulit tahu ke mana uang mengalir', 'solution' => 'Buku kas terbuka & real-time untuk semua warga'],
                ['icon' => 'receipt',   'problem' => 'Bayar iuran harus ketemu bendahara, sering telat',        'solution' => 'Iuran dibayar online lewat Midtrans, kapan saja'],
                ['icon' => 'document',  'problem' => 'Minta surat pengantar ribet & lama',                       'solution' => 'Ajukan surat & unduh PDF ber-QR resmi sendiri'],
                ['icon' => 'megaphone', 'problem' => 'Pengumuman tersebar di banyak grup chat',                  'solution' => 'Satu feed pengumuman, yang penting disematkan'],
                ['icon' => 'alert',     'problem' => 'Laporan warga tak jelas tindak lanjutnya',                 'solution' => 'Lapor warga dengan foto & lacak status penanganan'],
            ];
        @endphp

        {{-- Label kolom (desktop): Tanpa SAPA ⟶ Dengan SAPA --}}
        <div data-reveal class="hidden md:grid grid-cols-[1fr_auto_1fr] gap-4 items-center mt-12 mb-5">
            <div class="flex items-center gap-2.5 font-bold text-slate-500">
                <span class="flex w-8 h-8 items-center justify-center rounded-xl bg-red-100 text-red-500"><x-ui.icon name="x" class="w-5 h-5" /></span>
                Tanpa SAPA
            </div>
            <div class="w-10" aria-hidden="true"></div>
            <div class="flex items-center gap-2.5 font-bold text-brand-700">
                <span class="flex w-8 h-8 items-center justify-center rounded-xl bg-brand-100 text-brand-600"><x-ui.icon name="check" class="w-5 h-5" /></span>
                Dengan SAPA
            </div>
        </div>

        {{-- Tiap baris: 2 kartu terpisah (masalah + solusi). Tiap kartu punya
             sorotan cahaya yang mengikuti kursor (spotlight). --}}
        <div class="space-y-4 md:space-y-3">
            @foreach ($pairs as $pair)
                <div data-reveal style="transition-delay: {{ $loop->index * 70 }}ms"
                     class="group grid md:grid-cols-[1fr_auto_1fr] gap-3 md:gap-4 items-stretch">
                    {{-- Kartu masalah --}}
                    <div class="spot-card flex items-start gap-3 rounded-2xl bg-slate-50 ring-1 ring-slate-200/70 p-5"
                         style="--spot: rgba(52,211,153,0.20)"
                         @mousemove="const r = $el.getBoundingClientRect();
                                     $el.style.setProperty('--mx', ($event.clientX - r.left) + 'px');
                                     $el.style.setProperty('--my', ($event.clientY - r.top) + 'px')">
                        <div class="spotlight"></div>
                        <span class="relative mt-0.5 flex w-10 h-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-400 ring-1 ring-slate-100">
                            <x-ui.icon :name="$pair['icon']" class="w-5 h-5" />
                        </span>
                        <p class="relative text-slate-500">{{ $pair['problem'] }}</p>
                    </div>

                    {{-- Panah transformasi: ke bawah di mobile, ke kanan di desktop --}}
                    <div class="flex items-center justify-center" aria-hidden="true">
                        <span class="flex w-9 h-9 items-center justify-center rounded-full bg-white text-brand-600 shadow-card ring-1 ring-slate-100 rotate-90 md:rotate-0 transition-transform duration-200 md:group-hover:translate-x-0.5">
                            <x-ui.icon name="arrow-right" class="w-5 h-5" />
                        </span>
                    </div>

                    {{-- Kartu solusi --}}
                    <div class="spot-card flex items-start gap-3 rounded-2xl bg-brand-600 text-white p-5 shadow-soft"
                         style="--spot: rgba(255,255,255,0.30)"
                         @mousemove="const r = $el.getBoundingClientRect();
                                     $el.style.setProperty('--mx', ($event.clientX - r.left) + 'px');
                                     $el.style.setProperty('--my', ($event.clientY - r.top) + 'px')">
                        <div class="spotlight"></div>
                        <div aria-hidden="true" class="pointer-events-none absolute -right-6 -bottom-8 w-28 h-28 rounded-full bg-white/10 blur-xl"></div>
                        <span class="relative mt-0.5 flex w-10 h-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/20">
                            <x-ui.icon :name="$pair['icon']" class="w-5 h-5" />
                        </span>
                        <p class="relative font-medium text-brand-50">{{ $pair['solution'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Fitur ===== --}}
    <section id="fitur" class="mx-auto max-w-6xl px-4 sm:px-6 py-16"
             x-data="{ open: false, current: 0 }"
             x-effect="document.body.style.overflow = open ? 'hidden' : ''">
        <div data-reveal class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100 px-3 py-1 text-xs font-semibold">
                <x-ui.icon name="sparkles" class="w-4 h-4" /> Fitur unggulan
            </span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">Semua yang warga butuhkan</h2>
            <p class="mt-3 text-slate-600">Enam layanan inti dalam satu platform yang rapi dan mudah dipakai siapa saja. Klik <span class="font-semibold text-brand-600">Pelajari</span> untuk detailnya.</p>
        </div>
        <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
                // 'tone' = warna badge; 'glow' = sorotan kursor; 'long'+'points' = isi modal detail.
                $features = [
                    ['icon' => 'chart',     'title' => 'Kas Transparan', 'desc' => 'Pemasukan & pengeluaran RT terbuka, lengkap dengan grafik bulanan.',        'tone' => 'bg-brand-50 text-brand-600',   'glow' => 'rgba(16,185,129,0.16)',
                     'long' => 'Semua pemasukan dan pengeluaran kas RT tercatat rapi dan bisa dilihat seluruh warga secara real-time. Tidak ada lagi pertanyaan "uang kas ke mana".',
                     'points' => ['Buku kas masuk & keluar otomatis', 'Grafik saldo per bulan', 'Bisa dilihat semua warga', 'Ekspor laporan kapan saja']],
                    ['icon' => 'receipt',   'title' => 'Iuran Online',    'desc' => 'Bayar iuran lewat Midtrans; status otomatis lunas setelah bayar.',          'tone' => 'bg-emerald-50 text-emerald-600', 'glow' => 'rgba(5,150,105,0.16)',
                     'long' => 'Bayar iuran kapan saja lewat Midtrans — transfer bank, e-wallet, atau QRIS. Status langsung berubah lunas begitu pembayaran masuk.',
                     'points' => ['Bayar via transfer / e-wallet / QRIS', 'Status lunas otomatis', 'Riwayat pembayaran tersimpan', 'Pengingat tagihan']],
                    ['icon' => 'document',  'title' => 'Surat Digital',   'desc' => 'Ajukan surat pengantar, dapat PDF ber-QR yang bisa diverifikasi publik.',   'tone' => 'bg-sky-50 text-sky-600',       'glow' => 'rgba(56,189,248,0.18)',
                     'long' => 'Ajukan surat pengantar tanpa antre. Surat terbit dalam bentuk PDF resmi ber-QR yang keasliannya bisa dicek siapa saja.',
                     'points' => ['Ajukan surat sepenuhnya online', 'PDF resmi ber-QR', 'Verifikasi publik lewat QR', 'Lacak status pengajuan']],
                    ['icon' => 'megaphone', 'title' => 'Pengumuman',      'desc' => 'Info penting tersampaikan, yang mendesak disematkan di atas.',               'tone' => 'bg-amber-50 text-amber-600',   'glow' => 'rgba(245,158,11,0.18)',
                     'long' => 'Semua informasi dari pengurus tersampaikan di satu tempat. Pengumuman mendesak bisa disematkan agar selalu tampil paling atas.',
                     'points' => ['Satu feed pengumuman', 'Sematkan info penting', 'Sampai ke semua warga', 'Arsip pengumuman lama']],
                    ['icon' => 'alert',     'title' => 'Lapor Warga',     'desc' => 'Laporkan masalah lingkungan dengan foto & lacak penanganannya.',             'tone' => 'bg-rose-50 text-rose-600',     'glow' => 'rgba(244,63,94,0.16)',
                     'long' => 'Laporkan masalah lingkungan — jalan rusak, sampah, keamanan — lengkap dengan foto, lalu pantau tindak lanjutnya sampai selesai.',
                     'points' => ['Lapor lengkap dengan foto', 'Lacak status penanganan', 'Riwayat laporan tersimpan', 'Transparan bagi semua warga']],
                    ['icon' => 'adjust',    'title' => 'Mode Inklusif',   'desc' => 'Perbesar teks & tingkatkan kontras — nyaman untuk lansia.',                  'tone' => 'bg-violet-50 text-violet-600', 'glow' => 'rgba(139,92,246,0.18)',
                     'long' => 'Aplikasi nyaman dipakai semua umur. Perbesar ukuran teks dan tingkatkan kontras untuk memudahkan warga lansia.',
                     'points' => ['Perbesar ukuran teks', 'Mode kontras tinggi', 'Navigasi sederhana', 'Ramah untuk lansia']],
                ];
            @endphp
            @foreach ($features as $i => $f)
                {{-- Wrapper luar = entrance reveal; kartu dalam = hover + sorotan kursor. --}}
                <div data-reveal style="transition-delay: {{ $i * 70 }}ms" class="h-full">
                    <div class="spot-card group card-surface relative h-full p-6 rounded-2xl"
                         style="--spot: {{ $f['glow'] }}"
                         @mousemove="const r = $el.getBoundingClientRect();
                                     $el.style.setProperty('--mx', ($event.clientX - r.left) + 'px');
                                     $el.style.setProperty('--my', ($event.clientY - r.top) + 'px')">
                        <div class="spotlight"></div>
                        <span class="relative flex w-14 h-14 items-center justify-center rounded-2xl {{ $f['tone'] }} ring-1 ring-inset ring-black/5 shadow-sm transition-transform duration-200 group-hover:scale-110 group-hover:-rotate-6">
                            <x-ui.icon :name="$f['icon']" class="w-7 h-7" />
                        </span>
                        <h3 class="relative mt-5 text-lg font-bold text-slate-900">{{ $f['title'] }}</h3>
                        <p class="relative mt-1.5 text-sm leading-relaxed text-slate-600">{{ $f['desc'] }}</p>
                        <button type="button" @click="open = true; current = {{ $i }}"
                                class="relative mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 transition-all duration-200 hover:text-brand-700">
                            Pelajari <x-ui.icon name="arrow-right" class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Modal detail fitur (muncul saat "Pelajari" ditekan) --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="open = false" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="open = false"
                 x-show="open"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
            @php
                // Gradient header per fitur (urutan sama dgn $features). Ditulis literal agar terdeteksi Tailwind.
                $grads = [
                    'from-brand-500 to-brand-700',
                    'from-emerald-500 to-emerald-700',
                    'from-sky-500 to-sky-700',
                    'from-amber-500 to-amber-600',
                    'from-rose-500 to-rose-600',
                    'from-violet-500 to-violet-700',
                ];
            @endphp
            <div class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-black/5"
                 x-show="open"
                 x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                @foreach ($features as $i => $f)
                    {{-- Aksen gradient tipis di tepi atas, warna sesuai fitur aktif --}}
                    <div x-show="current === {{ $i }}" x-cloak class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r {{ $grads[$i] }}"></div>
                @endforeach

                <button type="button" @click="open = false" aria-label="Tutup"
                        class="absolute right-4 top-5 z-10 flex w-9 h-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <x-ui.icon name="close" class="w-5 h-5" />
                </button>

                @foreach ($features as $i => $f)
                    <div x-show="current === {{ $i }}" x-cloak>
                        {{-- Badge ikon gradient dengan halo --}}
                        <div class="relative inline-flex">
                            <span aria-hidden="true" class="absolute inset-0 rounded-2xl bg-gradient-to-br {{ $grads[$i] }} opacity-40 blur-lg"></span>
                            <span class="relative flex w-16 h-16 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grads[$i] }} text-white shadow-lg">
                                <x-ui.icon :name="$f['icon']" class="w-8 h-8" />
                            </span>
                        </div>

                        <h3 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-900">{{ $f['title'] }}</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-slate-600">{{ $f['long'] }}</p>

                        <ul class="mt-6 space-y-3">
                            @foreach ($f['points'] as $p)
                                <li class="flex items-center gap-3">
                                    <span class="flex w-6 h-6 shrink-0 items-center justify-center rounded-full {{ $f['tone'] }} ring-1 ring-inset ring-black/5"><x-ui.icon name="check" class="w-3.5 h-3.5" /></span>
                                    <span class="text-sm font-medium text-slate-700">{{ $p }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('register') }}" class="group mt-7 flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-br {{ $grads[$i] }} px-5 py-3.5 font-semibold text-white shadow-lg transition hover:brightness-105">
                            Coba sekarang <x-ui.icon name="arrow-right" class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-0.5" />
                        </a>
                        <button type="button" @click="open = false" class="mt-2.5 w-full rounded-2xl px-5 py-3 text-sm font-semibold text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50 hover:text-slate-700">
                            Tutup
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== Cara kerja ===== --}}
    <section id="cara" class="bg-slate-50 border-y border-slate-100">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-16"
             x-data="{ active: 0, timer: null,
                       start() { this.stop(); this.timer = setInterval(() => this.active = (this.active + 1) % 3, 3500); },
                       stop() { clearInterval(this.timer); this.timer = null; } }"
             x-init="start()">
            <div data-reveal class="text-center max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100 px-3 py-1 text-xs font-semibold">
                    <x-ui.icon name="sparkles" class="w-4 h-4" /> Cara kerja
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">Verifikasi dalam 3 langkah</h2>
                <p class="mt-3 text-slate-600">Arahkan kursor ke tiap langkah — lihat langsung tampilannya di aplikasi.</p>
            </div>

            {{-- Autoplay jalan sendiri; berhenti selagi kursor di area ini, lanjut lagi saat keluar. --}}
            <div class="mt-12 grid lg:grid-cols-2 gap-10 lg:gap-14 items-center"
                 @mouseenter="stop()" @mouseleave="start()">
                {{-- Kiri: daftar langkah interaktif (hover/klik → ganti layar HP) --}}
                <div data-reveal class="order-2 lg:order-1 space-y-3">
                    @php
                        $steps = [
                            ['n' => '1', 'icon' => 'user',     'title' => 'Daftar',          'desc' => 'Buat akun warga cukup dengan email Anda.'],
                            ['n' => '2', 'icon' => 'shield',   'title' => 'Verifikasi',      'desc' => 'Pengurus memverifikasi data Anda sebagai warga sah.'],
                            ['n' => '3', 'icon' => 'sparkles', 'title' => 'Nikmati layanan', 'desc' => 'Bayar iuran, ajukan surat, dan pantau kas RT.'],
                        ];
                    @endphp
                    @foreach ($steps as $i => $s)
                        <button type="button"
                                @mouseenter="active = {{ $i }}" @click="active = {{ $i }}"
                                class="group w-full text-left flex items-start gap-4 rounded-2xl p-4 transition-all duration-200"
                                :class="active === {{ $i }} ? 'bg-white shadow-soft ring-1 ring-brand-100 -translate-y-0.5' : 'ring-1 ring-transparent hover:bg-white/70'">
                            <span class="flex w-11 h-11 shrink-0 items-center justify-center rounded-xl text-base font-extrabold transition-all duration-200"
                                  :class="active === {{ $i }} ? 'bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-card' : 'bg-brand-50 text-brand-600'">{{ $s['n'] }}</span>
                            <div>
                                <h3 class="flex items-center gap-2 font-bold text-slate-900">
                                    <x-ui.icon :name="$s['icon']" class="w-4 h-4 text-brand-600" /> {{ $s['title'] }}
                                </h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $s['desc'] }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>

                {{-- Kanan: mockup HP; layar berganti mengikuti langkah aktif --}}
                <div data-reveal="scale" class="order-1 lg:order-2 flex flex-col items-center gap-6">
                    <div class="phone">
                        <div class="phone-notch"></div>
                        <div class="phone-screen">
                            {{-- Layar 1: Daftar --}}
                            <div class="phone-page" x-show="active === 0"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-x-5"
                                 x-transition:enter-end="opacity-100 translate-x-0">
                                <div class="text-[13px] font-bold text-slate-900">Daftar Warga</div>
                                <div class="mt-0.5 text-[10px] text-slate-400">Buat akun untuk mulai.</div>
                                <div class="mt-4 space-y-2.5">
                                    <div>
                                        <div class="text-[9px] font-medium text-slate-500 mb-1">Email</div>
                                        <div class="h-8 rounded-lg bg-white ring-1 ring-slate-200 flex items-center px-2 text-[10px] text-slate-400">warga@email.com</div>
                                    </div>
                                    <div>
                                        <div class="text-[9px] font-medium text-slate-500 mb-1">Kata sandi</div>
                                        <div class="h-8 rounded-lg bg-white ring-1 ring-slate-200 flex items-center px-2 text-[11px] tracking-widest text-slate-300">••••••••</div>
                                    </div>
                                </div>
                                <div class="mt-4 h-9 rounded-lg bg-brand-600 text-white flex items-center justify-center text-[11px] font-semibold shadow-soft">Daftar</div>
                                <div class="mt-auto text-center text-[9px] text-slate-400">Sudah punya akun? <span class="text-brand-600 font-semibold">Masuk</span></div>
                            </div>

                            {{-- Layar 2: Verifikasi --}}
                            <div class="phone-page" x-show="active === 1" style="display:none"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-x-5"
                                 x-transition:enter-end="opacity-100 translate-x-0">
                                <div class="text-[13px] font-bold text-slate-900">Verifikasi</div>
                                <div class="flex-1 flex flex-col items-center justify-center text-center">
                                    <div class="relative">
                                        <div class="w-16 h-16 rounded-full bg-brand-100 flex items-center justify-center">
                                            <x-ui.icon name="user" class="w-8 h-8 text-brand-600" />
                                        </div>
                                        <span class="absolute -right-1 -bottom-1 w-6 h-6 rounded-full bg-brand-600 text-white flex items-center justify-center ring-2 ring-white">
                                            <x-ui.icon name="check" class="w-4 h-4" />
                                        </span>
                                    </div>
                                    <div class="mt-3 text-[12px] font-bold text-slate-900">Budi Santoso</div>
                                    <div class="text-[9px] text-slate-400">Warga RT 04 / RW 02</div>
                                    <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-brand-50 text-brand-700 px-2.5 py-1 text-[10px] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span> Terverifikasi
                                    </div>
                                </div>
                            </div>

                            {{-- Layar 3: Nikmati layanan --}}
                            <div class="phone-page" x-show="active === 2" style="display:none"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-x-5"
                                 x-transition:enter-end="opacity-100 translate-x-0">
                                <div class="flex items-center justify-between">
                                    <div class="text-[13px] font-bold text-slate-900">Beranda</div>
                                    <span class="w-6 h-6 rounded-full bg-brand-100"></span>
                                </div>
                                <div class="mt-3 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-3">
                                    <div class="text-[9px] text-brand-100">Saldo Kas RT</div>
                                    <div class="text-[15px] font-extrabold tracking-tight">Rp 4.250.000</div>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    <div class="rounded-lg bg-white ring-1 ring-slate-100 p-2 flex flex-col items-center gap-1">
                                        <x-ui.icon name="wallet" class="w-4 h-4 text-brand-600" /><span class="text-[8px] text-slate-500">Iuran</span>
                                    </div>
                                    <div class="rounded-lg bg-white ring-1 ring-slate-100 p-2 flex flex-col items-center gap-1">
                                        <x-ui.icon name="document" class="w-4 h-4 text-sky-600" /><span class="text-[8px] text-slate-500">Surat</span>
                                    </div>
                                    <div class="rounded-lg bg-white ring-1 ring-slate-100 p-2 flex flex-col items-center gap-1">
                                        <x-ui.icon name="megaphone" class="w-4 h-4 text-amber-600" /><span class="text-[8px] text-slate-500">Info</span>
                                    </div>
                                </div>
                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center gap-2 rounded-lg bg-white ring-1 ring-slate-100 p-2">
                                        <span class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center"><x-ui.icon name="check" class="w-3.5 h-3.5" /></span>
                                        <span class="text-[9px] text-slate-600 flex-1">Iuran Agustus</span>
                                        <span class="text-[8px] font-semibold text-emerald-600">Lunas</span>
                                    </div>
                                    <div class="flex items-center gap-2 rounded-lg bg-white ring-1 ring-slate-100 p-2">
                                        <span class="w-6 h-6 rounded-md bg-sky-50 text-sky-600 flex items-center justify-center"><x-ui.icon name="document" class="w-3.5 h-3.5" /></span>
                                        <span class="text-[9px] text-slate-600 flex-1">Surat pengantar</span>
                                        <span class="text-[8px] font-semibold text-sky-600">Terbit</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Dot indikator — klik/hover untuk lompat ke langkah tertentu. --}}
                    <div class="flex items-center gap-2">
                        @for ($i = 0; $i < 3; $i++)
                            <button type="button" aria-label="Langkah {{ $i + 1 }}"
                                    @click="active = {{ $i }}" @mouseenter="active = {{ $i }}"
                                    class="h-2 rounded-full transition-all duration-300"
                                    :class="active === {{ $i }} ? 'w-6 bg-brand-600' : 'w-2 bg-slate-300 hover:bg-slate-400'"></button>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== CTA ===== --}}
    <section class="mx-auto max-w-6xl px-4 sm:px-6 py-16">
        <div data-reveal class="cta-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 text-white ring-1 ring-white/10 px-6 sm:px-10 py-16 text-center"
             @mousemove="const r = $el.getBoundingClientRect();
                         $el.style.setProperty('--mx', ($event.clientX - r.left) + 'px');
                         $el.style.setProperty('--my', ($event.clientY - r.top) + 'px')">
            {{-- Tekstur + kilau + sorotan kursor + dekorasi latar --}}
            <div class="cta-dots"></div>
            <div class="cta-shine"></div>
            <div class="cta-spot"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -left-20 -top-24 w-72 h-72 rounded-full bg-white/10 blur-3xl"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -right-16 -bottom-24 w-80 h-80 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div aria-hidden="true" class="absolute -right-10 -top-10 opacity-10"><x-ui.icon name="home" class="w-64 h-64" /></div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 ring-1 ring-white/25 px-3 py-1 text-xs font-semibold text-white">
                    <x-ui.icon name="sparkles" class="w-4 h-4" /> Gratis untuk RT/RW
                </span>
                <h2 class="mt-5 text-3xl sm:text-4xl font-extrabold text-white leading-tight">Siap membawa RT Anda ke era digital?</h2>
                <p class="mt-3 text-brand-100 max-w-xl mx-auto">Gabung sekarang dan rasakan pengelolaan warga yang transparan, cepat, dan inklusif.</p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 font-bold text-brand-700 shadow-lg shadow-brand-950/25 transition hover:-translate-y-0.5 hover:shadow-xl">
                        Daftar Gratis <x-ui.icon name="arrow-right" class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-0.5" />
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-6 py-3.5 font-semibold text-white ring-1 ring-white/30 backdrop-blur-sm transition hover:bg-white/20 hover:ring-white/50">
                        Masuk
                    </a>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-brand-100/90">
                    @foreach (['Tanpa biaya', 'Tanpa kartu kredit', 'Siap dalam 5 menit'] as $t)
                        <span class="inline-flex items-center gap-1.5"><x-ui.icon name="check" class="w-4 h-4 text-emerald-300" /> {{ $t }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Footer ===== --}}
    <footer class="border-t border-slate-100">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <x-app-brand-mark class="w-8 h-8" />
                <span class="font-display font-bold text-slate-900 tracking-tight">SAPA</span>
            </div>
            <p class="text-sm text-slate-400">&copy; {{ date('Y') }} SAPA · Sistem Administrasi dan Pelayanan Antarwarga.</p>
        </div>
    </footer>
</body>
</html>
