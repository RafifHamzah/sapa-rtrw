<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Dues;
use App\Models\DuesPayment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans) {}

    /**
     * Mulai pembayaran online atas sebuah tagihan iuran.
     * Membuat DuesPayment (pending) lalu mengembalikan Snap token.
     */
    public function pay(Request $request, Dues $dues): JsonResponse
    {
        $user = $request->user();

        // Warga hanya boleh membayar tagihan keluarganya sendiri.
        if ($user->hasRole('warga')) {
            $familyId = $user->resident?->family_id;
            abort_unless($familyId !== null && $dues->family_id === $familyId, 403);
        }

        if ($dues->status === \App\Enums\DuesStatus::Paid) {
            return response()->json(['message' => 'Tagihan ini sudah lunas.'], 422);
        }

        // Pakai kembali pembayaran pending yang belum kedaluwarsa bila ada,
        // supaya tidak menumpuk baris pembayaran untuk satu tagihan.
        $payment = $dues->payments()
            ->where('status', PaymentStatus::Pending)
            ->latest()
            ->first();

        if (! $payment) {
            $payment = $dues->payments()->create([
                'amount' => $dues->amount,
                'payment_method' => PaymentMethod::Online,
                'status' => PaymentStatus::Pending,
                'recorded_by' => $user->id,
            ]);
        }

        // Selalu pakai order_id BARU tiap percobaan bayar: Midtrans menolak
        // pembuatan Snap token untuk order_id yang sudah pernah dipakai.
        $payment->update([
            'midtrans_order_id' => 'DUES-' . $dues->id . '-' . $payment->id . '-' . Str::upper(Str::random(6)),
        ]);

        try {
            $snapToken = $this->midtrans->createSnapToken($payment);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Gagal membuat transaksi Midtrans.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'snap_token' => $snapToken,
            'order_id' => $payment->midtrans_order_id,
            'client_key' => config('services.midtrans.client_key'),
        ]);
    }

    /**
     * Webhook notifikasi Midtrans. Route ini dikecualikan dari CSRF.
     * Signature diverifikasi di dalam service sebelum diproses.
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            $this->midtrans->handleNotification($request->all());
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json(['message' => 'OK']);
    }
}
