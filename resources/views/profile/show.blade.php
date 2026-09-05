<x-app-layout>
    {{-- Header profil (ala kartu identitas warga) --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-6 sm:p-8 mb-6">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-5">
            <span class="w-20 h-20 rounded-2xl bg-white/15 ring-2 ring-white/30 flex items-center justify-center text-3xl font-extrabold shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-extrabold text-white">{{ $user->name }}</h1>
                    <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-semibold">
                        <x-ui.icon name="star" class="w-3.5 h-3.5 text-amber-300" /> Level {{ $user->level() }}
                    </span>
                    @if ($rank !== false)
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-semibold">
                            <x-ui.icon name="trophy" class="w-3.5 h-3.5" /> Peringkat #{{ $rank + 1 }}
                        </span>
                    @endif
                </div>
                <p class="text-brand-100 text-sm mt-1">
                    @if ($resident?->family?->rt)
                        RT {{ $resident->family->rt->number }} / RW {{ $resident->family->rt->rw_number }} · {{ $resident->family->rt->village }}
                    @else
                        Warga terverifikasi
                    @endif
                </p>
                <div class="mt-4 max-w-md">
                    <x-ui.xp-bar :user="$user" :light="true" />
                </div>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-10 opacity-10"><x-ui.icon name="trophy" class="w-52 h-52" /></div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <x-ui.stat label="Total XP" :value="number_format($user->xp, 0, ',', '.')" icon="star" tone="amber" />
        <x-ui.stat label="Badge Diraih" :value="$stats['badges'] . ' / ' . count($allBadges)" icon="trophy" tone="brand" />
        <x-ui.stat label="Iuran Lunas" :value="$stats['dues_paid']" icon="receipt" tone="emerald" />
        <x-ui.stat label="Laporan" :value="$stats['complaints']" icon="alert" tone="slate" />
    </div>

    {{-- Badge --}}
    <x-ui.section-header title="Koleksi Badge" subtitle="Raih badge dengan aktif di lingkungan" icon="trophy" />
    <x-ui.card class="mb-8">
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-8 gap-4">
            @foreach ($allBadges as $badge)
                <x-ui.badge-medal :badge="$badge" :owned="$user->hasBadge($badge)" />
            @endforeach
        </div>
    </x-ui.card>

    {{-- Riwayat kontribusi --}}
    <x-ui.section-header title="Riwayat Kontribusi" icon="clock" />
    <x-ui.card padding="p-0">
        @forelse ($xpLogs as $log)
            <div class="flex items-center gap-4 px-5 py-3 {{ ! $loop->last ? 'border-b border-slate-50' : '' }}">
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <x-ui.icon name="star" class="w-5 h-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $log->reason }}</p>
                    <p class="text-xs text-slate-400">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
                <span class="text-sm font-bold text-brand-600 shrink-0">+{{ $log->points }} XP</span>
            </div>
        @empty
            <x-ui.empty-state icon="star" title="Belum ada aktivitas"
                message="Bayar iuran, ajukan surat, atau buat laporan untuk mengumpulkan XP." />
        @endforelse
    </x-ui.card>
</x-app-layout>
