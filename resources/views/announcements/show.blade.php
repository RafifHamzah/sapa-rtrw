<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('announcements.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-4">
            <x-ui.icon name="arrow-left" class="w-4 h-4" /> Kembali ke pengumuman
        </a>

        <x-ui.card padding="p-6 sm:p-8">
            <div class="flex items-center gap-2 mb-3 flex-wrap">
                @if ($announcement->is_pinned)<x-ui.badge color="warning">📌 Disematkan</x-ui.badge>@endif
                <x-ui.badge :color="$announcement->category->getColor()">{{ $announcement->category->getLabel() }}</x-ui.badge>
            </div>

            <h1 class="text-2xl font-extrabold text-slate-900">{{ $announcement->title }}</h1>
            <div class="flex items-center gap-2 mt-2 text-sm text-slate-400">
                <x-ui.icon name="user" class="w-4 h-4" /> {{ $announcement->author?->name ?? 'Pengurus' }}
                <span>·</span>
                <x-ui.icon name="clock" class="w-4 h-4" /> {{ $announcement->published_at->translatedFormat('d F Y, H:i') }}
            </div>

            <div class="prose prose-slate prose-sm sm:prose-base max-w-none mt-6">{!! $announcement->content !!}</div>

            @if ($announcement->attachment_path)
                <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank"
                   class="inline-flex items-center gap-2 mt-6 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
                    <x-ui.icon name="download" class="w-5 h-5" /> Lihat Lampiran
                </a>
            @endif

            <div class="mt-8 pt-5 border-t border-slate-100">
                @php
                    $shareText = urlencode('[Pengumuman RT] ' . $announcement->title . ' — ' . route('announcements.show', $announcement));
                @endphp
                <a href="https://wa.me/?text={{ $shareText }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#25D366] px-4 py-2.5 text-sm font-semibold text-white hover:brightness-95">
                    <x-ui.icon name="share" class="w-5 h-5" /> Bagikan ke WhatsApp
                </a>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
