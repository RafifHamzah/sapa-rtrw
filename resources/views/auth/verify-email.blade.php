<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Verifikasi Email</h1>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            Terima kasih telah mendaftar! Silakan verifikasi email Anda dengan
            mengeklik tautan yang baru kami kirim. Belum menerima email?
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-xl bg-emerald-50 ring-1 ring-emerald-200 px-4 py-3 text-sm text-emerald-800">
            Tautan verifikasi baru telah dikirim ke email Anda.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <x-ui.button type="submit" size="lg" class="w-full">Kirim Ulang Email Verifikasi</x-ui.button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <x-ui.button type="submit" variant="ghost" class="w-full">Keluar</x-ui.button>
    </form>
</x-guest-layout>
