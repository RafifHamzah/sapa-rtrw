<?php

namespace App\Filament\Resources\Dues;

use App\Filament\Resources\Dues\Pages\CreateDues;
use App\Filament\Resources\Dues\Pages\EditDues;
use App\Filament\Resources\Dues\Pages\ListDues;
use App\Filament\Resources\Dues\Schemas\DuesForm;
use App\Filament\Resources\Dues\Tables\DuesTable;
use App\Models\Dues;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DuesResource extends Resource
{
    protected static ?string $model = Dues::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 12;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Iuran (Tagihan)';

    protected static ?string $modelLabel = 'Tagihan Iuran';

    protected static ?string $pluralModelLabel = 'Tagihan Iuran';

    public static function form(Schema $schema): Schema
    {
        return DuesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DuesTable::configure($table);
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
            'index' => ListDues::route('/'),
            'create' => CreateDues::route('/create'),
            'edit' => EditDues::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
