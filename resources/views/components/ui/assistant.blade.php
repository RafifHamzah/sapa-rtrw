{{-- SAPA AI — asisten FAQ mengambang. --}}
<div class="fixed left-4 bottom-24 md:bottom-6 z-40 flex flex-col items-start gap-3" x-data="assistantChat()" @keydown.escape.window="open = false">

    {{-- Panel chat --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="glass rounded-3xl shadow-card ring-1 ring-slate-200 flex flex-col overflow-hidden
                w-[min(23rem,calc(100vw-2rem))] h-[min(30rem,72vh)]">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white">
            <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                <x-ui.icon name="sparkles" class="w-5 h-5" />
            </span>
            <div class="flex-1 min-w-0">
                <p class="font-bold leading-tight">SAPA AI</p>
                <p class="text-[11px] text-brand-100 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span> Asisten warga
                </p>
            </div>
            <button @click="open = false" class="text-white/80 hover:text-white" aria-label="Tutup">
                <x-ui.icon name="close" class="w-5 h-5" />
            </button>
        </div>

        {{-- Pesan --}}
        <div x-ref="scroll" class="flex-1 overflow-y-auto px-3 py-4 space-y-3 bg-white/40">
            <template x-for="(m, i) in messages" :key="i">
                <div>
                    {{-- Bot --}}
                    <template x-if="m.role === 'bot'">
                        <div class="flex items-start gap-2">
                            <span class="shrink-0 w-7 h-7 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center">
                                <x-ui.icon name="sparkles" class="w-4 h-4" />
                            </span>
                            <div class="max-w-[85%]">
                                <div class="rounded-2xl rounded-tl-sm bg-white ring-1 ring-slate-100 px-3.5 py-2.5 text-sm text-slate-700 leading-relaxed" x-html="m.html"></div>
                                <template x-if="m.action">
                                    <a :href="m.action.url" class="inline-flex items-center gap-1.5 mt-2 rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
                                        <span x-text="m.action.label"></span>
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                    {{-- Warga --}}
                    <template x-if="m.role === 'user'">
                        <div class="flex justify-end">
                            <div class="max-w-[85%] rounded-2xl rounded-tr-sm bg-brand-600 text-white px-3.5 py-2.5 text-sm" x-html="m.html"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Indikator mengetik --}}
            <div x-show="loading" class="flex items-center gap-2">
                <span class="shrink-0 w-7 h-7 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center">
                    <x-ui.icon name="sparkles" class="w-4 h-4" />
                </span>
                <div class="rounded-2xl rounded-tl-sm bg-white ring-1 ring-slate-100 px-4 py-3 flex gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 animate-bounce" style="animation-delay:300ms"></span>
                </div>
            </div>
        </div>

        {{-- Saran --}}
        <div x-show="suggestions.length" class="px-3 pt-2 flex flex-wrap gap-1.5 bg-white/40">
            <template x-for="s in suggestions" :key="s">
                <button @click="send(s)" class="rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100 px-3 py-1 text-xs font-medium hover:bg-brand-100" x-text="s"></button>
            </template>
        </div>

        {{-- Input --}}
        <form @submit.prevent="send()" class="flex items-center gap-2 p-3 bg-white/60 border-t border-slate-100">
            <input x-model="input" type="text" placeholder="Tanya SAPA AI…"
                   class="flex-1 rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 bg-white" />
            <button type="submit" :disabled="loading || !input.trim()"
                    class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center hover:bg-brand-700 disabled:opacity-50 shrink-0">
                <x-ui.icon name="send" class="w-5 h-5" />
            </button>
        </form>
    </div>

    {{-- Tombol mengambang --}}
    <button @click="toggle()" :aria-expanded="open" aria-label="Buka SAPA AI"
            class="inline-flex items-center gap-2 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-card pl-3.5 pr-4 py-3 hover:shadow-soft transition-all">
        <x-ui.icon name="chat" class="w-6 h-6" />
        <span class="text-sm font-semibold">SAPA AI</span>
    </button>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('assistantChat', () => ({
                open: false,
                input: '',
                loading: false,
                messages: [],
                suggestions: [],
                toggle() {
                    this.open = !this.open;
                    if (this.open && this.messages.length === 0) this.welcome();
                    this.scrollSoon();
                },
                welcome() {
                    this.messages.push({ role: 'bot', html: this.format('Halo! 👋 Saya **SAPA AI**, asisten warga Anda. Mau tanya apa hari ini?') });
                    this.suggestions = ['Cara bayar iuran', 'Iuran saya berapa?', 'Cara ajukan surat', 'Saldo kas RT'];
                },
                async send(text) {
                    const msg = (text ?? this.input).trim();
                    if (!msg || this.loading) return;
                    this.input = '';
                    this.suggestions = [];
                    this.messages.push({ role: 'user', html: this.escape(msg) });
                    this.loading = true;
                    this.scrollSoon();
                    try {
                        const res = await fetch(@js(route('assistant.ask')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({ message: msg }),
                        });
                        const data = await res.json();
                        this.messages.push({ role: 'bot', html: this.format(data.reply), action: data.action });
                        this.suggestions = data.suggestions || [];
                    } catch (e) {
                        this.messages.push({ role: 'bot', html: 'Maaf, koneksi bermasalah. Coba lagi ya. 🙏' });
                    } finally {
                        this.loading = false;
                        this.scrollSoon();
                    }
                },
                escape(s) {
                    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                },
                format(s) {
                    return this.escape(s).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                },
                scrollSoon() {
                    this.$nextTick(() => { if (this.$refs.scroll) this.$refs.scroll.scrollTop = this.$refs.scroll.scrollHeight; });
                },
            }));
        });
    </script>
</div>
