<?php

namespace Tests\Feature;

use App\Enums\DuesStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserStatus;
use App\Models\Dues;
use App\Models\DuesPayment;
use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MidtransService;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'SB-Mid-server-TESTKEY';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.midtrans.server_key' => self::SERVER_KEY]);
    }

    private function makeDues(int $amount = 50000): Dues
    {
        $rt = Rt::create([
            'number' => '001', 'rw_number' => '001', 'village' => 'X',
            'district' => 'Y', 'city' => 'Z', 'province' => 'P',
        ]);
        $family = Family::create([
            'rt_id' => $rt->id, 'kk_number' => '3201000000000001', 'address' => 'Jl. Test',
        ]);

        return $family->dues()->create([
            'rt_id' => $rt->id, 'period_month' => 8, 'period_year' => 2026,
            'amount' => $amount, 'status' => DuesStatus::Unpaid,
        ]);
    }

    private function pendingPayment(Dues $dues, string $orderId): DuesPayment
    {
        return $dues->payments()->create([
            'amount' => $dues->amount,
            'payment_method' => PaymentMethod::Online,
            'status' => PaymentStatus::Pending,
            'midtrans_order_id' => $orderId,
        ]);
    }

    private function signedPayload(string $orderId, string $grossAmount, string $transactionStatus): array
    {
        $statusCode = '200';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . self::SERVER_KEY);

        return [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => $transactionStatus,
            'transaction_id' => 'midtrans-txn-123',
            'signature_key' => $signature,
        ];
    }

    public function test_settlement_webhook_marks_paid_and_posts_cash_once(): void
    {
        $dues = $this->makeDues(50000);
        $payment = $this->pendingPayment($dues, 'DUES-1-1-ABC123');

        $payload = $this->signedPayload('DUES-1-1-ABC123', '50000.00', 'settlement');

        $this->postJson('/midtrans/callback', $payload)->assertOk();

        $payment->refresh();
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame('settlement', $payment->midtrans_status);
        $this->assertSame('midtrans-txn-123', $payment->midtrans_transaction_id);
        $this->assertSame(DuesStatus::Paid, $dues->fresh()->status);
        $this->assertSame(1, Transaction::where('dues_payment_id', $payment->id)->count());

        // Webhook diulang → tetap 1 transaksi kas (idempoten).
        $this->postJson('/midtrans/callback', $payload)->assertOk();
        $this->assertSame(1, Transaction::where('dues_payment_id', $payment->id)->count());
    }

    public function test_invalid_signature_is_rejected_and_changes_nothing(): void
    {
        $dues = $this->makeDues();
        $payment = $this->pendingPayment($dues, 'DUES-1-1-XYZ999');

        $payload = $this->signedPayload('DUES-1-1-XYZ999', '50000.00', 'settlement');
        $payload['signature_key'] = 'tampered-signature';

        $this->postJson('/midtrans/callback', $payload)->assertStatus(403);

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame(DuesStatus::Unpaid, $dues->fresh()->status);
        $this->assertSame(0, Transaction::count());
    }

    public function test_expire_webhook_sets_expired_without_cash_entry(): void
    {
        $dues = $this->makeDues();
        $payment = $this->pendingPayment($dues, 'DUES-1-1-EXP001');

        $payload = $this->signedPayload('DUES-1-1-EXP001', '50000.00', 'expire');
        $this->postJson('/midtrans/callback', $payload)->assertOk();

        $this->assertSame(PaymentStatus::Expired, $payment->fresh()->status);
        $this->assertSame(DuesStatus::Unpaid, $dues->fresh()->status);
        $this->assertSame(0, Transaction::count());
    }

    public function test_service_rejects_invalid_signature(): void
    {
        $service = app(MidtransService::class);
        $this->assertFalse($service->verifySignature(['order_id' => 'x', 'signature_key' => 'bad']));

        $valid = $this->signedPayload('ORDER-1', '10000.00', 'settlement');
        $this->assertTrue($service->verifySignature($valid));
    }

    public function test_warga_can_start_payment_and_receive_snap_token(): void
    {
        $this->seed(ShieldSeeder::class);

        $dues = $this->makeDues(50000);
        $family = $dues->family;

        $warga = User::create([
            'rt_id' => $dues->rt_id,
            'name' => 'Warga',
            'email' => 'warga@example.com',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $warga->assignRole('warga');

        Resident::create([
            'family_id' => $family->id,
            'user_id' => $warga->id,
            'nik' => '3201000000000001',
            'full_name' => 'Warga',
            'gender' => \App\Enums\Gender::Male,
            'birth_date' => '1990-01-01',
            'relationship' => \App\Enums\ResidentRelationship::Head,
        ]);

        // Hindari memanggil API Midtrans sungguhan.
        $this->mock(MidtransService::class, function ($mock): void {
            $mock->shouldReceive('createSnapToken')->once()->andReturn('snap-token-abc');
        });

        $response = $this->actingAs($warga)->postJson("/dues/{$dues->id}/pay");

        $response->assertOk()
            ->assertJson(['snap_token' => 'snap-token-abc'])
            ->assertJsonStructure(['snap_token', 'order_id', 'client_key']);

        $payment = DuesPayment::where('dues_id', $dues->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame(PaymentMethod::Online, $payment->payment_method);
        $this->assertNotNull($payment->midtrans_order_id);
    }

    public function test_warga_cannot_pay_other_family_dues(): void
    {
        $this->seed(ShieldSeeder::class);
        $dues = $this->makeDues();

        // Warga tanpa resident/keluarga yang cocok.
        $warga = User::create([
            'name' => 'Orang Lain', 'email' => 'lain@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $warga->assignRole('warga');

        $this->actingAs($warga)->postJson("/dues/{$dues->id}/pay")->assertForbidden();
    }
}
