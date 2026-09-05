<?php

namespace App\Filament\Resources\Complaints\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'updates';

    protected static ?string $title = 'Riwayat Penanganan';

    public function table(Table $table): Table
    {
        // Read-only: entri timeline dibuat otomatis oleh aksi "Perbarui Status".
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('author.name')
                    ->label('Oleh')
                    ->placeholder('Sistem'),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
