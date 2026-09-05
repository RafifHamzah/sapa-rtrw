<?php

namespace App\Services;

use App\Enums\DuesStatus;
use App\Enums\TransactionType;
use App\Models\Complaint;
use App\Models\Dues;
use App\Models\LetterRequest;
use App\Models\Rt;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * SAPA AI — asisten FAQ berbasis intent (rule/retrieval). Menjawab pertanyaan
 * umum RT/RW dan pertanyaan bergantung data warga yang login. Dirancang agar
 * mudah di-upgrade ke LLM: cukup ganti resolusi jawaban di ask().
 */
class ChatbotService
{
    /**
     * @return array{reply: string, suggestions: array<int, string>, action: array{label: string, url: string}|null}
     */
    public function ask(string $message, ?User $user = null): array
    {
        $intent = $this->matchIntent($message);

        return match ($intent) {
            'iuran_saya' => $this->iuranSaya($user),
            'kas' => $this->kas($user),
            'surat_saya' => $this->suratSaya($user),
            'laporan_saya' => $this->laporanSaya($user),
            'prestasi' => $this->prestasi($user),
            'kontak' => $this->kontak($user),
            default => $this->staticAnswer($intent),
        };
    }

    /**
     * Basis pengetahuan statis: kata kunci → intent + jawaban.
     *
     * @return array<string, array{keywords: array<int, string>, answer?: string, suggestions?: array<int, string>, action?: array{label: string, url: string}}>
     */
    private function knowledgeBase(): array
    {
        return [
            'greeting' => [
                'keywords' => ['halo', 'hai', 'hi', 'pagi', 'siang', 'sore', 'malam', 'assalam', 'permisi'],
                'answer' => 'Halo! 👋 Saya **SAPA AI**, asisten warga Anda. Saya bisa bantu soal iuran, surat pengantar, laporan warga, dan kas RT. Mau tanya apa?',
                'suggestions' => ['Cara bayar iuran', 'Iuran saya berapa?', 'Cara ajukan surat', 'Saldo kas RT'],
            ],
            'what_is_sapa' => [
                'keywords' => ['apa itu sapa', 'aplikasi ini', 'tentang aplikasi', 'sapa itu'],
                'answer' => 'SAPA (Sistem Administrasi dan Pelayanan Antarwarga) adalah aplikasi RT/RW digital: kas transparan, bayar iuran online, surat pengantar ber-QR, pengumuman, dan lapor warga — semua dalam satu tempat.',
                'suggestions' => ['Cara bayar iuran', 'Jenis surat apa saja?', 'Cara lapor warga'],
            ],
            'bayar_iuran' => [
                'keywords' => ['cara bayar', 'bayar iuran', 'iuran online', 'midtrans', 'bayaran', 'cara iuran'],
                'answer' => "Cara bayar iuran:\n1. Buka menu **Iuran**.\n2. Pilih tagihan yang belum lunas, tekan **Bayar**.\n3. Selesaikan pembayaran lewat Midtrans (kartu, e-wallet, atau transfer).\nStatus otomatis jadi **Lunas** setelah pembayaran dikonfirmasi. 🎉",
                'suggestions' => ['Iuran saya berapa?', 'Metode pembayaran apa saja?'],
                'action' => ['label' => 'Buka Iuran', 'url' => '/iuran'],
            ],
            'ajukan_surat' => [
                'keywords' => ['ajukan surat', 'buat surat', 'cara surat', 'minta surat', 'bikin surat', 'mengurus surat'],
                'answer' => "Cara ajukan surat pengantar:\n1. Buka menu **Surat**.\n2. Pilih jenis surat & isi keperluan (plus data tambahan bila diminta).\n3. Kirim — pengurus akan menyetujui.\nSetelah disetujui, unduh **PDF ber-QR** yang bisa diverifikasi publik.",
                'suggestions' => ['Jenis surat apa saja?', 'Status surat saya'],
                'action' => ['label' => 'Ajukan Surat', 'url' => '/letters'],
            ],
            'jenis_surat' => [
                'keywords' => ['jenis surat', 'surat apa saja', 'domisili', 'sktm', 'pengantar usaha', 'macam surat'],
                'answer' => "Jenis surat yang tersedia:\n• **Surat Keterangan Domisili**\n• **Surat Keterangan Tidak Mampu (SKTM)**\n• **Surat Pengantar Usaha**\nPengurus bisa menambah jenis surat lain kapan saja.",
                'suggestions' => ['Cara ajukan surat', 'Status surat saya'],
                'action' => ['label' => 'Ajukan Surat', 'url' => '/letters'],
            ],
            'lapor' => [
                'keywords' => ['cara lapor', 'lapor warga', 'pengaduan', 'keluhan', 'melapor', 'aduan', 'masalah lingkungan'],
                'answer' => "Cara membuat laporan:\n1. Buka menu **Lapor**.\n2. Isi judul, kategori, deskripsi, patokan lokasi, dan foto (opsional).\n3. Kirim — pengurus akan menindaklanjuti dan Anda bisa **melacak statusnya**.",
                'suggestions' => ['Status laporan saya', 'Kategori laporan apa saja?'],
                'action' => ['label' => 'Buat Laporan', 'url' => '/complaints'],
            ],
            'verifikasi_akun' => [
                'keywords' => ['verifikasi', 'akun pending', 'belum aktif', 'menunggu verifikasi', 'akun saya belum'],
                'answer' => 'Akun warga baru berstatus **menunggu verifikasi**. Pengurus RT akan memeriksa data Anda; setelah diverifikasi, semua fitur (iuran, surat, dll) langsung aktif. Bila lama, hubungi pengurus RT.',
                'suggestions' => ['Kontak pengurus', 'Apa itu SAPA?'],
            ],
            'mode_inklusif' => [
                'keywords' => ['inklusif', 'font besar', 'huruf besar', 'lansia', 'kontras', 'aksesibilitas', 'susah baca'],
                'answer' => 'Aktifkan **Mode Inklusif** lewat ikon geser (⚙️) di kanan atas untuk memperbesar teks & menaikkan kontras — nyaman untuk lansia. Preferensinya tersimpan otomatis di perangkat Anda.',
                'suggestions' => ['Apa itu SAPA?'],
            ],
            'kas_info' => [
                'keywords' => ['transparansi', 'buku kas', 'laporan keuangan', 'keuangan rt'],
                'answer' => 'Semua transaksi kas RT terbuka untuk warga di menu **Kas** — lengkap dengan grafik pemasukan/pengeluaran dan saldo real-time.',
                'suggestions' => ['Saldo kas RT', 'Cara bayar iuran'],
                'action' => ['label' => 'Lihat Kas', 'url' => '/kas'],
            ],
            'terima_kasih' => [
                'keywords' => ['makasih', 'terima kasih', 'thanks', 'thank you', 'suwun'],
                'answer' => 'Sama-sama! 😊 Senang bisa membantu. Ada lagi yang ingin ditanyakan?',
                'suggestions' => ['Cara bayar iuran', 'Cara ajukan surat', 'Cara lapor warga'],
            ],
        ];
    }

    private function matchIntent(string $message): string
    {
        $text = Str::lower(Str::ascii(trim($message)));

        // Intent dinamis (butuh data user) — dicek lebih dulu.
        $dynamic = [
            'iuran_saya' => ['iuran saya', 'tagihan saya', 'nunggak', 'belum bayar', 'utang iuran', 'tunggakan saya'],
            'kas' => ['saldo', 'saldo kas', 'kas berapa', 'uang kas', 'keuangan'],
            'surat_saya' => ['surat saya', 'status surat', 'permohonan surat saya', 'surat saya sudah'],
            'laporan_saya' => ['laporan saya', 'status laporan', 'aduan saya', 'pengaduan saya'],
            'prestasi' => ['xp saya', 'level saya', 'badge saya', 'poin saya', 'peringkat saya', 'prestasi saya'],
            'kontak' => ['kontak', 'hubungi pengurus', 'nomor ketua', 'kontak rt', 'nomor rt', 'telepon rt'],
        ];

        $best = 'fallback';
        $bestScore = 0;

        foreach ($dynamic as $key => $keywords) {
            $score = $this->score($text, $keywords) + 0.5; // sedikit prioritas
            if ($score > $bestScore + 0.5) {
                $bestScore = $score;
                $best = $key;
            }
        }

        foreach ($this->knowledgeBase() as $key => $data) {
            $score = $this->score($text, $data['keywords']);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $key;
            }
        }

        return $bestScore > 0 ? $best : 'fallback';
    }

    /**
     * @param  array<int, string>  $keywords
     */
    private function score(string $text, array $keywords): float
    {
        $score = 0;
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) {
                // Frasa lebih panjang bernilai lebih tinggi.
                $score += str_contains($kw, ' ') ? 2 : 1;
            }
        }

        return $score;
    }

    /**
     * @return array{reply: string, suggestions: array<int, string>, action: array{label: string, url: string}|null}
     */
    private function staticAnswer(string $intent): array
    {
        $kb = $this->knowledgeBase();

        if ($intent === 'fallback' || ! isset($kb[$intent])) {
            return $this->reply(
                "Maaf, saya belum paham pertanyaan itu. 🙏 Saya bisa bantu soal **iuran**, **surat**, **laporan warga**, **kas**, dan **verifikasi akun**. Coba salah satu di bawah ini:",
                ['Cara bayar iuran', 'Cara ajukan surat', 'Cara lapor warga', 'Saldo kas RT'],
            );
        }

        $data = $kb[$intent];

        return $this->reply(
            $data['answer'] ?? '',
            $data['suggestions'] ?? [],
            $data['action'] ?? null,
        );
    }

    // --- Jawaban dinamis (data warga) --------------------------------------

    private function iuranSaya(?User $user): array
    {
        $family = $user?->resident?->family;

        if (! $family) {
            return $this->reply('Akun Anda belum tertaut ke data warga, jadi saya belum bisa melihat tagihan iuran. Silakan hubungi pengurus untuk menautkannya.', ['Kontak pengurus']);
        }

        $unpaid = Dues::where('family_id', $family->id)->where('status', '!=', DuesStatus::Paid)->get();

        if ($unpaid->isEmpty()) {
            return $this->reply('Mantap! ✅ Semua iuran keluarga Anda sudah **lunas**. Terima kasih sudah tepat waktu. 🙌', ['Saldo kas RT', 'Prestasi saya']);
        }

        $total = (int) $unpaid->sum('amount');

        return $this->reply(
            "Anda punya **{$unpaid->count()} tagihan** iuran yang belum lunas, total **" . rupiah($total) . "**. Yuk selesaikan lewat menu Iuran. 💳",
            ['Cara bayar iuran'],
            ['label' => 'Bayar Sekarang', 'url' => '/iuran'],
        );
    }

    private function kas(?User $user): array
    {
        $rt = $user?->rt ?? $user?->resident?->family?->rt ?? Rt::query()->first();
        $rtId = $rt?->id;

        $income = (int) Transaction::when($rtId, fn ($q) => $q->where('rt_id', $rtId))
            ->where('type', TransactionType::Income)->sum('amount');
        $expense = (int) Transaction::when($rtId, fn ($q) => $q->where('rt_id', $rtId))
            ->where('type', TransactionType::Expense)->sum('amount');

        return $this->reply(
            'Saldo kas RT saat ini **' . rupiah($income - $expense) . '** (pemasukan ' . rupiah($income) . ', pengeluaran ' . rupiah($expense) . '). Semua transaksinya transparan di menu Kas. 📊',
            ['Cara bayar iuran'],
            ['label' => 'Lihat Kas', 'url' => '/kas'],
        );
    }

    private function suratSaya(?User $user): array
    {
        $residentId = $user?->resident?->id;
        if (! $residentId) {
            return $this->reply('Akun Anda belum tertaut data warga, jadi belum ada riwayat surat. Hubungi pengurus untuk menautkannya.', ['Kontak pengurus']);
        }

        $letters = LetterRequest::with('letterType')->where('resident_id', $residentId)->latest()->get();

        if ($letters->isEmpty()) {
            return $this->reply('Anda belum pernah mengajukan surat. Mau ajukan sekarang?', ['Jenis surat apa saja?'], ['label' => 'Ajukan Surat', 'url' => '/letters']);
        }

        $lines = $letters->take(3)->map(fn ($l) => '• ' . $l->letterType->name . ' — **' . $l->status->getLabel() . '**')->implode("\n");

        return $this->reply(
            "Riwayat surat Anda:\n{$lines}\nBuka menu Surat untuk detail & unduh PDF.",
            ['Cara ajukan surat'],
            ['label' => 'Buka Surat', 'url' => '/letters'],
        );
    }

    private function laporanSaya(?User $user): array
    {
        $complaints = $user ? Complaint::where('user_id', $user->id)->latest()->get() : collect();

        if ($complaints->isEmpty()) {
            return $this->reply('Anda belum membuat laporan. Ada masalah lingkungan yang ingin dilaporkan?', ['Cara lapor warga'], ['label' => 'Buat Laporan', 'url' => '/complaints']);
        }

        $lines = $complaints->take(3)->map(fn ($c) => '• ' . $c->title . ' — **' . $c->status->getLabel() . '**')->implode("\n");

        return $this->reply("Laporan Anda:\n{$lines}\nAnda bisa melihat timeline penanganannya di menu Lapor.", ['Cara lapor warga'], ['label' => 'Lihat Laporan', 'url' => '/complaints']);
    }

    private function prestasi(?User $user): array
    {
        if (! $user) {
            return $this->reply('Silakan masuk untuk melihat prestasi Anda.', []);
        }

        return $this->reply(
            "Anda di **Level {$user->level()}** dengan **" . number_format($user->xp, 0, ',', '.') . " XP** dan **{$user->badges()->count()} badge**. Kumpulkan XP dengan aktif bayar iuran, ajukan surat, dan lapor warga! 🏆",
            ['Papan peringkat'],
            ['label' => 'Lihat Prestasi', 'url' => '/profil'],
        );
    }

    private function kontak(?User $user): array
    {
        $rt = $user?->rt ?? $user?->resident?->family?->rt ?? Rt::query()->first();

        if (! $rt) {
            return $this->reply('Informasi kontak pengurus belum tersedia.', []);
        }

        $chairman = $rt->chairman_name ?? 'Ketua RT';
        $phone = $rt->phone ? " (📞 {$rt->phone})" : '';

        return $this->reply("Ketua RT {$rt->number}/RW {$rt->rw_number}: **{$chairman}**{$phone}. Untuk urusan administratif, Anda juga bisa langsung memakai menu Surat & Lapor di aplikasi.", ['Cara ajukan surat', 'Cara lapor warga']);
    }

    /**
     * @param  array<int, string>  $suggestions
     * @param  array{label: string, url: string}|null  $action
     * @return array{reply: string, suggestions: array<int, string>, action: array{label: string, url: string}|null}
     */
    private function reply(string $reply, array $suggestions = [], ?array $action = null): array
    {
        return ['reply' => $reply, 'suggestions' => $suggestions, 'action' => $action];
    }
}
