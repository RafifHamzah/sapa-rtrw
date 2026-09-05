<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.favicons')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'RT/RW Digital') }}</title>
    @include('partials.meta')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:500,600,700,800|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        {{-- Branding panel (desktop) — komposisi 3D interaktif mengikuti kursor --}}
        <div class="brand-panel relative hidden lg:flex flex-col justify-between overflow-hidden text-white p-12"
             x-data="{ rx: 0, ry: 0, mx: 0.7, my: 0.3 }"
             @mousemove="const r = $el.getBoundingClientRect();
                         mx = ($event.clientX - r.left) / r.width;
                         my = ($event.clientY - r.top) / r.height;
                         ry = (mx - 0.5) * 14; rx = -(my - 0.5) * 14;"
             @mouseleave="rx = 0; ry = 0; mx = 0.7; my = 0.3;">

            {{-- Sorotan cahaya mengikuti kursor + blob ambient. --}}
            <div class="brand-spotlight" :style="`--mx:${mx * 100}%; --my:${my * 100}%`"></div>
            <div class="brand-blob brand-blob-1" aria-hidden="true"></div>
            <div class="brand-blob brand-blob-2" aria-hidden="true"></div>

            {{-- Header brand --}}
            <a href="{{ url('/') }}" class="relative z-10 flex items-center gap-3">
                <x-app-brand-mark class="w-11 h-11" />
                {{-- Wordmark versi PUTIH agar kebaca di atas panel hijau gelap. --}}
                <img src="{{ asset('images/sapa-wordmark-white.png') }}" alt="SAPA" class="h-8 w-auto">
            </a>

            {{-- Panggung 3D: tumpukan kartu kaca pada kedalaman berbeda,
                 seluruh grup miring mengikuti kursor → parallax nyata. --}}
            <div class="relative z-10 flex-1 flex items-center justify-center py-6" style="perspective: 1500px;">
                <div class="brand-3d"
                     :class="{ 'brand-3d-float': rx === 0 && ry === 0 }"
                     :style="`transform: rotateX(${rx}deg) rotateY(${ry}deg)`"
                     aria-hidden="true">

                    {{-- Kartu utama: ringkasan kas (angka ilustratif, bukan data asli) --}}
                    <div class="glass-card card-main">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-brand-100/90">Saldo Kas RT</span>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>Real-time
                            </span>
                        </div>
                        <div class="mt-1.5 text-2xl font-extrabold tracking-tight text-white">Rp 4.250.000</div>
                        <div class="mt-3 flex items-end gap-1.5 h-10" aria-hidden="true">
                            @foreach ([40, 55, 48, 70, 62, 85, 78] as $h)
                                <span class="spark-bar" style="height: {{ $h }}%"></span>
                            @endforeach
                        </div>
                        <div class="mt-2.5 flex items-center gap-1.5 text-xs font-medium text-emerald-200">
                            <x-ui.icon name="trend-up" class="w-4 h-4" /> +12% bulan ini
                        </div>
                    </div>

                    {{-- Chip iuran lunas (lapisan paling depan) --}}
                    <div class="glass-card card-iuran flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-emerald-400/25 flex items-center justify-center">
                            <x-ui.icon name="check" class="w-4.5 h-4.5 text-emerald-200" />
                        </span>
                        <div class="leading-tight">
                            <div class="text-[11px] text-brand-100/80">Iuran Agustus</div>
                            <div class="text-xs font-bold text-white">Lunas</div>
                        </div>
                    </div>

                    {{-- Kartu surat ber-QR (agak di belakang) --}}
                    <div class="glass-card card-surat flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-white/10 border border-white/15 flex items-center justify-center">
                            <x-ui.icon name="shield" class="w-5 h-5 text-brand-100" />
                        </span>
                        <div class="leading-tight">
                            <div class="text-xs font-bold text-white">Surat ber-QR</div>
                            <div class="text-[11px] text-emerald-200">Terverifikasi resmi</div>
                        </div>
                    </div>

                    <div class="deco-ring" aria-hidden="true"></div>
                </div>
            </div>

            {{-- Headline + fitur --}}
            <div class="relative z-10 max-w-md">
                <h2 class="text-3xl font-extrabold leading-tight text-white">Semua urusan warga, dalam satu sapa.</h2>
                <p class="mt-3 text-brand-100/90">Kas transparan, iuran online, surat pengantar digital, pengumuman, dan lapor warga — semua dalam genggaman.</p>
                <div class="mt-6 space-y-2.5">
                    @foreach (['Keuangan RT terbuka & real-time', 'Bayar iuran tanpa antre', 'Surat pengantar ber-QR resmi'] as $point)
                        <div class="flex items-center gap-3 text-sm text-brand-50">
                            <span class="w-6 h-6 rounded-full bg-white/15 flex items-center justify-center shrink-0"><x-ui.icon name="check" class="w-4 h-4" /></span>
                            {{ $point }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Footer --}}
            <p class="relative z-10 text-xs text-brand-200/80 mt-8">SAPA &copy; {{ date('Y') }} · Sistem Administrasi dan Pelayanan Antarwarga</p>
        </div>

        {{-- Form panel --}}
        <div class="login-stage relative flex flex-col justify-center items-center px-5 py-10 sm:px-10 min-h-screen lg:min-h-0 overflow-hidden">
            <div class="relative w-full max-w-md">
                <a href="{{ url('/') }}" class="lg:hidden flex items-center justify-center gap-2.5 mb-8">
                    <x-app-brand-mark class="w-10 h-10" />
                    {{-- Wordmark warna penuh untuk header ringkas di mobile. --}}
                    <img src="{{ asset('images/sapa-wordmark.png') }}" alt="SAPA" class="h-8 w-auto">
                </a>

                {{-- Tumpukan 3D: kartu login + 2 kartu kaca di belakang pada kedalaman
                     berbeda. Seluruh grup miring halus mengikuti kursor → lapisan parallax. --}}
                <div class="login-3d"
                     x-data="{ rx: 0, ry: 0 }"
                     @mousemove="const r = $el.getBoundingClientRect();
                                 ry = ((($event.clientX - r.left) / r.width) - 0.5) * 8;
                                 rx = -((($event.clientY - r.top) / r.height) - 0.5) * 8;"
                     @mouseleave="rx = 0; ry = 0"
                     :style="`transform: rotateX(${rx}deg) rotateY(${ry}deg)`">
                    <div class="stack-layer stack-back" aria-hidden="true"></div>
                    <div class="stack-layer stack-mid" aria-hidden="true"></div>
                    <div class="card-surface login-card-3d p-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
