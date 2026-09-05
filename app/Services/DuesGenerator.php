<?php

namespace App\Services;

use App\Enums\DuesStatus;
use App\Models\Dues;
use App\Models\Family;
use Illuminate\Support\Facades\DB;

class DuesGenerator
{
    /**
     * Buat tagihan iuran untuk SEMUA keluarga di sebuah RT pada satu periode.
     * Menghormati unique (family_id, period_month, period_year) sehingga aman
     * dijalankan ulang — keluarga yang sudah ditagih pada periode itu dilewati.
     *
     * @return array{created: int, skipped: int}
     */
    public function generate(int $rtId, int $month, int $year, int $amount, ?string $dueDate = null): array
    {
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($rtId, $month, $year, $amount, $dueDate, &$created, &$skipped): void {
            $families = Family::where('rt_id', $rtId)->get();

            foreach ($families as $family) {
                // withTrashed: unique index tetap berlaku untuk baris soft-deleted.
                $exists = Dues::withTrashed()
                    ->where('family_id', $family->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                Dues::create([
                    'rt_id' => $rtId,
                    'family_id' => $family->id,
                    'period_month' => $month,
                    'period_year' => $year,
                    'amount' => $amount,
                    'status' => DuesStatus::Unpaid,
                    'due_date' => $dueDate,
                ]);

                $created++;
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }
}
