<x-app-layout>
    {{-- Hero greeting --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-6 sm:p-8 mb-6">
        <div class="relative z-10">
            <p class="text-brand-100 text-sm">Selamat datang,</p>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-0.5">{{ $user->name }} 👋</h1>
            <p class="text-brand-100 text-sm mt-2">
                @if ($rt)
                    RT {{ $rt->number }} / RW {{ $rt->rw_number }} — {{ $rt->village }}
                @else
                    Selamat datang di layanan digital RT/RW Anda.
                @endif
            </p>
        </div>
        <div class="absolute -right-8 -bottom-10 opacity-20">
            <x-ui.icon name="home" class="w-48 h-48" />
        </div>
    </div>

    @unless ($resident)
        <div class="mb-6 flex items-start gap-3 rounded-2xl bg-amber-50 ring-1 ring-amber-200 px-4 py-3 text-sm text-amber-800">
            <x-ui.icon name="alert" class="w-5 h-5 shrink-0 text-amber-500" />
            <span>Akun Anda belum tertaut ke data warga. Beberapa fitur (iuran &amp; surat) akan aktif setelah pengurus menautkan data Anda.</span>
        </div>
    @endunless

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <x-ui.stat label="Saldo Kas RT" :value="rupiah($balance)" icon="wallet" tone="brand" sub="Transparan untuk semua warga" />
        <x-ui.stat label="Tagihan Belum Lunas" :value="$arrearsCount . ' tagihan'" icon="receipt"
                   :tone="$arrearsCount > 0 ? 'amber' : 'emerald'" :sub="rupiah($arrearsTotal)" />
        <x-ui.stat label="Total Pemasukan Kas" :value="rupiah($income)" icon="trend-up" tone="emerald" />
    </div>

    {{-- Prestasi & peringkat --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
        <x-ui.card class="lg:col-span-1 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-900">Prestasi Saya</h3>
                <a href="{{ route('profile.show') }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">Detail</a>
            </div>
            <x-ui.xp-bar :user="$user" />
            <div class="flex items-center gap-1.5 mt-4">
                @php $owned = $user->badges->take(4); @endphp
                @forelse ($owned as $ub)
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center" title="{{ $ub->badge->getLabel() }}">
                        <x-ui.icon :name="$ub->badge->icon()" class="w-4 h-4" />
                    </span>
                @empty
                    <span class="text-xs text-slate-400">Belum ada badge — mulai beraktivitas!</span>
                @endforelse
                @if ($user->badges->count() > 4)
                    <span class="text-xs text-slate-400 ml-1">+{{ $user->badges->count() - 4 }}</span>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card class="lg:col-span-2">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-900 flex items-center gap-2"><x-ui.icon name="trophy" class="w-5 h-5 text-amber-500" /> Warga Teraktif</h3>
                <a href="{{ route('leaderboard') }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">Papan peringkat</a>
            </div>
            <div class="space-y-1.5">
                @forelse ($leaders->take(3) as $i => $leader)
                    <div class="flex items-center gap-3 py-1.5">
                        <span @class([
                            'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0',
                            'bg-amber-400' => $i === 0, 'bg-slate-300' => $i === 1, 'bg-amber-700' => $i === 2,
                        ])>{{ $i + 1 }}</span>
                        <span class="text-sm font-medium text-slate-700 flex-1 truncate">{{ $leader->name }}</span>
                        <span class="text-xs text-slate-400">Lv {{ $leader->level() }}</span>
                        <span class="text-sm font-bold text-brand-600">{{ number_format($leader->xp, 0, ',', '.') }} XP</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 py-2">Belum ada data peringkat.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    {{-- Aksi cepat --}}
    <x-ui.section-header title="Layanan Cepat" icon="sparkles" />
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
        @php
            $quick = [
                ['route' => 'kas.index', 'label' => 'Kas Transparan', 'icon' => 'chart', 'tone' => 'bg-brand-50 text-brand-600'],
                ['route' => 'iuran.index', 'label' => 'Bayar Iuran', 'icon' => 'receipt', 'tone' => 'bg-amber-50 text-amber-600'],
                ['route' => 'letters.index', 'label' => 'Ajukan Surat', 'icon' => 'document', 'tone' => 'bg-sky-50 text-sky-600'],
                ['route' => 'complaints.index', 'label' => 'Lapor Warga', 'icon' => 'alert', 'tone' => 'bg-red-50 text-red-600'],
            ];
        @endphp
        @foreach ($quick as $q)
            <a href="{{ route($q['route']) }}" class="card-surface p-4 flex flex-col gap-3 hover:shadow-soft hover:-translate-y-0.5 transition-all">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $q['tone'] }}">
                    <x-ui.icon :name="$q['icon']" class="w-5 h-5" />
                </span>
                <span class="text-sm font-semibold text-slate-700">{{ $q['label'] }}</span>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Pengumuman terbaru --}}
        <div class="lg:col-span-2">
            <x-ui.section-header title="Pengumuman Terbaru" icon="megaphone">
                <x-slot name="action">
                    <a href="{{ route('announcements.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 inline-flex items-center gap-1">
                        Semua <x-ui.icon name="chevron-right" class="w-4 h-4" />
                    </a>
                </x-slot>
            </x-ui.section-header>

            <div class="space-y-3">
                @forelse ($announcements as $a)
                    <a href="{{ route('announcements.show', $a) }}" class="card-surface p-4 block hover:shadow-soft transition-all">
                        <div class="flex items-center gap-2 mb-1">
                            @if ($a->is_pinned)<x-ui.badge color="warning">📌 Disematkan</x-ui.badge>@endif
                            <x-ui.badge :color="$a->category->getColor()">{{ $a->category->getLabel() }}</x-ui.badge>
                            <span class="ml-auto text-xs text-slate-400">{{ $a->published_at->translatedFormat('d M') }}</span>
                        </div>
                        <h3 class="font-semibold text-slate-800">{{ $a->title }}</h3>
                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ \Illuminate\Support\Str::of($a->content)->stripTags()->limit(120) }}</p>
                    </a>
                @empty
                    <x-ui.card><x-ui.empty-state icon="megaphone" title="Belum ada pengumuman" message="Pengumuman dari pengurus akan tampil di sini." /></x-ui.card>
                @endforelse
            </div>
        </div>

        {{-- Surat & laporan saya --}}
        <div class="space-y-6">
            <div>
                <x-ui.section-header title="Surat Saya" icon="document" />
                <x-ui.card padding="p-4">
                    @forelse ($letters as $letter)
                        <div class="flex items-center justify-between py-2 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">{{ $letter->letterType->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $letter->purpose }}</p>
                            </div>
                            <x-status-badge :status="$letter->status" />
                        </div>
                    @empty
                        <x-ui.empty-state icon="document" title="Belum ada permohonan" />
                    @endforelse
                </x-ui.card>
            </div>

            <div>
                <x-ui.section-header title="Laporan Saya" icon="alert" />
                <x-ui.card padding="p-4">
                    @forelse ($complaints as $c)
                        <div class="flex items-center justify-between py-2 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">{{ $c->title }}</p>
                                <p class="text-xs text-slate-400">{{ $c->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                            <x-status-badge :status="$c->status" />
                        </div>
                    @empty
                        <x-ui.empty-state icon="alert" title="Belum ada laporan" />
                    @endforelse
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
