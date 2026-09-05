<section>
    <header class="mb-5">
        <h2 class="text-lg font-bold text-slate-900">Informasi Profil</h2>
        <p class="mt-1 text-sm text-slate-500">Perbarui nama dan alamat email akun Anda.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
            <input id="name" name="name" type="text" class="field-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input id="email" name="email" type="email" class="field-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="text-sm mt-2 text-slate-600">
                    Email Anda belum terverifikasi.
                    <button form="send-verification" class="font-medium text-brand-600 hover:text-brand-700 underline">Kirim ulang tautan verifikasi.</button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-emerald-600">Tautan verifikasi baru telah dikirim ke email Anda.</p>
                @endif
            @endif
        </div>

        <div class="flex items-center gap-4 pt-1">
            <x-ui.button type="submit">Simpan</x-ui.button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm text-emerald-600 font-medium">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
