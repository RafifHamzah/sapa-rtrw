<?php

namespace App\Notifications;

use App\Enums\LetterStatus;
use App\Models\LetterRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke warga saat status permohonan suratnya berubah (disetujui/ditolak).
 * Disimpan ke tabel `notifications` (channel database) dan dibaca oleh lonceng
 * di aplikasi warga.
 */
class LetterStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public LetterRequest $letter)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $jenis = $this->letter->letterType?->name ?? 'Surat pengantar';

        return match ($this->letter->status) {
            LetterStatus::Approved => [
                'title' => 'Surat kamu disetujui 🎉',
                'body' => "{$jenis} sudah disetujui (No. {$this->letter->letter_number}). Silakan unduh PDF-nya.",
                'icon' => 'document',
                'color' => 'success',
                'url' => route('letters.index'),
            ],
            LetterStatus::Rejected => [
                'title' => 'Permohonan surat ditolak',
                'body' => "{$jenis} belum bisa disetujui." . ($this->letter->notes ? " Alasan: {$this->letter->notes}" : ''),
                'icon' => 'document',
                'color' => 'danger',
                'url' => route('letters.index'),
            ],
            default => [
                'title' => 'Status surat diperbarui',
                'body' => "{$jenis} kini berstatus {$this->letter->status->getLabel()}.",
                'icon' => 'document',
                'color' => 'info',
                'url' => route('letters.index'),
            ],
        };
    }
}
