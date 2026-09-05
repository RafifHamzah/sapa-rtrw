<?php

namespace App\Filament\Resources\Families\RelationManagers;

use App\Filament\Resources\Residents\Schemas\ResidentForm;
use App\Filament\Resources\Residents\Tables\ResidentsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ResidentsRelationManager extends RelationManager
{
    protected static string $relationship = 'residents';

    protected static ?string $title = 'Anggota Keluarga';

    protected static ?string $recordTitleAttribute = 'full_name';

    public function form(Schema $schema): Schema
    {
        // KK sudah ditentukan oleh induk → tanpa pemilih Kartu Keluarga.
        return ResidentForm::configure($schema, withFamily: false);
    }

    public function table(Table $table): Table
    {
        return ResidentsTable::configure($table)
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ]);
    }
}
