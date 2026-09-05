<?php

namespace App\Filament\Resources\DuesPayments\Pages;

use App\Filament\Resources\DuesPayments\DuesPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDuesPayments extends ListRecords
{
    protected static string $resource = DuesPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
