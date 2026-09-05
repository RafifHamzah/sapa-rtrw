<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasLabel, HasColor
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Lunas',
            self::Failed => 'Gagal',
            self::Expired => 'Kedaluwarsa',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Expired => 'gray',
            self::Cancelled => 'gray',
        };
    }

    /**
     * Petakan transaction_status dari Midtrans ke status internal.
     * Referensi: https://docs.midtrans.com/docs/https-notification-webhooks
     */
    public static function fromMidtrans(string $transactionStatus, ?string $fraudStatus = null): self
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge' ? self::Pending : self::Paid,
            'settlement' => self::Paid,
            'pending' => self::Pending,
            'deny', 'failure' => self::Failed,
            'expire' => self::Expired,
            'cancel', 'refund', 'partial_refund', 'chargeback' => self::Cancelled,
            default => self::Pending,
        };
    }
}
