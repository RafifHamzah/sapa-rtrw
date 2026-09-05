<?php

namespace App\Filament\Resources\Families\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FamiliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kk_number')
                    ->label('Nomor KK')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('headResident.full_name')
                    ->label('Kepala Keluarga')
                    ->searchable()
                    ->placeholder('— belum ditentukan —'),
                TextColumn::make('residents_count')
                    ->label('Jumlah Anggota')
                    ->counts('residents')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('house_number')
                    ->label('No. Rumah')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('rt_status')
                    ->label('Status Tinggal')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('kk_number')
            ->filters([
                SelectFilter::make('rt_status')
                    ->label('Status Tinggal')
                    ->options([
                        'Tetap' => 'Tetap',
                        'Kontrak' => 'Kontrak',
                        'Kos' => 'Kos',
                        'Lainnya' => 'Lainnya',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
