/**
 * Service worker SAPA — bikin aplikasi warga installable & tetap responsif
 * saat koneksi buruk. Strategi:
 *   - Navigasi (halaman): network-first → fallback halaman offline.
 *   - Aset build (hashed, immutable): cache-first (stale-while-revalidate).
 *   - Hanya GET same-origin; /admin, webhook, & API tidak disentuh.
 */
const CACHE = 'sapa-v1';
const OFFLINE_URL = '/offline.html';
const PRECACHE = [OFFLINE_URL, '/favicon.svg', '/icon-192.png', '/icon-512.png', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Hanya tangani GET same-origin.
    if (request.method !== 'GET') return;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Jangan ganggu panel admin, pembayaran, & callback.
    if (url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/midtrans') ||
        url.pathname.startsWith('/livewire')) {
        return;
    }

    // Navigasi halaman → network-first, fallback offline.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Aset build & statis → cache-first + revalidate di belakang layar.
    if (url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/favicon') ||
        /\.(?:png|svg|jpg|jpeg|webp|woff2?)$/.test(url.pathname)) {
        event.respondWith(
            caches.open(CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                const network = fetch(request)
                    .then((res) => {
                        if (res.ok) cache.put(request, res.clone());
                        return res;
                    })
                    .catch(() => cached);
                return cached || network;
            })
        );
    }
});
