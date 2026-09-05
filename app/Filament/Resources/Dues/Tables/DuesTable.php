<?php

namespace App\Filament\Resources\Dues\Tables;

use App\Enums\DuesStatus;
use App\Filament\Resources\Dues\Schemas\DuesForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('family.kk_number')
                    ->label('No. KK')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period')
                    ->label('Periode')
                    ->state(fn ($record): string => DuesForm::monthOptions()[$record->period_month] . ' ' . $record->period_year),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', 0)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('payments_count')
                    ->label('Pembayaran')
                    ->counts('payments')
                    ->badge()
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(DuesStatus::class),
                SelectFilter::make('period_year')
                    ->label('Tahun')
                    ->options(fn (): array => \App\Models\Dues::query()
                        ->distinct()
                        ->orderByDesc('period_year')
                        ->pluck('period_year', 'period_year')
                        ->all()),
                SelectFilter::make('period_month')
                    ->label('Bulan')
                    ->options(DuesForm::monthOptions()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
