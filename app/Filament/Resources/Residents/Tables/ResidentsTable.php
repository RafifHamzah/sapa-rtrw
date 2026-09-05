<?php

namespace App\Filament\Resources\Residents\Tables;

use App\Enums\Gender;
use App\Enums\ResidentRelationship;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ResidentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nik')
                    ->label('NIK')
                    // NIK terenkripsi → tak bisa dicari di DB; tampilkan tersamar.
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? str_repeat('•', 12) . substr($state, -4)
                        : '—')
                    ->toggleable(),
                TextColumn::make('family.kk_number')
                    ->label('No. KK')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('gender')
                    ->label('L/P')
                    ->badge(),
                TextColumn::make('relationship')
                    ->label('Status Keluarga')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('birth_date')
                    ->label('Umur')
                    ->formatStateUsing(fn ($state): string => $state ? $state->age . ' th' : '—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('occupation')
                    ->label('Pekerjaan')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('full_name')
            ->filters([
                SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options(Gender::class),
                SelectFilter::make('relationship')
                    ->label('Status Keluarga')
                    ->options(ResidentRelationship::class),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
