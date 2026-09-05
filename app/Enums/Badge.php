<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Badge: string implements HasLabel, HasColor
{
    case WargaTeladan = 'warga_teladan';
    case RajinBayarIuran = 'rajin_bayar_iuran';
    case TepatWaktuBayar = 'tepat_waktu_bayar';
    case AktifGotongRoyong = 'aktif_gotong_royong';
    case PelaporAktif = 'pelapor_aktif';
    case RelawanLingkungan = 'relawan_lingkungan';
    case UmkmInspiratif = 'umkm_inspiratif';
    case KontributorTerbaik = 'kontributor_terbaik';

    public function getLabel(): string
    {
        return match ($this) {
            self::WargaTeladan => 'Warga Teladan',
            self::RajinBayarIuran => 'Rajin Bayar Iuran',
            self::TepatWaktuBayar => 'Tepat Waktu Bayar',
            self::AktifGotongRoyong => 'Aktif Gotong Royong',
            self::PelaporAktif => 'Pelapor Aktif',
            self::RelawanLingkungan => 'Relawan Lingkungan',
            self::UmkmInspiratif => 'UMKM Inspiratif',
            self::KontributorTerbaik => 'Kontributor Terbaik',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::WargaTeladan => 'Kumpulkan 500 XP dari aktivitas warga.',
            self::RajinBayarIuran => 'Lunasi iuran minimal 3 periode.',
            self::TepatWaktuBayar => 'Bayar iuran sebelum jatuh tempo.',
            self::AktifGotongRoyong => 'Aktif di 5+ aktivitas lingkungan.',
            self::PelaporAktif => 'Kirim 3 laporan warga.',
            self::RelawanLingkungan => 'Laporkan isu lingkungan/kebersihan.',
            self::UmkmInspiratif => 'Miliki surat pengantar usaha yang disetujui.',
            self::KontributorTerbaik => 'Kumpulkan 1.000 XP — kontributor teratas.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WargaTeladan => 'trophy',
            self::RajinBayarIuran => 'receipt',
            self::TepatWaktuBayar => 'clock',
            self::AktifGotongRoyong => 'users',
            self::PelaporAktif => 'alert',
            self::RelawanLingkungan => 'sparkles',
            self::UmkmInspiratif => 'chart',
            self::KontributorTerbaik => 'star',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::WargaTeladan, self::KontributorTerbaik => 'primary',
            self::RajinBayarIuran, self::RelawanLingkungan => 'success',
            self::TepatWaktuBayar => 'info',
            self::AktifGotongRoyong, self::UmkmInspiratif => 'warning',
            self::PelaporAktif => 'danger',
        };
    }

    /** Bonus XP saat badge pertama kali diraih. */
    public function xpBonus(): int
    {
        return 100;
    }
}
