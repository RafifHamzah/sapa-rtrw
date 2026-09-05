<?php

namespace App\Filament\Widgets;

use App\Enums\ComplaintStatus;
use App\Enums\LetterStatus;
use App\Models\Complaint;
use App\Models\Family;
use App\Models\LetterRequest;
use App\Models\Resident;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Ringkasan komunitas untuk dashboard pengurus: jumlah warga & KK, surat yang
 * masih diproses, dan aduan yang masih aktif. Melengkapi ringkasan keuangan.
 */
class CommunityStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Komunitas';

    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $residents = Resident::count();
        $families = Family::count();

        $lettersInProgress = LetterRequest::whereIn('status', [
            LetterStatus::Pending,
            LetterStatus::Processing,
        ])->count();

        $activeComplaints = Complaint::whereIn('status', [
            ComplaintStatus::Open,
            ComplaintStatus::InProgress,
        ])->count();

        return [
            Stat::make('Total Warga', number_format($residents, 0, ',', '.'))
                ->description($families . ' kepala keluarga')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Total KK', number_format($families, 0, ',', '.'))
                ->description('Kartu keluarga terdaftar')
                ->descriptionIcon('heroicon-m-home')
                ->color('info'),

            Stat::make('Surat Diproses', $lettersInProgress . ' surat')
                ->description($lettersInProgress > 0 ? 'Menunggu tindak lanjut' : 'Semua sudah ditangani')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($lettersInProgress > 0 ? 'warning' : 'success'),

            Stat::make('Aduan Aktif', $activeComplaints . ' laporan')
                ->description($activeComplaints > 0 ? 'Belum selesai' : 'Tidak ada aduan aktif')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color($activeComplaints > 0 ? 'danger' : 'success'),
        ];
    }
}
