<?php

namespace App\Filament\Resources\LetterRequests\Pages;

use App\Filament\Resources\LetterRequests\LetterRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLetterRequest extends EditRecord
{
    protected static string $resource = LetterRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
