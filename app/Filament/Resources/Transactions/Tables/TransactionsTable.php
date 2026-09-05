<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\TransactionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', 0)
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->money('IDR', 0)),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(40)
                    ->searchable(),
                ImageColumn::make('receipt_path')
                    ->label('Nota')
                    ->disk('public')
                    ->square()
                    ->toggleable(),
                TextColumn::make('duesPayment.id')
                    ->label('Dari Iuran')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state): string => $state ? 'Ya' : '—')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(TransactionType::class),
                SelectFilter::make('transaction_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
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
