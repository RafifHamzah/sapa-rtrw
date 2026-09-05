<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Lupa Kata Sandi</h1>
        <p class="text-sm text-slate-500 mt-1">Masukkan email Anda, kami kirimkan tautan untuk mengatur ulang kata sandi.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input id="email" class="field-input" type="email" name="email" value="{{ old('email') }}" required autofocus>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <x-ui.button type="submit" size="lg" class="w-full">Kirim Tautan Reset</x-ui.button>
    </form>

    <p class="text-center text-sm text-slate-500 mt-6">
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">← Kembali ke halaman masuk</a>
    </p>
</x-guest-layout>
