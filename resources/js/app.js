import Alpine from 'alpinejs';
import confetti from 'canvas-confetti';

// Progressive enhancement: tandai bahwa JS aktif. State "tersembunyi" untuk scroll-reveal
// di CSS di-gate ke `html.js`, jadi tanpa JS konten tetap tampil final.
document.documentElement.classList.add('js');

const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Ledakan confetti warna brand (mis. saat pembayaran berhasil).
 * Menghormati prefers-reduced-motion demi perangkat low-end/aksesibilitas.
 */
window.confettiBurst = () => {
    if (prefersReducedMotion()) return;

    const colors = ['#10b981', '#16a34a', '#059669', '#6ee7b7', '#d1fae5'];
    const end = Date.now() + 900;

    (function frame() {
        confetti({ particleCount: 4, angle: 60, spread: 60, origin: { x: 0 }, colors });
        confetti({ particleCount: 4, angle: 120, spread: 60, origin: { x: 1 }, colors });
        if (Date.now() < end) requestAnimationFrame(frame);
    })();

    confetti({ particleCount: 90, spread: 80, startVelocity: 45, origin: { y: 0.6 }, colors });
};

// Alpine store: antrean toast global — window.toast(pesan, tipe).
document.addEventListener('alpine:init', () => {
    Alpine.store('toasts', {
        items: [],
        push(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type });
            setTimeout(() => this.remove(id), 4500);
        },
        remove(id) {
            this.items = this.items.filter((t) => t.id !== id);
        },
    });

    // Engine kuis reusable (dipakai game "Kuis Administrasi", "Tebak Jenis Surat", dll).
    Alpine.data('quizGame', (bank, completeUrl, perRound = 7) => ({
        bank,
        completeUrl,
        queue: [], index: 0, correct: 0,
        selected: null, answered: false, done: false, xpMessage: '',
        shuffle(arr) { return [...arr].sort(() => Math.random() - 0.5); },
        init() { this.start(); },
        start() {
            this.queue = this.shuffle(this.bank).slice(0, perRound).map((q) => ({
                ...q, options: this.shuffle(q.options),
            }));
            this.index = 0; this.correct = 0;
            this.selected = null; this.answered = false; this.done = false; this.xpMessage = '';
        },
        get current() { return this.queue[this.index] ?? { q: '', options: [], answer: '' }; },
        get passMark() { return Math.ceil(this.queue.length * 0.6); },
        choose(opt) {
            if (this.answered) return;
            this.selected = opt;
            this.answered = true;
            if (opt === this.current.answer) this.correct++;
            setTimeout(() => this.next(), 1100);
        },
        next() {
            this.index++;
            this.selected = null;
            this.answered = false;
            if (this.index >= this.queue.length) this.finish();
        },
        async finish() {
            this.done = true;
            if (this.correct >= this.passMark && window.confettiBurst) window.confettiBurst();
            try {
                const res = await fetch(this.completeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ correct: this.correct, total: this.queue.length }),
                });
                const d = await res.json();
                this.xpMessage = d.message;
                if (d.awarded && window.toast) window.toast(d.message, 'success');
            } catch (e) { /* offline */ }
        },
    }));
});

window.toast = (message, type = 'success') => {
    if (window.Alpine?.store('toasts')) {
        window.Alpine.store('toasts').push(message, type);
    }
};

// Scroll reveal: elemen [data-reveal] muncul halus saat masuk viewport.
const initScrollReveal = () => {
    const els = document.querySelectorAll('[data-reveal]');
    if (!els.length) return;

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 },
    );

    els.forEach((el) => observer.observe(el));
};

// Sembunyikan loading screen setelah halaman siap.
const hideLoader = () => {
    const loader = document.getElementById('app-loader');
    if (loader) {
        loader.classList.add('loader-hidden');
        setTimeout(() => loader.remove(), 600);
    }
};

window.addEventListener('load', () => {
    initScrollReveal();
    setTimeout(hideLoader, 350);
});

window.Alpine = Alpine;
Alpine.start();
