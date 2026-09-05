<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ComplaintCategory: string implements HasLabel
{
    case Infrastructure = 'infrastructure';
    case Security = 'security';
    case Environment = 'environment';
    case Social = 'social';
    case Administration = 'administration';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Infrastructure => 'Infrastruktur',
            self::Security => 'Keamanan',
            self::Environment => 'Lingkungan',
            self::Social => 'Sosial',
            self::Administration => 'Administrasi',
            self::Other => 'Lainnya',
        };
    }
}
