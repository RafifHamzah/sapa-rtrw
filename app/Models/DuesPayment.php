<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Observers\DuesPaymentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(DuesPaymentObserver::class)]
class DuesPayment extends Model
{
    /** @use HasFactory<\Database\Factories\DuesPaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'dues_id',
        'amount',
        'payment_method',
        'status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_status',
        'paid_at',
        'recorded_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payment_method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }

    public function dues(): BelongsTo
    {
        return $this->belongsTo(Dues::class);
    }

    /**
     * Transaksi kas (income) yang dihasilkan dari pembayaran ini. Tautannya ada
     * di sisi transactions (kolom dues_payment_id), sehingga relasinya hasOne.
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
