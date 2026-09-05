<?php

namespace App\Filament\Resources\Announcements\Tables;

use App\Enums\AnnouncementCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_pinned')
                    ->label('Pin')
                    ->boolean()
                    ->trueIcon('heroicon-s-bookmark')
                    ->falseIcon('heroicon-o-bookmark'),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge(),
                TextColumn::make('published_at')
                    ->label('Tayang')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Draf')
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('Penulis')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(AnnouncementCategory::class),
                TernaryFilter::make('is_pinned')
                    ->label('Disematkan'),
                Filter::make('published')
                    ->label('Sudah tayang')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('published_at')->where('published_at', '<=', now())),
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
