# Database Schema — Platform Digital RT/RW

> Sumber kebenaran skema database. Fase 1 (MVP) mencakup tabel bertanda ✅.
> Tabel opsional (🔵) ditunda ke fase berikutnya.
>
> Konvensi:
> - Semua nominal uang: `unsignedBigInteger` (Rupiah, tanpa desimal).
> - Semua kolom enum di-back oleh PHP backed enum (lihat `app/Enums`).
> - Timestamps (`created_at`, `updated_at`) ada di semua tabel kecuali disebut lain.
> - FK memakai `foreignId(...)->constrained()` dan diberi index.

## Enum reference

| Enum | Values |
|------|--------|
| `UserStatus` | `pending`, `active` (= terverifikasi), `inactive`, `suspended`, `rejected` |
| `Gender` | `male`, `female` |
| `ResidentRelationship` | `head`, `spouse`, `child`, `parent`, `sibling`, `other` |
| `TransactionType` | `income`, `expense` |
| `DuesStatus` | `unpaid`, `partial`, `paid`, `overdue` |
| `PaymentMethod` | `cash`, `transfer`, `qris`, `online`, `other` |
| `PaymentStatus` | `pending`, `paid`, `failed`, `expired`, `cancelled` |
| `LetterStatus` | `pending`, `processing`, `approved`, `rejected`, `completed` |
| `AnnouncementCategory` | `general`, `event`, `urgent`, `maintenance`, `financial` |
| `ComplaintCategory` | `infrastructure`, `security`, `environment`, `social`, `administration`, `other` |
| `ComplaintStatus` | `open`, `in_progress`, `resolved`, `closed`, `rejected` |

---

## ✅ rts
Unit RT (Rukun Tetangga) — root tenant dari semua data.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| number | string | Nomor RT, mis. `001` |
| rw_number | string | Nomor RW, mis. `005` |
| name | string, nullable | Nama/label RT |
| village | string | Kelurahan/Desa |
| district | string | Kecamatan |
| city | string | Kota/Kabupaten |
| province | string | Provinsi |
| postal_code | string, nullable | Kode pos |
| address | text, nullable | Alamat sekretariat RT |
| chairman_name | string, nullable | Nama Ketua RT |
| phone | string, nullable | Kontak RT |
| timestamps | | |

## ✅ users (extend default)
Akun login. Warga bisa punya akun (opsional), pengurus & admin wajib.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| rt_id | FK rts, nullable | index |
| name | string | bawaan |
| email | string, unique | bawaan |
| email_verified_at | timestamp, nullable | bawaan |
| password | string | bawaan, hashed |
| phone | string, nullable | |
| status | enum `UserStatus` | default `active`, index. Warga baru = `pending` |
| verified_at | timestamp, nullable | Waktu verifikasi oleh pengurus (Fase 2) |
| verified_by | FK users, nullable | Pengurus/admin yg memverifikasi, `nullOnDelete` |
| rejection_reason | text, nullable | Alasan bila pendaftaran ditolak |
| remember_token | | bawaan |
| timestamps | | |

## ✅ families
Kartu Keluarga (KK).

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| rt_id | FK rts | index |
| kk_number | string, unique | Nomor KK (16 digit) |
| head_resident_id | FK residents, nullable | Kepala keluarga; nullable krn chicken-egg dgn residents. `nullOnDelete` |
| address | text | Alamat rumah |
| house_number | string, nullable | Nomor rumah/blok |
| rt_status | string, nullable | Status kependudukan KK di RT (mis. tetap/kontrak) |
| timestamps | | |

## ✅ residents
Penduduk / anggota keluarga.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| family_id | FK families | index, `cascadeOnDelete` |
| user_id | FK users, nullable | index, `nullOnDelete`. Akun login resident |
| nik | string (encrypted) | NIK 16 digit — dienkripsi at rest |
| full_name | string | |
| gender | enum `Gender` | index |
| birth_place | string, nullable | |
| birth_date | date | |
| relationship | enum `ResidentRelationship` | Relasi ke kepala keluarga, index |
| religion | string, nullable | |
| marital_status | string, nullable | |
| occupation | string, nullable | |
| phone | string, nullable | |
| is_active | boolean | default true |
| timestamps | | |

## ✅ transaction_categories
Kategori kas (pemasukan/pengeluaran).

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| rt_id | FK rts | index |
| name | string | |
| type | enum `TransactionType` | index |
| description | string, nullable | |
| timestamps | | |

## ✅ transactions  *(softDeletes)*
Buku kas RT.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| rt_id | FK rts | index |
| transaction_category_id | FK transaction_categories | index, `restrictOnDelete` |
| type | enum `TransactionType` | index |
| amount | unsignedBigInteger | Rupiah |
| description | string | |
| transaction_date | date | index |
| receipt_path | string, nullable | Foto nota (disk `public`) |
| created_by | FK users, nullable | `nullOnDelete`. null = dicatat sistem |
| dues_payment_id | FK dues_payments, nullable | `nullOnDelete`. Tautan kanonik ke pembayaran iuran sumber (idempotensi kas). Fase 3 |
| timestamps + softDeletes | | |

## ✅ dues  *(softDeletes)*
Iuran bulanan per keluarga.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| rt_id | FK rts | index |
| family_id | FK families | index, `cascadeOnDelete` |
| period_month | unsignedTinyInteger | 1–12 |
| period_year | unsignedSmallInteger | mis. 2026 |
| amount | unsignedBigInteger | Nominal iuran (Rupiah) |
| status | enum `DuesStatus` | default `unpaid`, index |
| due_date | date, nullable | |
| timestamps + softDeletes | | |
| **unique** | (`family_id`, `period_month`, `period_year`) | 1 tagihan per keluarga per periode |

## ✅ dues_payments
Pembayaran iuran (tunai atau online via Midtrans). Saat pembayaran menjadi
`paid`, `DuesPaymentObserver` otomatis menandai dues lunas & mencatat 1 baris
kas (income) yang menunjuk balik lewat `transactions.dues_payment_id`.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| dues_id | FK dues | index, `cascadeOnDelete` |
| amount | unsignedBigInteger | Rupiah |
| payment_method | enum `PaymentMethod` | index |
| status | enum `PaymentStatus` | default `pending`, index. Fase 3 |
| midtrans_order_id | string, nullable, unique | order_id Snap. Fase 3 |
| midtrans_transaction_id | string, nullable | dari notifikasi Midtrans. Fase 3 |
| midtrans_status | string, nullable | transaction_status mentah Midtrans. Fase 3 |
| paid_at | datetime, nullable | terisi saat lunas |
| recorded_by | FK users, nullable | `nullOnDelete` |
| note | string, nullable | |
| timestamps | | |

> Catatan Fase 3: tautan ke kas dibalik dari Fase 1 — kolom `transaction_id`
> dihapus dari sini; kanoniknya sekarang `transactions.dues_payment_id`.

## ✅ letter_types
Jenis surat + template. Placeholder yang didukung: `{nama}`, `{nik}`, `{keperluan}`.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| rt_id | FK rts, nullable | index. null = template global |
| name | string | mis. "Surat Keterangan Domisili" |
| code | string, unique | slug, mis. `domisili` |
| description | string, nullable | |
| template_body | text | Body surat dengan placeholder |
| required_fields | json, nullable | Field tambahan pemohon `[{name,label,type,required}]`. Fase 4 |
| is_active | boolean | default true |
| timestamps | | |

## ✅ letter_requests
Pengajuan surat oleh warga.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| rt_id | FK rts | index |
| letter_type_id | FK letter_types | index, `restrictOnDelete` |
| resident_id | FK residents | index, `cascadeOnDelete`. Pemohon |
| requested_by | FK users, nullable | `nullOnDelete`. User yg mengajukan |
| purpose | text | Keperluan → placeholder `{keperluan}` |
| form_data | json, nullable | Isian field tambahan `{key: value}`. Fase 4 |
| status | enum `LetterStatus` | default `pending`, index |
| letter_number | string, nullable | Nomor surat: `001/DOM/RT05/2026` (per RT per tahun) |
| qr_token | string, nullable, unique | Token verifikasi publik (40 hex). Fase 4 |
| pdf_path | string, nullable | PDF hasil generate (disk public). Fase 4 |
| notes | text, nullable | Catatan pengurus / alasan tolak |
| processed_by | FK users, nullable | `nullOnDelete` |
| processed_at | datetime, nullable | |
| timestamps | | |

## ✅ announcements
Pengumuman RT.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| rt_id | FK rts | index |
| user_id | FK users, nullable | `nullOnDelete`. Penulis |
| title | string | |
| category | enum `AnnouncementCategory` | index |
| content | text | |
| attachment_path | string, nullable | Lampiran (disk public). Fase 5 |
| is_pinned | boolean | default false |
| published_at | datetime, nullable | null = draft; index `(is_pinned, published_at)` |
| timestamps | | |

## ✅ complaints
Pengaduan warga.

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| rt_id | FK rts | index |
| resident_id | FK residents, nullable | `nullOnDelete`. Pelapor |
| user_id | FK users, nullable | `nullOnDelete`. Akun pelapor |
| title | string | |
| category | enum `ComplaintCategory` | index |
| description | text | |
| location | string, nullable | Lokasi kejadian |
| photo_path | string, nullable | Foto laporan (disk public). Fase 5 |
| status | enum `ComplaintStatus` | default `open` (= "reported"), index. Alur: open→in_progress→resolved |
| response | text, nullable | Tanggapan pengurus |
| handled_by | FK users, nullable | `nullOnDelete` |
| resolved_at | datetime, nullable | |
| timestamps | | |

## ✅ complaint_updates *(Fase 5)*
Timeline penanganan laporan (riwayat perubahan status + catatan pengurus).

| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | PK |
| complaint_id | FK complaints | index, `cascadeOnDelete` |
| user_id | FK users, nullable | `nullOnDelete`. Pengurus/sistem |
| status | enum `ComplaintStatus` | index. Status setelah pembaruan ini |
| note | text, nullable | Catatan penanganan |
| timestamps | | |

---

## Relasi ringkas

- `RT` hasMany: users, families, residents, transactionCategories, transactions, dues, letterRequests, announcements, complaints; hasMany letterTypes.
- `Family` belongsTo rt; hasMany residents; belongsTo headResident (Resident); hasMany dues.
- `Resident` belongsTo family; belongsTo user; hasMany letterRequests; hasMany complaints.
- `User` belongsTo rt; hasOne resident; + Spatie HasRoles.
- `TransactionCategory` belongsTo rt; hasMany transactions.
- `Transaction` belongsTo rt, transactionCategory (category), creator (User), duesPayment (softDeletes).
- `Dues` belongsTo rt, family; hasMany payments (DuesPayment) (softDeletes).
- `DuesPayment` belongsTo dues, recorder (User); hasOne transaction (via `transactions.dues_payment_id`).
- `LetterType` belongsTo rt; hasMany letterRequests.
- `LetterRequest` belongsTo rt, letterType, resident, requester (User), processor (User).
- `Announcement` belongsTo rt, author (User); scope `published()` (pinned dulu lalu terbaru).
- `Complaint` belongsTo rt, resident, reporter (User), handler (User); hasMany updates (ComplaintUpdate).
- `ComplaintUpdate` belongsTo complaint, author (User).

## 🔵 Ditunda (opsional, fase berikutnya)
- `facilities`, `facility_bookings` (peminjaman fasilitas)
- `events`, `event_participants` (agenda kegiatan)
- `documents` (arsip dokumen)
- `notifications` (custom, di luar bawaan Laravel)
