<?php

namespace App\Notifications;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke warga pelapor saat status laporannya berubah (ditangani/selesai).
 * Channel database → dibaca lonceng aplikasi warga.
 */
class ComplaintStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public Complaint $complaint, public ?string $note = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $judul = $this->complaint->title;

        $preset = match ($this->complaint->status) {
            ComplaintStatus::InProgress => [
                'title' => 'Laporanmu sedang ditangani 🛠️',
                'color' => 'warning',
            ],
            ComplaintStatus::Resolved => [
                'title' => 'Laporanmu sudah selesai ✅',
                'color' => 'success',
            ],
            ComplaintStatus::Closed => [
                'title' => 'Laporanmu ditutup',
                'color' => 'gray',
            ],
            ComplaintStatus::Rejected => [
                'title' => 'Laporanmu tidak dapat diproses',
                'color' => 'danger',
            ],
            default => [
                'title' => 'Status laporan diperbarui',
                'color' => 'info',
            ],
        };

        $body = "\"{$judul}\" kini berstatus {$this->complaint->status->getLabel()}.";
        if ($this->note) {
            $body .= " Catatan: {$this->note}";
        }

        return [
            'title' => $preset['title'],
            'body' => $body,
            'icon' => 'megaphone',
            'color' => $preset['color'],
            'url' => route('complaints.show', $this->complaint),
        ];
    }
}
