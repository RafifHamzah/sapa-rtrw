<?php

namespace App\Filament\Resources\LetterRequests;

use App\Filament\Resources\LetterRequests\Pages\CreateLetterRequest;
use App\Filament\Resources\LetterRequests\Pages\EditLetterRequest;
use App\Filament\Resources\LetterRequests\Pages\ListLetterRequests;
use App\Filament\Resources\LetterRequests\Schemas\LetterRequestForm;
use App\Filament\Resources\LetterRequests\Tables\LetterRequestsTable;
use App\Models\LetterRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LetterRequestResource extends Resource
{
    protected static ?string $model = LetterRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    protected static ?int $navigationSort = 21;

    protected static ?string $recordTitleAttribute = 'letter_number';

    protected static ?string $navigationLabel = 'Permohonan Surat';

    protected static ?string $modelLabel = 'Permohonan Surat';

    protected static ?string $pluralModelLabel = 'Permohonan Surat';

    public static function getNavigationBadge(): ?string
    {
        // Tampilkan jumlah permohonan yang menunggu diproses.
        $count = static::getModel()::where('status', \App\Enums\LetterStatus::Pending)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return LetterRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LetterRequestsTable::configure($table);
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
            'index' => ListLetterRequests::route('/'),
            'create' => CreateLetterRequest::route('/create'),
            'edit' => EditLetterRequest::route('/{record}/edit'),
        ];
    }
}
