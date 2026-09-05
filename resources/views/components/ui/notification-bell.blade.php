@php
    $user = auth()->user();
    $notifications = $user->notifications()->latest()->limit(12)->get();
    $unread = $user->unreadNotifications()->count();

    $tone = [
        'success' => 'bg-emerald-100 text-emerald-600',
        'danger'  => 'bg-red-100 text-red-600',
        'warning' => 'bg-amber-100 text-amber-600',
        'info'    => 'bg-blue-100 text-blue-600',
        'gray'    => 'bg-slate-100 text-slate-500',
    ];
@endphp

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button @click="open = !open"
            class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl ring-1 ring-slate-200 text-slate-500 hover:bg-slate-100 transition-colors"
            aria-label="Notifikasi" title="Notifikasi">
        <x-ui.icon name="bell" class="w-5 h-5" />
        @if ($unread > 0)
            <span class="absolute -top-1 -right-1 min-w-[1.15rem] h-[1.15rem] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white">
                {{ $unread > 9 ? '9+' : $unread }}
            </span>
        @endif
    </button>

    <div x-show="open" x-transition @click.outside="open = false" x-cloak
         class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-2xl bg-white shadow-card ring-1 ring-slate-100 z-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
            <p class="text-sm font-semibold text-slate-800">Notifikasi</p>
            @if ($unread > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-brand-600 hover:text-brand-700">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-slate-50">
            @forelse ($notifications as $n)
                @php $d = $n->data; @endphp
                <a href="{{ route('notifications.read', $n->id) }}"
                   @class([
                       'flex gap-3 px-4 py-3 hover:bg-slate-50 transition-colors',
                       'bg-brand-50/50' => is_null($n->read_at),
                   ])>
                    <span @class(['shrink-0 w-9 h-9 rounded-xl flex items-center justify-center', $tone[$d['color'] ?? 'info'] ?? $tone['info']])>
                        <x-ui.icon :name="$d['icon'] ?? 'bell'" class="w-5 h-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800 leading-snug">{{ $d['title'] ?? 'Notifikasi' }}</p>
                        @isset($d['body'])
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $d['body'] }}</p>
                        @endisset
                        <p class="text-[11px] text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                    @if (is_null($n->read_at))
                        <span class="shrink-0 mt-1 w-2 h-2 rounded-full bg-brand-500"></span>
                    @endif
                </a>
            @empty
                <div class="px-4 py-10 text-center">
                    <span class="inline-flex w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 items-center justify-center mb-2">
                        <x-ui.icon name="bell" class="w-6 h-6" />
                    </span>
                    <p class="text-sm text-slate-500">Belum ada notifikasi.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
