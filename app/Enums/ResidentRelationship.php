<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ResidentRelationship: string implements HasLabel
{
    case Head = 'head';
    case Spouse = 'spouse';
    case Child = 'child';
    case Parent = 'parent';
    case Sibling = 'sibling';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Head => 'Kepala Keluarga',
            self::Spouse => 'Istri/Suami',
            self::Child => 'Anak',
            self::Parent => 'Orang Tua',
            self::Sibling => 'Saudara',
            self::Other => 'Lainnya',
        };
    }
}
