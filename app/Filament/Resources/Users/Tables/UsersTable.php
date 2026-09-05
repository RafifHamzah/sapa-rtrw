<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserStatus;
use App\Models\User;
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

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(','),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('resident.full_name')
                    ->label('Data Warga')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('xp')
                    ->label('XP')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (int $state): string => 'Lv ' . (intdiv($state, 100) + 1) . ' · ' . number_format($state, 0, ',', '.') . ' XP')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('verified_at')
                    ->label('Diverifikasi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(UserStatus::class),
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                self::verifyAction(),
                self::rejectAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Verifikasi warga: pending -> active (terverifikasi).
     */
    private static function verifyAction(): Action
    {
        return Action::make('verify')
            ->label('Verifikasi')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (User $record): bool => $record->isPending())
            ->requiresConfirmation()
            ->modalHeading('Verifikasi Warga')
            ->modalDescription(fn (User $record): string => "Verifikasi akun {$record->name}?")
            ->action(function (User $record): void {
                $record->update([
                    'status' => UserStatus::Active,
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                    'rejection_reason' => null,
                ]);

                Notification::make()
                    ->title('Warga berhasil diverifikasi')
                    ->success()
                    ->send();
            });
    }

    /**
     * Tolak pendaftaran warga: pending -> rejected, wajib menyertakan alasan.
     */
    private static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Tolak')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (User $record): bool => $record->isPending())
            ->schema([
                Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->required()
                    ->maxLength(1000),
            ])
            ->action(function (User $record, array $data): void {
                $record->update([
                    'status' => UserStatus::Rejected,
                    'rejection_reason' => $data['rejection_reason'],
                    'verified_at' => null,
                    'verified_by' => Auth::id(),
                ]);

                Notification::make()
                    ->title('Pendaftaran warga ditolak')
                    ->success()
                    ->send();
            });
    }
}
