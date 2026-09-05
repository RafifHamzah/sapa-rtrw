<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Tandai satu notifikasi sebagai dibaca lalu arahkan ke halaman terkait
     * (mis. daftar surat / detail laporan). Hanya notifikasi milik user sendiri.
     */
    public function read(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->whereKey($id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        $url = $notification?->data['url'] ?? url()->previous();

        return redirect()->to($url);
    }

    /**
     * Tandai semua notifikasi user sebagai dibaca.
     */
    public function readAll(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }
}
