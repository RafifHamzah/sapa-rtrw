<?php

use App\Http\Controllers\AccountStatusController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LetterVerificationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Landing publik tetap tampil walau DB belum siap (rescue → 0).
    return view('welcome', [
        'stats' => [
            'families' => rescue(fn () => \App\Models\Family::count(), 0, false),
            'residents' => rescue(fn () => \App\Models\Resident::count(), 0, false),
            'letters' => rescue(fn () => \App\Models\LetterRequest::whereNotNull('letter_number')->count(), 0, false),
            'transactions' => rescue(fn () => \App\Models\Transaction::count(), 0, false),
        ],
    ]);
});

// Halaman status akun — dapat diakses warga pending/rejected (tanpa gate verifikasi).
Route::get('/account/status', AccountStatusController::class)
    ->middleware('auth')
    ->name('account.status');

// Dashboard & fitur warga hanya untuk akun yang sudah terverifikasi.
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'verified.resident'])->name('dashboard');

Route::middleware(['auth', 'verified.resident'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Kas transparan (read-only) & Iuran saya (pembayaran Midtrans via Livewire).
    Route::get('/kas', [KasController::class, 'index'])->name('kas.index');
    Route::view('/iuran', 'iuran.index')->name('iuran.index');

    // Gamifikasi: profil prestasi & papan peringkat.
    Route::get('/profil', [GamificationController::class, 'profile'])->name('profile.show');
    Route::get('/peringkat', [GamificationController::class, 'leaderboard'])->name('leaderboard');

    // SAPA AI — asisten FAQ.
    Route::post('/assistant/ask', [AssistantController::class, 'ask'])->name('assistant.ask');

    // Notifikasi warga (lonceng).
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Belajar Sambil Bermain (mini-game edukasi).
    Route::get('/game', [GameController::class, 'index'])->name('game.index');
    Route::get('/game/pilah-sampah', [GameController::class, 'pilahSampah'])->name('game.pilah-sampah');
    Route::get('/game/kuis-administrasi', [GameController::class, 'kuisAdministrasi'])->name('game.kuis-administrasi');
    Route::get('/game/tebak-surat', [GameController::class, 'tebakSurat'])->name('game.tebak-surat');
    Route::post('/game/{game}/complete', [GameController::class, 'complete'])->name('game.complete');
});

// Pembayaran iuran online (Midtrans).
Route::post('/dues/{dues}/pay', [PaymentController::class, 'pay'])
    ->middleware(['auth', 'verified.resident'])
    ->name('dues.pay');

// Webhook Midtrans — tanpa auth, dikecualikan dari CSRF (lihat bootstrap/app.php).
Route::post('/midtrans/callback', [PaymentController::class, 'callback'])
    ->name('midtrans.callback');

// Verifikasi surat publik (tanpa login).
Route::get('/verify/{qr_token}', LetterVerificationController::class)->name('letters.verify');

// Surat pengantar (warga terverifikasi).
Route::middleware(['auth', 'verified.resident'])->group(function () {
    Route::get('/letters', [LetterController::class, 'index'])->name('letters.index');
    Route::post('/letters', [LetterController::class, 'store'])->name('letters.store');
    Route::get('/letters/{letter}/download', [LetterController::class, 'download'])->name('letters.download');

    // Pengumuman (feed warga) & Lapor Warga (pengaduan).
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
});

require __DIR__.'/auth.php';
