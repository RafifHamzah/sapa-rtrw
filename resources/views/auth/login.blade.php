<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Masuk</h1>
        <p class="text-sm text-slate-500 mt-1">Selamat datang kembali. Silakan masuk ke akun Anda.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input id="email" class="field-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi</label>
            <input id="password" class="field-input" type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm text-brand-600 hover:text-brand-700 font-medium" href="{{ route('password.request') }}">Lupa sandi?</a>
            @endif
        </div>

        <x-ui.button type="submit" size="lg" class="w-full">Masuk</x-ui.button>
    </form>

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-slate-200"></div></div>
        <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-slate-400">atau</span></div>
    </div>

    <x-google-button label="Masuk dengan Google" />

    <p class="text-center text-sm text-slate-500 mt-6">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Daftar warga</a>
    </p>
</x-guest-layout>
