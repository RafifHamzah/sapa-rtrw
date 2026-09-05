<?php

namespace App\Filament\Resources\Users\Concerns;

use App\Models\Resident;
use App\Models\User;

/**
 * Menyinkronkan tautan akun ke data warga. Karena relasinya hasOne
 * (User->Resident, FK residents.user_id), field 'resident_id' di form tidak
 * bisa disimpan langsung ke tabel users — jadi disinkronkan manual di sisi
 * residents setelah user tersimpan.
 */
trait SyncsResidentLink
{
    protected function syncResidentLink(User $user): void
    {
        $residentId = $this->data['resident_id'] ?? null;

        // Lepas tautan resident lama milik user ini bila pilihannya berubah.
        Resident::query()
            ->where('user_id', $user->id)
            ->when($residentId, fn ($query) => $query->whereKeyNot($residentId))
            ->update(['user_id' => null]);

        if ($residentId) {
            Resident::whereKey($residentId)->update(['user_id' => $user->id]);
        }
    }
}
