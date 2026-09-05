<?php

namespace Tests\Feature;

use App\Enums\DuesStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Dues;
use App\Models\DuesPayment;
use App\Models\Family;
use App\Models\Rt;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuesPaymentSettlementTest extends TestCase
{
    use RefreshDatabase;

    private function makeDues(int $amount = 50000): Dues
    {
        $rt = Rt::create([
            'number' => '001', 'rw_number' => '001', 'village' => 'X',
            'district' => 'Y', 'city' => 'Z', 'province' => 'P',
        ]);
        $family = Family::create([
            'rt_id' => $rt->id,
            'kk_number' => '3201000000000001',
            'address' => 'Jl. Test',
        ]);

        return $family->dues()->create([
            'rt_id' => $rt->id,
            'period_month' => 8,
            'period_year' => 2026,
            'amount' => $amount,
            'status' => DuesStatus::Unpaid,
        ]);
    }

    public function test_paid_payment_settles_dues_and_posts_cash_entry(): void
    {
        $dues = $this->makeDues(50000);

        $payment = $dues->payments()->create([
            'amount' => 50000,
            'payment_method' => PaymentMethod::Cash,
            'status' => PaymentStatus::Paid,
        ]);

        $dues->refresh();

        $this->assertSame(DuesStatus::Paid, $dues->status);

        $transaction = Transaction::where('dues_payment_id', $payment->id)->first();
        $this->assertNotNull($transaction);
        $this->assertSame(TransactionType::Income, $transaction->type);
        $this->assertSame(50000, $transaction->amount);
        $this->assertNull($transaction->created_by);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_pending_payment_does_not_post_cash_entry(): void
    {
        $dues = $this->makeDues();

        $payment = $dues->payments()->create([
            'amount' => 50000,
            'payment_method' => PaymentMethod::Online,
            'status' => PaymentStatus::Pending,
        ]);

        $this->assertSame(DuesStatus::Unpaid, $dues->fresh()->status);
        $this->assertSame(0, Transaction::where('dues_payment_id', $payment->id)->count());
    }

    public function test_settlement_is_idempotent_across_repeated_saves(): void
    {
        $dues = $this->makeDues();

        $payment = $dues->payments()->create([
            'amount' => 50000,
            'payment_method' => PaymentMethod::Online,
            'status' => PaymentStatus::Pending,
        ]);

        // Pertama kali jadi paid → posting kas.
        $payment->update(['status' => PaymentStatus::Paid]);
        // Simulasi webhook diulang beberapa kali.
        $payment->update(['midtrans_status' => 'settlement']);
        $payment->update(['status' => PaymentStatus::Paid]);
        $payment->save();

        $this->assertSame(1, Transaction::where('dues_payment_id', $payment->id)->count());
    }
}
