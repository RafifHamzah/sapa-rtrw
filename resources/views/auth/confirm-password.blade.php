<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Konfirmasi Kata Sandi</h1>
        <p class="text-sm text-slate-500 mt-1">Ini area aman. Masukkan kata sandi Anda untuk melanjutkan.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf
        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi</label>
            <input id="password" class="field-input" type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <x-ui.button type="submit" size="lg" class="w-full">Konfirmasi</x-ui.button>
    </form>
</x-guest-layout>
