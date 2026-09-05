<?php

namespace App\Filament\Resources\DuesPayments\Tables;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DuesPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dues.family.kk_number')
                    ->label('No. KK')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', 0)
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('midtrans_status')
                    ->label('Midtrans')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('paid_at')
                    ->label('Waktu Bayar')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('transaction.id')
                    ->label('Kas')
                    ->formatStateUsing(fn ($state): string => $state ? 'Tercatat' : '—')
                    ->placeholder('—')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'gray')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PaymentStatus::class),
                SelectFilter::make('payment_method')
                    ->label('Metode')
                    ->options(PaymentMethod::class),
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
