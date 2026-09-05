{{--
    Meta sosial (Open Graph + Twitter Card) untuk share preview.
    Override per halaman dgn @include('partials.meta', ['metaTitle' => '...', ...]).
    Gambar: public/og-image.png (1200×630). URL absolut mengikuti APP_URL,
    jadi pastikan APP_URL di .env produksi = domain asli (bukan localhost).
--}}
@php
    $metaTitle = $metaTitle ?? 'SAPA — Semua Urusan Warga, dalam Satu Sapa';
    $metaDescription = $metaDescription ?? 'SAPA (Sistem Administrasi dan Pelayanan Antarwarga): kas transparan, iuran online, surat pengantar ber-QR, pengumuman, dan lapor warga — dalam satu aplikasi.';
    $metaImage = $metaImage ?? url('/og-image.png');
    $metaUrl = $metaUrl ?? url()->current();
@endphp
<meta name="description" content="{{ $metaDescription }}">
{{-- theme-color sudah disediakan partials/favicons --}}

{{-- Open Graph (Facebook, WhatsApp, LinkedIn, dll.) --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="SAPA">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:secure_url" content="{{ $metaImage }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="SAPA — RT/RW digital untuk semua">

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
