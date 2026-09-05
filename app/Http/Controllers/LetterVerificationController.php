<?php

namespace App\Http\Controllers;

use App\Enums\LetterStatus;
use App\Models\LetterRequest;
use Illuminate\Contracts\View\View;

class LetterVerificationController extends Controller
{
    /**
     * Halaman verifikasi publik (tanpa login). Token valid → tampilkan detail
     * surat yang sudah disahkan; token ngasal → halaman "tidak ditemukan".
     */
    public function __invoke(string $qr_token): View
    {
        $request = LetterRequest::query()
            ->with(['letterType', 'resident', 'rt'])
            ->where('qr_token', $qr_token)
            ->whereIn('status', [LetterStatus::Approved, LetterStatus::Completed])
            ->first();

        return view('letters.verify', [
            'valid' => $request !== null,
            'request' => $request,
        ]);
    }
}
