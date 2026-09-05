<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\DuesPayment;
use Illuminate\Support\Facades\DB;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;

class MidtransService
{
    public function __construct()
    {
        MidtransConfig::$serverKey = (string) config('services.midtrans.server_key');
        MidtransConfig::$clientKey = (string) config('services.midtrans.client_key');
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production');
        MidtransConfig::$isSanitized = (bool) config('services.midtrans.is_sanitized');
        MidtransConfig::$is3ds = (bool) config('services.midtrans.is_3ds');
    }

    /**
     * Buat Snap token untuk sebuah pembayaran iuran.
     * Pembayaran harus sudah punya midtrans_order_id.
     */
    public function createSnapToken(DuesPayment $payment): string
    {
        $payment->loadMissing('dues.family.headResident', 'dues.rt');
        $dues = $payment->dues;

        $params = [
            'transaction_details' => [
                'order_id' => $payment->midtrans_order_id,
                'gross_amount' => (int) $payment->amount,
            ],
            'item_details' => [[
                'id' => 'dues-' . $dues->id,
                'price' => (int) $payment->amount,
                'quantity' => 1,
                'name' => mb_substr(sprintf('Iuran %02d/%d', $dues->period_month, $dues->period_year), 0, 50),
            ]],
            'customer_details' => [
                'first_name' => mb_substr((string) ($dues->family->headResident?->full_name ?? 'Warga'), 0, 50),
            ],
        ];

        return Snap::getSnapToken($params);
    }

    /**
     * Verifikasi signature key dari notifikasi Midtrans.
     * signature_key = sha512(order_id + status_code + gross_amount + server_key)
     */
    public function verifySignature(array $payload): bool
    {
        $expected = hash('sha512',
            ($payload['order_id'] ?? '')
            . ($payload['status_code'] ?? '')
            . ($payload['gross_amount'] ?? '')
            . (string) config('services.midtrans.server_key')
        );

        return isset($payload['signature_key'])
            && hash_equals($expected, (string) $payload['signature_key']);
    }

    /**
     * Proses notifikasi (webhook) Midtrans. WAJIB memverifikasi signature dulu.
     * Update pembayaran dibungkus DB transaction; efek samping (tandai dues
     * lunas + catat kas) ditangani DuesPaymentObserver secara idempoten.
     *
     * @throws RuntimeException bila signature tidak valid atau order tidak ditemukan.
     */
    public function handleNotification(array $payload): DuesPayment
    {
        if (! $this->verifySignature($payload)) {
            throw new RuntimeException('Invalid Midtrans signature.');
        }

        $orderId = $payload['order_id'] ?? null;

        $payment = DuesPayment::where('midtrans_order_id', $orderId)->first();

        if (! $payment) {
            throw new RuntimeException("Payment not found for order_id [{$orderId}].");
        }

        return $this->applyStatus($payment, $payload);
    }

    /**
     * Fallback tanpa webhook: tarik status transaksi langsung dari API Midtrans
     * lalu terapkan ke pembayaran. Berguna di dev lokal (webhook tak sampai ke
     * localhost) atau untuk "cek status manual". Data dari API dipercaya, jadi
     * tidak perlu verifikasi signature.
     *
     * @throws RuntimeException bila belum ada order_id atau transaksi belum ada di Midtrans.
     */
    public function syncStatusFromGateway(DuesPayment $payment): DuesPayment
    {
        if (! $payment->midtrans_order_id) {
            throw new RuntimeException('Pembayaran belum memiliki order_id Midtrans.');
        }

        // Transaction::status() melempar exception bila order belum pernah dibayar.
        $response = Transaction::status($payment->midtrans_order_id);
        $payload = json_decode(json_encode($response), true) ?: [];

        return $this->applyStatus($payment, $payload);
    }

    /**
     * Terapkan status transaksi (dari webhook maupun API) ke pembayaran.
     * Efek samping (tandai dues lunas + catat kas) ditangani DuesPaymentObserver
     * secara idempoten, jadi aman dipanggil berkali-kali.
     */
    private function applyStatus(DuesPayment $payment, array $payload): DuesPayment
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = $payload['fraud_status'] ?? null;
        $status = PaymentStatus::fromMidtrans($transactionStatus, $fraudStatus);

        return DB::transaction(function () use ($payment, $payload, $transactionStatus, $status): DuesPayment {
            $payment->update([
                'midtrans_transaction_id' => $payload['transaction_id'] ?? $payment->midtrans_transaction_id,
                'midtrans_status' => $transactionStatus,
                'status' => $status,
                // paid_at final di-set oleh observer saat benar-benar lunas.
                'paid_at' => $status === PaymentStatus::Paid ? now() : $payment->paid_at,
            ]);

            return $payment;
        });
    }
}
