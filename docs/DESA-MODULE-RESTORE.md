# Modul Desa — Panduan Restore

Modul **Desa** (multi-tenant: buat desa, gabung via kode, role per-desa, kas & iuran
per-desa) **dihapus** dari aplikasi pada 2026-08-11 karena SAPA difokuskan sebagai
sistem administrasi **satu komunitas** (RT 004/RW 07 Sukamaju), bukan platform banyak desa.

Semua kode diarsipkan di **`docs/desa-module-backup.tar.gz`** dan bisa dipulihkan penuh.

## Cara restore

### 1. Ekstrak file eksklusif (aman — file ini dulu tidak ada sebelum modul Desa)
```bash
cd /Users/rafifhamzah/RT:RW
tar xzf docs/desa-module-backup.tar.gz
```
Ini mengembalikan: 5 migration `desas*`, 4 enum `Desa*`, 5 model `Desa*`,
`DesaPolicy`, `DesaFinanceService`, 5 controller `Desa*Controller`, folder
`resources/views/desa/`, dan `resources/views/components/desa-tabs.blade.php`.
Juga membuat folder **`_desabk_shared/`** berisi salinan referensi (lihat langkah 2).

### 2. Terapkan lagi perubahan pada 5 file BERSAMA (shared)
File ini dipakai bareng fitur lain, jadi TIDAK ditimpa otomatis. Bandingkan versi
saat ini dengan salinan referensi di `_desabk_shared/` lalu masukkan lagi potongan
berikut:

**`app/Http/Controllers/Controller.php`** — aktifkan trait otorisasi:
```php
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
abstract class Controller { use AuthorizesRequests; }
```

**`app/Models/User.php`** — tambah import `App\Enums\DesaRole` &
`Illuminate\Database\Eloquent\Relations\BelongsToMany`, lalu relasi/helper desa:
`desaMemberships()`, `desas()`, `desaJoinRequests()`, `desaDues()`, `desaRole()`,
`isMemberOfDesa()`, `isDesaAdmin()`, `activeDesa()`, `activeDesaRole()`.
(Lihat `_desabk_shared/User.php`.)

**`app/Services/MidtransService.php`** — tambah `use App\Models\DesaDues;` dan method
`createSnapTokenForDesaDues()`, `fetchTransactionStatus()`, `isPaidPayload()`.
(Lihat `_desabk_shared/MidtransService.php`.)

**`routes/web.php`** — tambah 5 import controller `Desa*` dan grup route:
```php
Route::middleware(['auth', 'verified.resident'])->prefix('desa')->name('desa.')->group(function () { /* ... */ });
```
(Lihat `_desabk_shared/web.php`.)

**`resources/views/layouts/app.blade.php`** — tambah item nav
`['route' => 'desa.index', 'label' => 'Desa', 'icon' => 'map-pin']` dan ubah grid
bottom-nav mobile `grid-cols-6` → `grid-cols-7`.
(Lihat `_desabk_shared/app.blade.php`.)

### 3. Migrasi ulang + build
```bash
php artisan migrate
./node_modules/.bin/vite build
php artisan config:clear
```

### 4. Bersihkan
```bash
rm -rf _desabk_shared
```

## Catatan
- Kolom `users.google_id` TETAP ADA (itu untuk Login Google, bukan modul Desa).
- Saat dihapus, tabel `desas`, `desa_members`, `desa_join_requests`, `desa_dues`,
  `desa_transactions` di-drop dan barisnya dihapus dari tabel `migrations`.
- Referensi arsitektur lengkap ada di artifact "Arsitektur SAPA".
