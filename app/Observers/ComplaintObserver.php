<?php

namespace App\Observers;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintStatusNotification;
use Filament\Notifications\Notification;

class ComplaintObserver
{
    /**
     * Saat laporan baru masuk:
     *   1. Catat entri awal ke timeline penanganan.
     *   2. Kirim notifikasi in-app (database) ke pengurus & super_admin.
     */
    public function created(Complaint $complaint): void
    {
        $complaint->updates()->create([
            'user_id' => $complaint->user_id,
            'status' => $complaint->status,
            'note' => 'Laporan diterima dan menunggu tindak lanjut.',
        ]);

        $this->notifyManagers($complaint);

        // Gamifikasi: XP untuk pelapor (idempoten per laporan).
        if ($complaint->reporter) {
            app(\App\Services\GamificationService::class)
                ->recordActivity($complaint->reporter, 30, 'Lapor warga', 'complaint:' . $complaint->id);
        }
    }

    /**
     * Saat status laporan berubah, kabari warga pelapor lewat lonceng in-app.
     * Ditaruh di observer agar mencakup semua jalur perubahan (aksi tabel Filament
     * maupun halaman edit resource). Hanya untuk status tindak lanjut yang berarti.
     */
    public function updated(Complaint $complaint): void
    {
        if (! $complaint->wasChanged('status')) {
            return;
        }

        $notifiable = in_array($complaint->status, [
            ComplaintStatus::InProgress,
            ComplaintStatus::Resolved,
            ComplaintStatus::Closed,
            ComplaintStatus::Rejected,
        ], true);

        if (! $notifiable || ! $complaint->reporter) {
            return;
        }

        rescue(
            fn () => $complaint->reporter->notify(new ComplaintStatusNotification($complaint)),
            report: false,
        );
    }

    private function notifyManagers(Complaint $complaint): void
    {
        $managers = User::query()
            ->role(['pengurus', 'super_admin'])
            ->when($complaint->rt_id, fn ($query) => $query->where('rt_id', $complaint->rt_id))
            ->get();

        if ($managers->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Laporan warga baru')
            ->body("[{$complaint->category->getLabel()}] {$complaint->title}")
            ->icon('heroicon-o-megaphone')
            ->warning()
            ->sendToDatabase($managers);
    }
}
