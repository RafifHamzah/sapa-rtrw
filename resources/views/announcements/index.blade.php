<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center"><x-ui.icon name="megaphone" class="w-6 h-6" /></span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Pengumuman</h1>
                <p class="text-sm text-slate-500">Kabar &amp; informasi terbaru dari pengurus RT.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        @forelse ($announcements as $announcement)
            <a href="{{ route('announcements.show', $announcement) }}"
               class="card-surface p-5 sm:p-6 block hover:shadow-soft transition-all {{ $announcement->is_pinned ? 'ring-2 ring-accent-200' : '' }}">
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    @if ($announcement->is_pinned)
                        <x-ui.badge color="warning">📌 Disematkan</x-ui.badge>
                    @endif
                    <x-ui.badge :color="$announcement->category->getColor()">{{ $announcement->category->getLabel() }}</x-ui.badge>
                    <span class="ml-auto text-xs text-slate-400">{{ $announcement->published_at->translatedFormat('d M Y') }}</span>
                </div>
                <h2 class="font-bold text-lg text-slate-900">{{ $announcement->title }}</h2>
                <p class="text-sm text-slate-500 mt-1.5 line-clamp-2">{{ \Illuminate\Support\Str::of($announcement->content)->stripTags()->limit(160) }}</p>
                <div class="flex items-center gap-2 mt-3 text-xs text-slate-400">
                    <x-ui.icon name="user" class="w-4 h-4" /> {{ $announcement->author?->name ?? 'Pengurus' }}
                    <span class="ml-auto inline-flex items-center gap-1 text-brand-600 font-medium">Baca <x-ui.icon name="chevron-right" class="w-4 h-4" /></span>
                </div>
            </a>
        @empty
            <x-ui.card><x-ui.empty-state icon="megaphone" title="Belum ada pengumuman"
                message="Belum ada informasi yang dipublikasikan pengurus." /></x-ui.card>
        @endforelse
    </div>
</x-app-layout>
