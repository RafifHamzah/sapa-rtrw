{{-- Favicon SAPA (rumah + tiga warga). SVG untuk browser modern, ICO/PNG fallback. --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="alternate icon" href="{{ asset('favicon-32.png') }}" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

{{-- PWA: installable + tampilan standalone di HP. --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#10b981">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="SAPA">

{{-- Daftarkan service worker + tangkap prompt install (window.sapaInstall()). --}}
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
    window.__deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        window.__deferredPrompt = e;
        document.documentElement.classList.add('pwa-installable');
    });
    window.addEventListener('appinstalled', () => {
        window.__deferredPrompt = null;
        document.documentElement.classList.remove('pwa-installable');
    });
    window.sapaInstall = async () => {
        const p = window.__deferredPrompt;
        if (!p) return;
        p.prompt();
        await p.userChoice;
        window.__deferredPrompt = null;
        document.documentElement.classList.remove('pwa-installable');
    };
</script>
