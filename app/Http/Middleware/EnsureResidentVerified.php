<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResidentVerified
{
    /**
     * Blokir warga yang belum terverifikasi (pending) atau ditolak (rejected)
     * dari halaman yang membutuhkan verifikasi. Mereka diarahkan ke halaman
     * status akun. Pengurus/super_admin tidak terpengaruh.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('warga') && ! $user->isVerified()) {
            return redirect()->route('account.status');
        }

        return $next($request);
    }
}
