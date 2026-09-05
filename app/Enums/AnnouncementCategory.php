<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AnnouncementCategory: string implements HasLabel, HasColor
{
    case General = 'general';
    case Event = 'event';
    case Urgent = 'urgent';
    case Maintenance = 'maintenance';
    case Financial = 'financial';

    public function getLabel(): string
    {
        return match ($this) {
            self::General => 'Umum',
            self::Event => 'Kegiatan',
            self::Urgent => 'Mendesak',
            self::Maintenance => 'Pemeliharaan',
            self::Financial => 'Keuangan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::General => 'gray',
            self::Event => 'info',
            self::Urgent => 'danger',
            self::Maintenance => 'warning',
            self::Financial => 'success',
        };
    }
}
