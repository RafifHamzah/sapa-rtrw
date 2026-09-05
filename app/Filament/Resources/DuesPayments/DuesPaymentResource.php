<?php

namespace App\Filament\Resources\DuesPayments;

use App\Filament\Resources\DuesPayments\Pages\CreateDuesPayment;
use App\Filament\Resources\DuesPayments\Pages\EditDuesPayment;
use App\Filament\Resources\DuesPayments\Pages\ListDuesPayments;
use App\Filament\Resources\DuesPayments\Schemas\DuesPaymentForm;
use App\Filament\Resources\DuesPayments\Tables\DuesPaymentsTable;
use App\Models\DuesPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DuesPaymentResource extends Resource
{
    protected static ?string $model = DuesPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 13;

    protected static ?string $navigationLabel = 'Pembayaran Iuran';

    protected static ?string $modelLabel = 'Pembayaran Iuran';

    protected static ?string $pluralModelLabel = 'Pembayaran Iuran';

    public static function form(Schema $schema): Schema
    {
        return DuesPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DuesPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDuesPayments::route('/'),
            'create' => CreateDuesPayment::route('/create'),
            'edit' => EditDuesPayment::route('/{record}/edit'),
        ];
    }
}
