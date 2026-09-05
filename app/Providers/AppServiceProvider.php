<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        // Gate untuk fitur warga yang membutuhkan akun terverifikasi.
        // Dipakai bersama middleware 'verified.resident' dan bisa dipanggil
        // via @can('access-verified-area') di Blade.
        Gate::define('access-verified-area', function (User $user): bool {
            return $user->isVerified();
        });
    }
}
