<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    /** Daftar game yang tersedia + judulnya (untuk label XP). */
    private const GAMES = [
        'pilah-sampah' => 'Pilah Sampah',
        'kuis-administrasi' => 'Kuis Administrasi',
        'tebak-surat' => 'Tebak Jenis Surat',
    ];

    public function __construct(private readonly GamificationService $gamification) {}

    /**
     * Halaman "Belajar Sambil Bermain".
     */
    public function index(): View
    {
        return view('game.index', [
            'games' => [
                ['key' => 'pilah-sampah', 'title' => 'Pilah Sampah 3D', 'emoji' => '🗑️',
                    'desc' => 'Pilah sampah ke tempat yang benar: organik, anorganik, atau B3.',
                    'route' => route('game.pilah-sampah'), 'available' => true],
                ['key' => 'kuis-administrasi', 'title' => 'Kuis Administrasi', 'emoji' => '📋',
                    'desc' => 'Uji pengetahuan seputar RT/RW, surat, iuran, dan lingkungan.',
                    'route' => route('game.kuis-administrasi'), 'available' => true],
                ['key' => 'tebak-surat', 'title' => 'Tebak Jenis Surat', 'emoji' => '📄',
                    'desc' => 'Tebak jenis surat dari skenario kebutuhan warga.',
                    'route' => route('game.tebak-surat'), 'available' => true],
            ],
        ]);
    }

    /**
     * Game Pilah Sampah (drag & drop 3D).
     */
    public function pilahSampah(): View
    {
        return view('game.pilah-sampah');
    }

    /**
     * Game Kuis Administrasi (pilihan ganda).
     */
    public function kuisAdministrasi(): View
    {
        return view('game.kuis-administrasi', [
            'questions' => $this->kuisQuestions(),
        ]);
    }

    /**
     * Game Tebak Jenis Surat (pilihan ganda).
     */
    public function tebakSurat(): View
    {
        return view('game.tebak-surat', [
            'questions' => $this->tebakSuratQuestions(),
        ]);
    }

    /**
     * Klaim XP setelah menyelesaikan sebuah game. Dibatasi satu kali per hari
     * per game (idempoten via source_key bertanggal).
     */
    public function complete(Request $request, string $game): JsonResponse
    {
        if (! array_key_exists($game, self::GAMES)) {
            throw ValidationException::withMessages(['game' => 'Game tidak dikenal.']);
        }

        $data = $request->validate([
            'correct' => ['required', 'integer', 'min:0', 'max:50'],
            'total' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $ratio = min(1, $data['correct'] / max(1, $data['total']));
        $reward = $ratio >= 0.6 ? 40 : 15;
        $user = $request->user();

        $awarded = $this->gamification->award(
            $user, $reward, 'Main "' . self::GAMES[$game] . '"', "game:{$game}:" . now()->toDateString(),
        );
        $this->gamification->syncBadges($user);

        return response()->json([
            'awarded' => $awarded,
            'xp' => $awarded ? $reward : 0,
            'total_xp' => $user->fresh()->xp,
            'level' => $user->fresh()->level(),
            'message' => $awarded
                ? "Keren! Kamu dapat +{$reward} XP. 🎉"
                : 'XP harian untuk game ini sudah kamu dapatkan. Tetap seru main lagi, kok! 😄',
        ]);
    }

    /**
     * Bank soal Kuis Administrasi.
     *
     * @return array<int, array{q: string, options: array<int, string>, answer: string}>
     */
    private function kuisQuestions(): array
    {
        return [
            ['q' => 'Dokumen apa yang wajib dimiliki setiap penduduk berusia 17 tahun ke atas?',
                'options' => ['KK', 'KTP', 'SIM', 'Paspor'], 'answer' => 'KTP'],
            ['q' => 'Surat untuk warga kurang mampu (misalnya untuk beasiswa) disebut?',
                'options' => ['Surat Domisili', 'SKTM', 'SKCK', 'Pengantar Usaha'], 'answer' => 'SKTM'],
            ['q' => 'Di aplikasi SAPA, iuran warga bisa dibayar lewat?',
                'options' => ['Tunai saja', 'Midtrans (online)', 'Wesel pos', 'Tidak bisa'], 'answer' => 'Midtrans (online)'],
            ['q' => 'Kepanjangan dari "KK" adalah?',
                'options' => ['Kartu Kredit', 'Kartu Keluarga', 'Kartu Kuning', 'Kartu Kesehatan'], 'answer' => 'Kartu Keluarga'],
            ['q' => 'Sampah baterai bekas termasuk kategori?',
                'options' => ['Organik', 'Anorganik', 'B3 (Berbahaya)', 'Daur ulang'], 'answer' => 'B3 (Berbahaya)'],
            ['q' => 'Untuk mengurus surat pengantar, warga pertama-tama meminta ke?',
                'options' => ['Camat', 'Ketua RT', 'Gubernur', 'Kepolisian'], 'answer' => 'Ketua RT'],
            ['q' => 'Berikut yang termasuk sampah organik adalah?',
                'options' => ['Botol plastik', 'Sisa sayur', 'Kaleng bekas', 'Baterai'], 'answer' => 'Sisa sayur'],
            ['q' => 'Kegiatan membersihkan lingkungan bersama seluruh warga disebut?',
                'options' => ['Ronda', 'Kerja bakti', 'Arisan', 'Posyandu'], 'answer' => 'Kerja bakti'],
            ['q' => 'Di SAPA, keaslian surat yang sudah disetujui bisa diverifikasi lewat?',
                'options' => ['Tanda tangan basah', 'QR code', 'Cap stempel', 'Nomor telepon'], 'answer' => 'QR code'],
            ['q' => 'Status laporan warga yang sedang ditangani pengurus ditandai sebagai?',
                'options' => ['Baru', 'Sedang Ditangani', 'Selesai', 'Ditolak'], 'answer' => 'Sedang Ditangani'],
        ];
    }

    /**
     * Bank soal Tebak Jenis Surat: cocokkan skenario → jenis surat yang tepat.
     *
     * @return array<int, array{q: string, options: array<int, string>, answer: string}>
     */
    private function tebakSuratQuestions(): array
    {
        $types = ['Surat Keterangan Domisili', 'Surat Keterangan Tidak Mampu (SKTM)', 'Surat Pengantar Usaha'];

        $scenarios = [
            ['Warga ingin membuka rekening bank dan diminta bukti tempat tinggal.', 'Surat Keterangan Domisili'],
            ['Anak warga mengajukan beasiswa karena kondisi ekonomi keluarga terbatas.', 'Surat Keterangan Tidak Mampu (SKTM)'],
            ['Warga membuka warung sembako dan butuh pengantar untuk izin usaha mikro.', 'Surat Pengantar Usaha'],
            ['Warga baru pindah dan butuh keterangan bahwa ia benar tinggal di RT ini.', 'Surat Keterangan Domisili'],
            ['Keluarga kurang mampu mengajukan keringanan biaya berobat di rumah sakit.', 'Surat Keterangan Tidak Mampu (SKTM)'],
            ['Warga ingin mendaftarkan usaha laundry-nya ke kelurahan.', 'Surat Pengantar Usaha'],
            ['Warga mendaftar sekolah anak dan diminta keterangan alamat tinggal.', 'Surat Keterangan Domisili'],
            ['Warga mengajukan bantuan sosial (bansos) untuk keluarga tidak mampu.', 'Surat Keterangan Tidak Mampu (SKTM)'],
            ['Warga mengurus izin PIRT untuk produk makanan rumahan buatannya.', 'Surat Pengantar Usaha'],
            ['Warga melamar pekerjaan dan diminta surat keterangan tempat tinggal.', 'Surat Keterangan Domisili'],
        ];

        return array_map(fn (array $s): array => [
            'q' => $s[0],
            'options' => $types,
            'answer' => $s[1],
        ], $scenarios);
    }
}
