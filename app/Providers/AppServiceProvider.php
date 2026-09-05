<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Di belakang proxy TLS (mis. Railway) request masuk sebagai HTTP,
        // sehingga aset ter-generate sebagai http:// → diblokir sbg mixed content.
        // Paksa skema https saat APP_URL memang https (produksi).
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Gate untuk fitur warga yang membutuhkan akun terverifikasi.
        // Dipakai bersama middleware 'verified.resident' dan bisa dipanggil
        // via @can('access-verified-area') di Blade.
        Gate::define('access-verified-area', function (User $user): bool {
            return $user->isVerified();
        });
    }
}
