<?php

namespace App\Filament\Resources\DuesPayments\Pages;

use App\Filament\Resources\DuesPayments\DuesPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDuesPayment extends EditRecord
{
    protected static string $resource = DuesPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
