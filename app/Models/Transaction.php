<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rt_id',
        'transaction_category_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'receipt_path',
        'created_by',
        'dues_payment_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'integer',
            'transaction_date' => 'date',
        ];
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'transaction_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Pembayaran iuran sumber dari transaksi kas ini (jika transaksi berasal
     * dari pembayaran iuran otomatis).
     */
    public function duesPayment(): BelongsTo
    {
        return $this->belongsTo(DuesPayment::class);
    }
}
