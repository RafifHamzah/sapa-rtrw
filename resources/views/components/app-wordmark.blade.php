{{--
    Wordmark "Sapa" (gambar tipografi warna). Dipakai berdampingan dengan
    <x-app-brand-mark /> di header. Untuk latar gelap, pakai versi putih:
    images/sapa-wordmark-white.png (lihat navbar hero di welcome.blade.php).
--}}
<img src="{{ asset('images/sapa-wordmark.png') }}" alt="SAPA"
     {{ $attributes->merge(['class' => 'h-7 w-auto']) }}>
