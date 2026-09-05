<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountStatusController extends Controller
{
    /**
     * Halaman status akun untuk warga yang belum terverifikasi.
     * Warga yang sudah terverifikasi langsung diarahkan ke dashboard.
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isVerified() || ! $user->hasRole('warga')) {
            return redirect()->route('dashboard');
        }

        return view('account.status', ['user' => $user]);
    }
}
