<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Concerns\SyncsResidentLink;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use SyncsResidentLink;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Isi field 'resident_id' dari tautan resident yang ada saat ini.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['resident_id'] = $this->record->resident?->id;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncResidentLink($this->record);
    }
}
