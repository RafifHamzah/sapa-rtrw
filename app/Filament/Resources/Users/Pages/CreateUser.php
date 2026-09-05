<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Concerns\SyncsResidentLink;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use SyncsResidentLink;

    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->syncResidentLink($this->record);
    }
}
