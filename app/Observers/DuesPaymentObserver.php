<?php

namespace App\Observers;

use App\Enums\DuesStatus;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\DuesPayment;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Services\GamificationService;
use Illuminate\Support\Facades\DB;

class DuesPaymentObserver
{
    /**
     * Dijalankan setelah pembayaran dibuat/diperbarui. Ketika sebuah pembayaran
     * menjadi LUNAS, secara otomatis:
     *   1. Menandai tagihan (dues) sebagai paid.
     *   2. Mencatat satu baris transaksi kas (income) atas pembayaran itu.
     *
     * Idempoten: aman dipanggil berkali-kali (mis. webhook Midtrans diulang) —
     * transaksi kas hanya dibuat sekali per pembayaran.
     */
    public function saved(DuesPayment $payment): void
    {
        $becamePaid = $payment->status === PaymentStatus::Paid
            && ($payment->wasRecentlyCreated || $payment->wasChanged('status'));

        if (! $becamePaid) {
            return;
        }

        $this->settle($payment);
    }

    private function settle(DuesPayment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            // Kunci baris pembayaran untuk mencegah balapan antar webhook.
            $payment = DuesPayment::whereKey($payment->getKey())->lockForUpdate()->first();

            if (! $payment || $payment->status !== PaymentStatus::Paid) {
                return;
            }

            // Guard idempotensi: satu pembayaran hanya menghasilkan satu transaksi
            // kas (withTrashed agar entri yang di-soft-delete tidak memicu dobel).
            $alreadyPosted = Transaction::withTrashed()
                ->where('dues_payment_id', $payment->id)
                ->exists();

            if ($alreadyPosted) {
                // Tetap pastikan tagihan tertandai lunas & paid_at terisi.
                $this->finalizeDues($payment);

                return;
            }

            $dues = $payment->dues()->firstOrFail();

            $category = $this->resolveDuesIncomeCategory($dues->rt_id);

            Transaction::create([
                'rt_id' => $dues->rt_id,
                'transaction_category_id' => $category->id,
                'type' => TransactionType::Income,
                'amount' => $payment->amount,
                'description' => sprintf(
                    'Iuran KK #%d periode %02d/%d',
                    $dues->family_id,
                    $dues->period_month,
                    $dues->period_year,
                ),
                'transaction_date' => ($payment->paid_at ?? now())->toDateString(),
                'dues_payment_id' => $payment->id,
                'created_by' => null, // dicatat oleh sistem
            ]);

            $this->finalizeDues($payment);
        });
    }

    private function finalizeDues(DuesPayment $payment): void
    {
        // paid_at diisi tanpa memicu observer lagi (updateQuietly).
        if ($payment->paid_at === null) {
            $payment->updateQuietly(['paid_at' => now()]);
        }

        $payment->dues()->update(['status' => DuesStatus::Paid]);

        // Gamifikasi: beri XP ke akun warga pemilik tagihan (idempoten).
        $warga = $payment->dues?->family?->headResident?->user;
        if ($warga) {
            app(GamificationService::class)
                ->recordActivity($warga, 50, 'Bayar iuran', 'dues_payment:' . $payment->id);
        }
    }

    /**
     * Kategori income untuk iuran. Dibuat otomatis bila belum ada agar
     * pencatatan kas tidak pernah gagal.
     */
    private function resolveDuesIncomeCategory(int $rtId): TransactionCategory
    {
        return TransactionCategory::firstOrCreate(
            [
                'rt_id' => $rtId,
                'type' => TransactionType::Income,
                'name' => 'Iuran Bulanan',
            ],
            [
                'description' => 'Pemasukan dari iuran warga',
            ],
        );
    }
}
