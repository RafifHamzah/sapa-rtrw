<?php

namespace App\Filament\Resources\LetterTypes\Pages;

use App\Filament\Resources\LetterTypes\LetterTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLetterType extends CreateRecord
{
    protected static string $resource = LetterTypeResource::class;
}
