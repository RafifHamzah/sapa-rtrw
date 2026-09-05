<?php

namespace App\Filament\Resources\LetterRequests\Tables;

use App\Enums\LetterStatus;
use App\Models\LetterRequest;
use App\Services\LetterService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LetterRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('letter_number')
                    ->label('Nomor')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('letterType.name')
                    ->label('Jenis')
                    ->searchable(),
                TextColumn::make('resident.full_name')
                    ->label('Pemohon')
                    ->searchable(),
                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('processed_at')
                    ->label('Diproses')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(LetterStatus::class),
                SelectFilter::make('letter_type_id')
                    ->label('Jenis')
                    ->relationship('letterType', 'name'),
            ])
            ->recordActions([
                self::approveAction(),
                self::rejectAction(),
                self::downloadAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Setujui')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (LetterRequest $record): bool => $record->status === LetterStatus::Pending)
            ->requiresConfirmation()
            ->modalHeading('Setujui Permohonan Surat')
            ->modalDescription('Nomor surat & PDF ber-QR akan dibuat otomatis.')
            ->action(function (LetterRequest $record): void {
                app(LetterService::class)->approve($record, Auth::user());

                Notification::make()
                    ->title('Surat disetujui')
                    ->body('Nomor: ' . $record->fresh()->letter_number)
                    ->success()
                    ->send();
            });
    }

    private static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Tolak')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (LetterRequest $record): bool => $record->status === LetterStatus::Pending)
            ->schema([
                Textarea::make('reason')
                    ->label('Alasan Penolakan')
                    ->required()
                    ->maxLength(1000),
            ])
            ->action(function (LetterRequest $record, array $data): void {
                app(LetterService::class)->reject($record, Auth::user(), $data['reason']);

                Notification::make()
                    ->title('Permohonan ditolak')
                    ->success()
                    ->send();
            });
    }

    private static function downloadAction(): Action
    {
        return Action::make('download')
            ->label('Unduh PDF')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->visible(fn (LetterRequest $record): bool => $record->isApproved()
                && $record->pdf_path !== null
                && Storage::disk('public')->exists($record->pdf_path))
            ->url(fn (LetterRequest $record): string => Storage::disk('public')->url($record->pdf_path))
            ->openUrlInNewTab();
    }
}
