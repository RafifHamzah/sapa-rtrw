<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    /**
     * Alihkan pengguna ke halaman izin Google.
     */
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google belum dikonfigurasi. Hubungi pengurus.',
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Tangani callback Google: tautkan / buat akun warga lalu login.
     *
     * Akun baru dibuat sebagai warga berstatus "pending" — sama seperti
     * registrasi biasa, tetap menunggu verifikasi pengurus. Email dari Google
     * sudah terverifikasi, jadi email_verified_at langsung diisi.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors([
                'email' => 'Gagal masuk dengan Google. Silakan coba lagi.',
            ]);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Google Anda tidak memiliki email yang bisa dipakai.',
            ]);
        }

        // Cocokkan lewat google_id dulu, lalu email (untuk menautkan akun lama).
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            if (! $user->google_id) {
                $user->forceFill(['google_id' => $googleUser->getId()])->save();
            }
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Warga',
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'password' => Str::random(40), // acak — akun ini login lewat Google
                'status' => UserStatus::Pending,
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole('warga');

            event(new Registered($user));
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
