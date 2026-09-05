<?php

namespace App\Services;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ComplaintService
{
    /**
     * Ubah status laporan + catat ke timeline penanganan (complaint_updates).
     * Mengisi handled_by saat mulai ditangani dan resolved_at saat selesai.
     */
    public function changeStatus(
        Complaint $complaint,
        ComplaintStatus $status,
        ?User $handler = null,
        ?string $note = null,
        ?string $response = null,
    ): Complaint {
        return DB::transaction(function () use ($complaint, $status, $handler, $note, $response): Complaint {
            $attributes = ['status' => $status];

            if ($handler !== null) {
                $attributes['handled_by'] = $handler->id;
            }

            if ($response !== null) {
                $attributes['response'] = $response;
            }

            // Set/hapus resolved_at sesuai status akhir.
            if (in_array($status, [ComplaintStatus::Resolved, ComplaintStatus::Closed], true)) {
                $attributes['resolved_at'] = $complaint->resolved_at ?? now();
            } elseif ($status === ComplaintStatus::InProgress) {
                $attributes['resolved_at'] = null;
            }

            $complaint->update($attributes);

            $complaint->updates()->create([
                'user_id' => $handler?->id,
                'status' => $status,
                'note' => $note,
            ]);

            return $complaint;
        });
    }
}
