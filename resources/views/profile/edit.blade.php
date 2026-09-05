<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center"><x-ui.icon name="user" class="w-6 h-6" /></span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Pengaturan Akun</h1>
                <p class="text-sm text-slate-500">Kelola informasi profil & kata sandi Anda.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <x-ui.card>
            @include('profile.partials.update-profile-information-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.update-password-form')
        </x-ui.card>
    </div>
</x-app-layout>
