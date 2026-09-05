<?php

namespace Database\Seeders;

use App\Enums\AnnouncementCategory;
use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\DuesStatus;
use App\Enums\Gender;
use App\Enums\LetterStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ResidentRelationship;
use App\Enums\TransactionType;
use App\Enums\UserStatus;
use App\Models\Family;
use App\Models\LetterRequest;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use App\Services\ComplaintService;
use App\Services\GamificationService;
use App\Services\LetterService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private int $nikSequence = 1;

    public function run(): void
    {
        $rt = Rt::create([
            'number' => '004',
            'rw_number' => '007',
            'name' => 'RT 04 Melati',
            'village' => 'Sukamaju',
            'district' => 'Cibinong',
            'city' => 'Kabupaten Bogor',
            'province' => 'Jawa Barat',
            'postal_code' => '16913',
            'address' => 'Jl. Melati Raya No. 1, Sukamaju, Cibinong',
            'chairman_name' => 'H. Bambang Sutrisno',
            'phone' => '0812-1000-0001',
        ]);

        [$admin, $ketua, $bendahara, $sekretaris] = $this->seedStaff($rt);

        $this->seedTransactionCategories($rt);
        $this->seedLetterTypes($rt);

        $families = $this->seedFamilies($rt);

        $this->seedDues($rt, $families, $bendahara);
        $this->seedExpensesAndDonations($rt, $bendahara);
        $this->seedLetterRequests($rt, $families, $sekretaris);
        $this->seedAnnouncements($rt, $ketua);
        $this->seedComplaints($rt, $families, $ketua);
        $this->seedSampleWargaAccounts($rt, $ketua);
        $this->seedGamification($families);
    }

    /**
     * Bonus XP untuk membedakan peringkat + sinkron badge final, agar leaderboard
     * & profil prestasi terlihat hidup saat demo.
     *
     * @param  array<int, Family>  $families
     */
    private function seedGamification(array $families): void
    {
        $gamification = app(GamificationService::class);
        $bonuses = [0 => 720, 1 => 430, 2 => 250, 3 => 140, 4 => 70];

        foreach (array_slice($families, 0, 8) as $i => $family) {
            $user = $family->headResident?->user;
            if (! $user) {
                continue;
            }

            if (! empty($bonuses[$i])) {
                $gamification->award($user, $bonuses[$i], 'Kontribusi komunitas', 'seed_bonus:' . $user->id);
            }

            $gamification->syncBadges($user);
        }
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: User} [admin, ketua, bendahara, sekretaris]
     */
    private function seedStaff(Rt $rt): array
    {
        $admin = $this->makeUser($rt, 'Super Admin', 'admin@rtrw.test', 'super_admin', '0812-1000-0000');

        $ketua = $this->makeUser($rt, 'H. Bambang Sutrisno', 'pengurus@rtrw.test', 'pengurus', '0812-1000-0001');
        $bendahara = $this->makeUser($rt, 'Sri Wahyuni', 'bendahara@rtrw.test', 'pengurus', '0812-1000-0002');
        $sekretaris = $this->makeUser($rt, 'Ahmad Fauzi', 'sekretaris@rtrw.test', 'pengurus', '0812-1000-0003');

        return [$admin, $ketua, $bendahara, $sekretaris];
    }

    private function makeUser(Rt $rt, string $name, string $email, string $role, ?string $phone = null): User
    {
        $user = User::create([
            'rt_id' => $rt->id,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'phone' => $phone,
            'status' => UserStatus::Active,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function seedTransactionCategories(Rt $rt): void
    {
        $categories = [
            ['name' => 'Iuran Bulanan', 'type' => TransactionType::Income, 'description' => 'Iuran wajib warga per bulan'],
            ['name' => 'Sumbangan', 'type' => TransactionType::Income, 'description' => 'Donasi sukarela warga/donatur'],
            ['name' => 'Kebersihan', 'type' => TransactionType::Expense, 'description' => 'Honor petugas kebersihan'],
            ['name' => 'Keamanan', 'type' => TransactionType::Expense, 'description' => 'Honor petugas keamanan'],
            ['name' => 'Operasional', 'type' => TransactionType::Expense, 'description' => 'Biaya operasional & ATK RT'],
            ['name' => 'Sosial', 'type' => TransactionType::Expense, 'description' => 'Santunan & kegiatan sosial'],
        ];

        foreach ($categories as $category) {
            $rt->transactionCategories()->create($category);
        }
    }

    private function seedLetterTypes(Rt $rt): void
    {
        $types = [
            [
                'name' => 'Surat Keterangan Domisili',
                'code' => 'DOM',
                'description' => 'Menerangkan tempat tinggal warga.',
                'template_body' => "Yang bertanda tangan di bawah ini menerangkan bahwa:\n\nNama       : {nama}\nNIK        : {nik}\nPekerjaan  : {pekerjaan}\nAlamat     : {alamat}\n\nadalah benar warga yang berdomisili di wilayah RT kami. Surat ini dibuat untuk keperluan {keperluan}.",
                'required_fields' => null,
            ],
            [
                'name' => 'Surat Keterangan Tidak Mampu (SKTM)',
                'code' => 'SKTM',
                'description' => 'Menerangkan kondisi ekonomi tidak mampu.',
                'template_body' => "Yang bertanda tangan di bawah ini menerangkan bahwa:\n\nNama    : {nama}\nNIK     : {nik}\nAlamat  : {alamat}\n\nadalah benar warga yang tergolong keluarga kurang mampu secara ekonomi dengan penghasilan {penghasilan} per bulan. Surat ini dibuat untuk keperluan {keperluan}.",
                'required_fields' => [
                    ['name' => 'penghasilan', 'label' => 'Penghasilan per Bulan', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'Surat Pengantar Usaha',
                'code' => 'USAHA',
                'description' => 'Pengantar untuk keperluan usaha warga.',
                'template_body' => "Yang bertanda tangan di bawah ini menerangkan bahwa:\n\nNama    : {nama}\nNIK     : {nik}\nAlamat  : {alamat}\n\nadalah benar warga kami yang menjalankan usaha dengan data:\n\nNama Usaha    : {nama_usaha}\nJenis Usaha   : {jenis_usaha}\nAlamat Usaha  : {alamat_usaha}\n\nSurat pengantar ini dibuat untuk keperluan {keperluan}.",
                'required_fields' => [
                    ['name' => 'nama_usaha', 'label' => 'Nama Usaha', 'type' => 'text', 'required' => true],
                    ['name' => 'jenis_usaha', 'label' => 'Jenis Usaha', 'type' => 'text', 'required' => true],
                    ['name' => 'alamat_usaha', 'label' => 'Alamat Usaha', 'type' => 'text', 'required' => true],
                ],
            ],
        ];

        foreach ($types as $type) {
            $rt->letterTypes()->create($type + ['is_active' => true]);
        }
    }

    /**
     * 14 KK dengan nama Indonesia yang wajar. Keluarga pertama ditautkan ke akun
     * warga demo (warga@rtrw.test).
     *
     * @return array<int, Family>
     */
    private function seedFamilies(Rt $rt): array
    {
        // [house, head[name,occ,year], spouse[name,occ,year]|null, children[[name,gender,year], ...]]
        $blueprint = [
            ['A-3', ['Budi Santoso', 'Wiraswasta', 1988], ['Siti Aminah', 'Ibu Rumah Tangga', 1990], [['Andi Santoso', Gender::Male, 2015]]],
            ['A-5', ['Joko Prasetyo', 'Pegawai Swasta', 1985], ['Rina Melati', 'Guru', 1987], []],
            ['A-7', ['Agus Salim', 'Pensiunan', 1962], ['Dewi Sartika', 'Wiraswasta', 1968], [['Putri Salim', Gender::Female, 2005]]],
            ['A-9', ['Hendra Gunawan', 'PNS', 1983], ['Lestari Ningsih', 'Bidan', 1986], [['Fajar Gunawan', Gender::Male, 2012]]],
            ['B-2', ['Slamet Riyadi', 'Sopir', 1980], ['Wati Susanti', 'Ibu Rumah Tangga', 1984], [['Bagus Riyadi', Gender::Male, 2010]]],
            ['B-4', ['Eko Purnomo', 'Karyawan Pabrik', 1982], ['Yuni Astuti', 'Penjahit', 1985], []],
            ['B-6', ['Rahmat Hidayat', 'Pedagang', 1979], ['Nurul Hasanah', 'Ibu Rumah Tangga', 1983], [['Fitri Hidayat', Gender::Female, 2008]]],
            ['B-8', ['Dedi Kurniawan', 'Teknisi', 1990], ['Ratna Sari', 'Kasir', 1992], []],
            ['C-1', ['Bambang Wijaya', 'Wiraswasta', 1975], ['Endang Susilowati', 'Ibu Rumah Tangga', 1978], [['Rangga Wijaya', Gender::Male, 2003]]],
            ['C-3', ['Firman Maulana', 'Pengemudi Ojek Online', 1991], ['Ayu Lestari', 'Ibu Rumah Tangga', 1994], [['Rizki Maulana', Gender::Male, 2018]]],
            ['C-5', ['Taufik Hidayat', 'Guru', 1984], ['Maya Puspita', 'Perawat', 1987], []],
            ['C-7', ['Iwan Setiawan', 'Satpam', 1986], ['Dina Marlina', 'Ibu Rumah Tangga', 1989], [['Nabila Setiawan', Gender::Female, 2016]]],
            ['D-2', ['Hendi Saputra', 'Wiraswasta', 1981], ['Sinta Dewi', 'Admin', 1985], []],
            ['D-4', ['Yusuf Ramadhan', 'Pedagang', 1977], ['Ani Rahayu', 'Ibu Rumah Tangga', 1980], [['Zahra Ramadhan', Gender::Female, 2011]]],
        ];

        $families = [];
        $kkSeq = 1;

        foreach ($blueprint as [$house, $head, $spouse, $children]) {
            $family = $rt->families()->create([
                'kk_number' => '3201010101' . str_pad((string) $kkSeq++, 6, '0', STR_PAD_LEFT),
                'address' => "Jl. Melati Raya No. {$house}",
                'house_number' => $house,
                'rt_status' => 'Tetap',
            ]);

            $headResident = $this->makeResident($family, $head[0], Gender::Male, $head[2], ResidentRelationship::Head, $head[1], 'Kawin');
            $family->update(['head_resident_id' => $headResident->id]);

            if ($spouse) {
                $this->makeResident($family, $spouse[0], Gender::Female, $spouse[2], ResidentRelationship::Spouse, $spouse[1], 'Kawin');
            }

            foreach ($children as [$childName, $childGender, $childYear]) {
                $this->makeResident($family, $childName, $childGender, $childYear, ResidentRelationship::Child, 'Pelajar', 'Belum Kawin');
            }

            $families[] = $family;
        }

        // Tautkan 8 kepala keluarga pertama ke akun warga (populasi leaderboard).
        // Dilakukan sebelum seed iuran/laporan agar observer memberi XP ke mereka.
        foreach (array_slice($families, 0, 8) as $i => $family) {
            $warga = User::create([
                'rt_id' => $rt->id,
                'name' => $family->headResident->full_name,
                'email' => $i === 0 ? 'warga@rtrw.test' : 'warga' . ($i + 1) . '@rtrw.test',
                'password' => Hash::make('password'),
                'phone' => '0812-2000-000' . ($i + 1),
                'status' => UserStatus::Active,
            ]);
            $warga->assignRole('warga');
            $family->headResident->update(['user_id' => $warga->id]);
        }

        return $families;
    }

    private function makeResident(Family $family, string $name, Gender $gender, int $birthYear, ResidentRelationship $rel, ?string $occupation, string $maritalStatus): Resident
    {
        return $family->residents()->create([
            'nik' => '3201010107' . str_pad((string) $this->nikSequence++, 6, '0', STR_PAD_LEFT),
            'full_name' => $name,
            'gender' => $gender,
            'birth_place' => 'Bogor',
            'birth_date' => Carbon::create($birthYear, random_int(1, 12), random_int(1, 28)),
            'relationship' => $rel,
            'religion' => 'Islam',
            'marital_status' => $maritalStatus,
            'occupation' => $occupation,
            'phone' => $rel === ResidentRelationship::Head ? '0813' . random_int(10000000, 99999999) : null,
            'is_active' => true,
        ]);
    }

    /**
     * Iuran 6 bulan terakhir. Bulan lampau umumnya lunas; bulan berjalan
     * banyak yang menunggak → grafik & status realistis. Pembayaran berstatus
     * Paid otomatis mencatat kas (income) lewat DuesPaymentObserver.
     *
     * @param  array<int, Family>  $families
     */
    private function seedDues(Rt $rt, array $families, User $recorder): void
    {
        $amount = 50000;
        $methods = [PaymentMethod::Cash, PaymentMethod::Transfer, PaymentMethod::Qris, PaymentMethod::Online];

        // [monthOffset, jumlah KK yang membayar] — 0 = bulan berjalan.
        $plan = [5 => 14, 4 => 14, 3 => 14, 2 => 13, 1 => 10, 0 => 3];

        foreach ($plan as $offset => $paidCount) {
            $period = Carbon::now()->startOfMonth()->subMonths($offset);

            foreach ($families as $index => $family) {
                $dues = $family->dues()->create([
                    'rt_id' => $rt->id,
                    'period_month' => $period->month,
                    'period_year' => $period->year,
                    'amount' => $amount,
                    'status' => DuesStatus::Unpaid,
                    'due_date' => $period->copy()->day(10),
                ]);

                if ($index < $paidCount) {
                    $dues->payments()->create([
                        'amount' => $amount,
                        'payment_method' => $methods[$index % count($methods)],
                        'status' => PaymentStatus::Paid,
                        'paid_at' => $period->copy()->day(random_int(2, 9)),
                        'recorded_by' => $recorder->id,
                        'note' => 'Pembayaran iuran',
                    ]);
                }
            }
        }
    }

    /**
     * Pengeluaran rutin & sumbangan selama 6 bulan agar buku kas & grafik hidup.
     */
    private function seedExpensesAndDonations(Rt $rt, User $recorder): void
    {
        $keamanan = $rt->transactionCategories()->where('name', 'Keamanan')->first();
        $kebersihan = $rt->transactionCategories()->where('name', 'Kebersihan')->first();
        $operasional = $rt->transactionCategories()->where('name', 'Operasional')->first();
        $sosial = $rt->transactionCategories()->where('name', 'Sosial')->first();
        $sumbangan = $rt->transactionCategories()->where('name', 'Sumbangan')->first();

        for ($offset = 5; $offset >= 1; $offset--) {
            $month = Carbon::now()->startOfMonth()->subMonths($offset);
            $label = $month->translatedFormat('F Y');

            $rt->transactions()->createMany([
                [
                    'transaction_category_id' => $keamanan->id,
                    'type' => TransactionType::Expense,
                    'amount' => 300000,
                    'description' => "Honor petugas keamanan {$label}",
                    'transaction_date' => $month->copy()->day(25),
                    'created_by' => $recorder->id,
                ],
                [
                    'transaction_category_id' => $kebersihan->id,
                    'type' => TransactionType::Expense,
                    'amount' => 200000,
                    'description' => "Honor petugas kebersihan {$label}",
                    'transaction_date' => $month->copy()->day(25),
                    'created_by' => $recorder->id,
                ],
                [
                    'transaction_category_id' => $operasional->id,
                    'type' => TransactionType::Expense,
                    'amount' => random_int(50, 150) * 1000,
                    'description' => "Operasional & ATK {$label}",
                    'transaction_date' => $month->copy()->day(20),
                    'created_by' => $recorder->id,
                ],
            ]);
        }

        // Sumbangan & kegiatan sosial insidental.
        $rt->transactions()->createMany([
            [
                'transaction_category_id' => $sumbangan->id,
                'type' => TransactionType::Income,
                'amount' => 1500000,
                'description' => 'Sumbangan warga untuk kegiatan HUT RI',
                'transaction_date' => Carbon::now()->subMonths(1)->day(12),
                'created_by' => $recorder->id,
            ],
            [
                'transaction_category_id' => $sosial->id,
                'type' => TransactionType::Expense,
                'amount' => 750000,
                'description' => 'Santunan anak yatim & perlengkapan lomba HUT RI',
                'transaction_date' => Carbon::now()->subMonths(1)->day(16),
                'created_by' => $recorder->id,
            ],
            [
                'transaction_category_id' => $sumbangan->id,
                'type' => TransactionType::Income,
                'amount' => 500000,
                'description' => 'Donasi renovasi pos ronda',
                'transaction_date' => Carbon::now()->subMonths(3)->day(8),
                'created_by' => $recorder->id,
            ],
        ]);
    }

    /**
     * Permohonan surat di berbagai status; dua di antaranya disetujui via
     * LetterService sehingga memiliki nomor, QR, dan PDF.
     *
     * @param  array<int, Family>  $families
     */
    private function seedLetterRequests(Rt $rt, array $families, User $processor): void
    {
        $domisili = $rt->letterTypes()->where('code', 'DOM')->first();
        $sktm = $rt->letterTypes()->where('code', 'SKTM')->first();
        $usaha = $rt->letterTypes()->where('code', 'USAHA')->first();

        $service = app(LetterService::class);

        // 1) Approved + PDF (warga demo).
        $service->approve(LetterRequest::create([
            'rt_id' => $rt->id,
            'letter_type_id' => $domisili->id,
            'resident_id' => $families[0]->headResident->id,
            'requested_by' => $families[0]->headResident->user_id,
            'purpose' => 'Pembukaan rekening bank',
            'status' => LetterStatus::Pending,
        ]), $processor);

        // 2) Approved + PDF (pengantar usaha, dengan field tambahan).
        $service->approve(LetterRequest::create([
            'rt_id' => $rt->id,
            'letter_type_id' => $usaha->id,
            'resident_id' => $families[5]->headResident->id,
            'purpose' => 'Pengajuan izin usaha mikro',
            'form_data' => ['nama_usaha' => 'Warung Sembako Berkah', 'jenis_usaha' => 'Perdagangan', 'alamat_usaha' => 'Jl. Melati Raya No. B-4'],
            'status' => LetterStatus::Pending,
        ]), $processor);

        // 3) Pending.
        LetterRequest::create([
            'rt_id' => $rt->id,
            'letter_type_id' => $sktm->id,
            'resident_id' => $families[2]->residents()->where('relationship', ResidentRelationship::Child)->first()->id,
            'purpose' => 'Pengajuan beasiswa sekolah',
            'form_data' => ['penghasilan' => 'Rp 1.500.000'],
            'status' => LetterStatus::Pending,
        ]);

        // 4) Rejected (dengan alasan).
        $service->reject(LetterRequest::create([
            'rt_id' => $rt->id,
            'letter_type_id' => $domisili->id,
            'resident_id' => $families[8]->headResident->id,
            'purpose' => 'Keperluan pribadi',
            'status' => LetterStatus::Pending,
        ]), $processor, 'Data kependudukan belum lengkap. Mohon lengkapi KK terlebih dahulu.');
    }

    private function seedAnnouncements(Rt $rt, User $author): void
    {
        $rt->announcements()->createMany([
            [
                'user_id' => $author->id,
                'title' => 'Kerja Bakti Serentak Minggu Ini',
                'category' => AnnouncementCategory::Event,
                'content' => '<p>Diberitahukan kepada seluruh warga untuk mengikuti <strong>kerja bakti</strong> pada hari Minggu pukul 07.00 WIB di lapangan RT. Mohon membawa peralatan kebersihan masing-masing.</p>',
                'is_pinned' => true,
                'published_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $author->id,
                'title' => 'Pemadaman Listrik Terjadwal',
                'category' => AnnouncementCategory::Urgent,
                'content' => '<p>PLN akan melakukan pemeliharaan jaringan pada Kamis, 08.00–12.00 WIB. Harap mempersiapkan kebutuhan sebelumnya.</p>',
                'is_pinned' => true,
                'published_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $author->id,
                'title' => 'Reminder Pembayaran Iuran Bulanan',
                'category' => AnnouncementCategory::Financial,
                'content' => '<p>Mohon warga menyelesaikan pembayaran iuran bulanan sebesar <strong>Rp50.000</strong> paling lambat tanggal 10. Kini bisa dibayar online lewat aplikasi.</p>',
                'is_pinned' => false,
                'published_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $author->id,
                'title' => 'Laporan Keuangan Kas RT Bulan Lalu',
                'category' => AnnouncementCategory::General,
                'content' => '<p>Rekap pemasukan dan pengeluaran kas RT bulan lalu dapat dilihat langsung pada menu <em>Kas Transparan</em> di aplikasi.</p>',
                'is_pinned' => false,
                'published_at' => Carbon::now()->subDays(9),
            ],
            [
                'user_id' => $author->id,
                'title' => '(Draf) Rencana Pengadaan CCTV',
                'category' => AnnouncementCategory::General,
                'content' => '<p>Draf rencana pengadaan CCTV di titik rawan. Belum dipublikasikan.</p>',
                'is_pinned' => false,
                'published_at' => null,
            ],
        ]);
    }

    /**
     * Laporan warga di berbagai status; sebagian digerakkan lewat
     * ComplaintService agar timeline penanganannya terisi.
     *
     * @param  array<int, Family>  $families
     */
    private function seedComplaints(Rt $rt, array $families, User $handler): void
    {
        $service = app(ComplaintService::class);

        // 1) Baru masuk (Open).
        $rt->complaints()->create([
            'resident_id' => $families[3]->headResident->id,
            'user_id' => $families[3]->headResident->user_id,
            'title' => 'Sampah menumpuk di TPS',
            'category' => ComplaintCategory::Environment,
            'description' => 'Tempat pembuangan sementara penuh dan belum diangkut selama 3 hari.',
            'location' => 'TPS ujung Jl. Melati',
            'status' => ComplaintStatus::Open,
        ]);

        // 2) Sedang ditangani (in_progress) — punya timeline.
        $service->changeStatus(
            $rt->complaints()->create([
                'resident_id' => $families[1]->headResident->id,
                'user_id' => $families[1]->headResident->user_id,
                'title' => 'Lampu jalan mati',
                'category' => ComplaintCategory::Infrastructure,
                'description' => 'Lampu penerangan jalan di depan blok A sudah mati sejak seminggu lalu.',
                'location' => 'Jl. Melati Raya depan A-5',
                'status' => ComplaintStatus::Open,
            ]),
            ComplaintStatus::InProgress,
            $handler,
            'Sudah dilaporkan ke kelurahan, menunggu penggantian lampu.',
            'Terima kasih atas laporannya, sedang kami tindak lanjuti.',
        );

        // 3) Selesai (resolved) — timeline penuh.
        $laporan3 = $rt->complaints()->create([
            'resident_id' => $families[4]->headResident->id,
            'user_id' => $families[4]->headResident->user_id,
            'title' => 'Saluran air tersumbat',
            'category' => ComplaintCategory::Infrastructure,
            'description' => 'Selokan depan rumah tersumbat menyebabkan genangan saat hujan.',
            'location' => 'Depan rumah B-2',
            'status' => ComplaintStatus::Open,
        ]);
        $service->changeStatus($laporan3, ComplaintStatus::InProgress, $handler, 'Dijadwalkan pembersihan gotong royong.');
        $service->changeStatus($laporan3->fresh(), ComplaintStatus::Resolved, $handler, 'Selokan sudah dibersihkan bersama warga.', 'Masalah telah selesai ditangani. Terima kasih.');

        // 4) Ditolak (rejected).
        $service->changeStatus(
            $rt->complaints()->create([
                'resident_id' => $families[6]->headResident->id,
                'user_id' => null,
                'title' => 'Keluhan suara bising',
                'category' => ComplaintCategory::Social,
                'description' => 'Laporan tanpa detail lokasi & waktu yang jelas.',
                'status' => ComplaintStatus::Open,
            ]),
            ComplaintStatus::Rejected,
            $handler,
            'Laporan kurang lengkap. Mohon sertakan detail waktu & lokasi.',
        );
    }

    /**
     * Akun warga untuk mendemokan alur verifikasi: satu menunggu, satu ditolak.
     */
    private function seedSampleWargaAccounts(Rt $rt, User $verifier): void
    {
        $pending = User::create([
            'rt_id' => $rt->id,
            'name' => 'Warga Pending',
            'email' => 'pending@rtrw.test',
            'password' => Hash::make('password'),
            'phone' => '0812-3000-0001',
            'status' => UserStatus::Pending,
        ]);
        $pending->assignRole('warga');

        $rejected = User::create([
            'rt_id' => $rt->id,
            'name' => 'Warga Ditolak',
            'email' => 'ditolak@rtrw.test',
            'password' => Hash::make('password'),
            'phone' => '0812-3000-0002',
            'status' => UserStatus::Rejected,
            'rejection_reason' => 'Data tidak sesuai dengan warga terdaftar.',
            'verified_by' => $verifier->id,
        ]);
        $rejected->assignRole('warga');
    }
}
