<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ComplaintStatus: string implements HasLabel, HasColor
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Baru',
            self::InProgress => 'Ditangani',
            self::Resolved => 'Selesai',
            self::Closed => 'Ditutup',
            self::Rejected => 'Ditolak',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::InProgress => 'info',
            self::Resolved => 'success',
            self::Closed => 'gray',
            self::Rejected => 'danger',
        };
    }
}
