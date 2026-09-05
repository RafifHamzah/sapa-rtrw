<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6">
        <a href="{{ route('complaints.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700">
            <x-ui.icon name="arrow-left" class="w-4 h-4" /> Kembali ke daftar laporan
        </a>

        <x-ui.card padding="p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $complaint->title }}</h1>
                    <p class="text-sm text-slate-400 mt-1">{{ $complaint->category->getLabel() }} · {{ $complaint->created_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
                <x-status-badge :status="$complaint->status" />
            </div>

            <p class="text-slate-700 mt-4">{{ $complaint->description }}</p>

            @if ($complaint->location)
                <p class="inline-flex items-center gap-1.5 text-sm text-slate-500 mt-3"><x-ui.icon name="map-pin" class="w-4 h-4" /> {{ $complaint->location }}</p>
            @endif

            @if ($complaint->photo_path)
                <img src="{{ Storage::url($complaint->photo_path) }}" alt="Foto laporan" class="mt-4 max-h-72 rounded-2xl ring-1 ring-slate-100">
            @endif

            @if ($complaint->response)
                <div class="mt-4 rounded-2xl bg-brand-50 ring-1 ring-brand-100 p-4">
                    <p class="text-xs font-semibold text-brand-700 uppercase tracking-wide">Tanggapan Pengurus</p>
                    <p class="text-sm text-brand-900 mt-1">{{ $complaint->response }}</p>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card padding="p-6">
            <x-ui.section-header title="Riwayat Penanganan" icon="clock" />
            <ol class="relative border-l-2 border-slate-100 ml-2 mt-2">
                @forelse ($complaint->updates as $update)
                    <li class="mb-6 ml-5 last:mb-0">
                        <span class="absolute -left-[9px] w-4 h-4 rounded-full ring-4 ring-white
                            {{ $update->status === \App\Enums\ComplaintStatus::Resolved ? 'bg-emerald-500' : ($update->status === \App\Enums\ComplaintStatus::InProgress ? 'bg-sky-500' : 'bg-amber-500') }}"></span>
                        <div class="flex items-center gap-2 flex-wrap">
                            <x-status-badge :status="$update->status" />
                            <time class="text-xs text-slate-400">{{ $update->created_at->translatedFormat('d M Y, H:i') }}</time>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">oleh {{ $update->author?->name ?? 'Sistem' }}</p>
                        @if ($update->note)
                            <p class="text-sm text-slate-600 mt-1.5">{{ $update->note }}</p>
                        @endif
                    </li>
                @empty
                    <li class="ml-5 text-sm text-slate-500">Belum ada pembaruan.</li>
                @endforelse
            </ol>
        </x-ui.card>
    </div>
</x-app-layout>
