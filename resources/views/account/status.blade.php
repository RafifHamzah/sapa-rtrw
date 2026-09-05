<x-guest-layout>
    @if ($user->isRejected())
        <div class="text-center">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mb-4">
                <x-ui.icon name="x" class="w-8 h-8" />
            </div>
            <h1 class="text-xl font-bold text-slate-900">Pendaftaran Ditolak</h1>
            <p class="text-sm text-slate-500 mt-2">Maaf, pendaftaran akun Anda ditolak oleh pengurus RT.</p>
            @if ($user->rejection_reason)
                <div class="mt-4 rounded-2xl bg-red-50 ring-1 ring-red-100 p-4 text-sm text-red-800 text-left">
                    <span class="font-semibold">Alasan:</span> {{ $user->rejection_reason }}
                </div>
            @endif
            <p class="text-xs text-slate-400 mt-4">Jika Anda merasa ini keliru, silakan hubungi pengurus RT.</p>
        </div>
    @else
        <div class="text-center">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                <x-ui.icon name="clock" class="w-8 h-8" />
            </div>
            <h1 class="text-xl font-bold text-slate-900">Menunggu Verifikasi</h1>
            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                Terima kasih telah mendaftar. Akun Anda sedang menunggu verifikasi
                pengurus RT. Fitur aplikasi akan aktif setelah akun diverifikasi.
            </p>
            <div class="mt-5 flex items-center justify-center gap-2 text-xs text-slate-400">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> Menunggu peninjauan
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-6 pt-5 border-t border-slate-100">
        @csrf
        <x-ui.button type="submit" variant="ghost" class="w-full">
            <x-ui.icon name="logout" class="w-5 h-5" /> Keluar
        </x-ui.button>
    </form>
</x-guest-layout>
