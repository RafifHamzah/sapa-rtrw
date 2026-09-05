# 🚀 Panduan Deploy — SAPA

Panduan langkah-demi-langkah menaikkan **SAPA** ke server produksi (domain asli + HTTPS).
Ikuti dari atas ke bawah. Butuh ± 20–30 menit.

> **Ganti** `sapawarga.com` di seluruh dokumen ini dengan domain asli kamu.

---

## 0. Prasyarat server

| Kebutuhan | Versi / Catatan |
|---|---|
| PHP | **8.3+** (ekstensi: `mbstring`, `openssl`, `pdo`, `sqlite3` atau `mysql`, `curl`, `fileinfo`, `gd`, `zip`, `bcmath`) |
| Composer | 2.x |
| Node.js + npm | 18+ (hanya untuk **build aset**, tidak perlu jalan permanen) |
| Web server | Nginx / Apache dengan document root ke folder **`public/`** |
| HTTPS | **Wajib** — pakai Let's Encrypt / sertifikat hosting. Tanpa HTTPS, PWA install & OG preview tidak jalan |

> Hosting yang paling gampang: **Laravel Forge, Railway, Ploi, atau cPanel** yang sudah mendukung Laravel.

---

## 1. Ambil kode & dependensi

```bash
git clone <URL-REPO> sapa && cd sapa

# Dependensi PHP (mode produksi, tanpa dev)
composer install --no-dev --optimize-autoloader

# Dependensi JS + build aset produksi
npm ci
npm run build
```

> `npm run build` menghasilkan folder `public/build/`. **Wajib** dijalankan — tanpa ini CSS/JS tidak muncul.

---

## 2. Konfigurasi `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Lalu **edit `.env`** — minimal yang WAJIB diubah untuk produksi:

```env
APP_NAME="SAPA"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sapawarga.com          # ← domain asli, pakai https

# --- Database (opsi A: SQLite, paling simpel) ---
DB_CONNECTION=sqlite
# (kosongkan DB_HOST/PORT/DATABASE/USERNAME/PASSWORD)

# --- Database (opsi B: MySQL) ---
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=sapa
# DB_USERNAME=sapa
# DB_PASSWORD=rahasia

FILESYSTEM_DISK=public
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

> **APP_KEY** harus terisi (otomatis oleh `key:generate`). Kalau kosong → NIK warga yang terenkripsi tidak bisa dibaca.

### Kalau pakai SQLite (opsi A)
```bash
touch database/database.sqlite
```

---

## 3. Migrasi + data awal

```bash
# Buat semua tabel + isi role/permission (Shield) + akun demo
php artisan migrate --force --seed
```

Ini menjalankan `ShieldSeeder` (role & izin) lalu `DemoSeeder` (1 RT + 14 KK/37 warga + pengurus + data contoh).

> **Kalau tidak mau data demo** (server produksi asli, bukan untuk penjurian):
> jalankan `php artisan migrate --force` lalu `php artisan db:seed --class=ShieldSeeder --force` saja.
> Untuk **submit lomba, biarkan --seed** agar juri bisa langsung login.

---

## 4. Storage, izin folder, & cache

```bash
# Symlink agar PDF surat & foto laporan bisa diakses publik
php artisan storage:link

# Izin tulis (sesuaikan user web server, mis. www-data)
chmod -R 775 storage bootstrap/cache

# Cache konfigurasi & route untuk performa produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Setiap kali mengubah `.env` di produksi, jalankan ulang `php artisan config:cache`.

---

## 5. Integrasi pihak ketiga

### Midtrans (pembayaran iuran)
1. Isi di `.env`: `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_MERCHANT_ID`.
2. Untuk demo lomba, cukup **sandbox**: `MIDTRANS_IS_PRODUCTION=false`.
3. Di **dashboard Midtrans → Settings → Configuration**, set **Payment Notification URL** ke:
   ```
   https://sapawarga.com/midtrans/callback
   ```

### Google Login (opsional)
1. Isi `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` di `.env`.
2. Set `GOOGLE_REDIRECT_URI=https://sapawarga.com/auth/google/callback`.
3. Di **Google Cloud Console → OAuth credentials → Authorized redirect URIs**, tambahkan URL yang sama persis.

> Kalau tidak dipakai, tombol "Masuk dengan Google" tetap tampil tapi tidak wajib berfungsi.

---

## 5b. 🚂 Deploy cepat via Railway (rekomendasi — dipakai untuk submission)

Railway menjalankan Laravel langsung dari GitHub tanpa perlu setup server manual.

1. **railway.app** → Login with GitHub → **New Project** → **Deploy from GitHub repo** → pilih repo `sapa-rtrw`.
2. Tab **Variables** → Raw Editor, paste:
   ```
   APP_NAME=SAPA
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:GENERATE_SENDIRI        # php artisan key:generate --show
   APP_URL=https://TEMP.up.railway.app     # ganti setelah dapat domain (langkah 5)
   DB_CONNECTION=sqlite
   DB_DATABASE=/app/database/database.sqlite
   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   FILESYSTEM_DISK=public
   LOG_CHANNEL=stderr
   ```
   Tambahkan juga (opsional): `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_MERCHANT_ID`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`.
3. Settings → Deploy → **Pre-deploy Command**:
   ```
   php artisan migrate --force --seed && php artisan storage:link
   ```
4. Tunggu build selesai (Railway auto-build: composer install + serve public/).
5. Settings → Networking → **Generate Domain** → salin URL → set `APP_URL` ke URL itu → redeploy.
6. Buka URL → landing SAPA muncul → **inilah link "Web Hosting"** untuk submission.
7. Update Payment Notification URL (Midtrans) & Authorized redirect URI (Google) ke domain Railway.

> Catatan: SQLite di Railway bersifat **ephemeral** (data ke-reset tiap redeploy, lalu di-seed ulang otomatis) — cukup untuk demo penjurian. Untuk data permanen, tambahkan plugin **MySQL/Postgres** Railway lalu petakan `DB_*` ke variabel plugin, dan hapus `--seed` dari pre-deploy setelah seed pertama.

---

## 6. Web server (document root) — untuk VPS/cPanel manual (lewati jika pakai Railway)

Arahkan document root ke folder **`public/`**, bukan root project.

**Nginx (contoh ringkas):**
```nginx
server {
    server_name sapawarga.com;
    root /var/www/sapa/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
Lalu pasang HTTPS (mis. `certbot --nginx`).

---

## 7. ✅ Verifikasi pasca-deploy (checklist submit)

- [ ] Buka `https://sapawarga.com` → landing tampil, CSS rapi (bukan HTML polos)
- [ ] Login juri via akun demo (lihat bawah) → dashboard warga muncul
- [ ] `https://sapawarga.com/admin` → login `admin@rtrw.test` → panel Filament (tema hijau) muncul
- [ ] **OG preview:** cek di [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/) atau [metatags.io](https://metatags.io) → banner SAPA muncul
- [ ] **PWA:** buka di Chrome HP → menu ⋮ → "Instal aplikasi" muncul (atau ikon install di address bar desktop)
- [ ] DevTools → Application → **Service Workers** = "activated", **Manifest** tanpa error
- [ ] Buka detail surat yang disetujui → PDF ke-download, QR bisa di-scan → halaman verifikasi publik jalan
- [ ] Coba bayar 1 iuran (sandbox Midtrans) → status berubah jadi Lunas

---

## 8. 🔑 Akun demo (untuk juri)

Semua password: **`password`**

| Peran | Email | Akses |
|---|---|---|
| Super Admin | `admin@rtrw.test` | Panel `/admin` penuh |
| Pengurus (Bendahara) | `bendahara@rtrw.test` | Panel `/admin` (tanpa kelola peran) |
| Warga | `warga@rtrw.test` | Aplikasi warga (`/dashboard`) |
| Warga lain | `warga2@rtrw.test` … `warga8@rtrw.test` | Aplikasi warga |

> Sebutkan tabel ini di form pengumpulan / README agar juri langsung bisa masuk.

---

## 9. 🩹 Troubleshooting cepat

| Gejala | Penyebab & solusi |
|---|---|
| Halaman polos tanpa style | Aset belum di-build → `npm run build` |
| Error 500 saat buka situs | `APP_KEY` kosong → `php artisan key:generate` lalu `config:cache` |
| Ganti `.env` tapi tak berubah | Cache lama → `php artisan config:clear && php artisan config:cache` |
| PDF/foto tidak muncul | Belum `php artisan storage:link` |
| PWA install tak muncul | Belum HTTPS, atau `APP_URL` masih localhost |
| OG preview kosong/salah | `APP_URL` bukan domain asli → perbaiki + rescrape di debugger |
| Login warga kena 403 di `/admin` | **Normal** — warga memang tidak boleh ke panel; pakai akun `admin@`/`bendahara@` |
| NIK warga tampil aneh/error | `APP_KEY` berubah setelah data terisi → NIK terenkripsi dgn key lama |

---

**Selesai.** Setelah semua ✅ di bagian 7, aplikasi siap dinilai. Semangat, semoga menang! 🏆
