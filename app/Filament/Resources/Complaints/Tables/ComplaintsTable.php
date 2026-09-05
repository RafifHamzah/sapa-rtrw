<?php

namespace App\Filament\Resources\Complaints\Tables;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Services\ComplaintService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ComplaintsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(30)
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->limit(20)
                    ->toggleable(),
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->toggleable(),
                TextColumn::make('handler.name')
                    ->label('Penangan')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Dilaporkan')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ComplaintStatus::class),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(ComplaintCategory::class),
            ])
            ->recordActions([
                self::updateStatusAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Ubah status laporan + catat ke timeline (via ComplaintService).
     */
    private static function updateStatusAction(): Action
    {
        return Action::make('updateStatus')
            ->label('Perbarui Status')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('primary')
            ->fillForm(fn (Complaint $record): array => ['status' => $record->status->value])
            ->schema([
                Select::make('status')
                    ->label('Status Baru')
                    ->options(ComplaintStatus::class)
                    ->required(),
                Textarea::make('note')
                    ->label('Catatan Penanganan')
                    ->maxLength(1000),
                Textarea::make('response')
                    ->label('Tanggapan untuk Warga (opsional)')
                    ->maxLength(1000),
            ])
            ->action(function (Complaint $record, array $data): void {
                // Filament bisa mengembalikan enum atau string tergantung konteks.
                $status = $data['status'] instanceof ComplaintStatus
                    ? $data['status']
                    : ComplaintStatus::from($data['status']);

                app(ComplaintService::class)->changeStatus(
                    complaint: $record,
                    status: $status,
                    handler: Auth::user(),
                    note: $data['note'] ?? null,
                    response: $data['response'] ?? null,
                );

                Notification::make()
                    ->title('Status laporan diperbarui')
                    ->success()
                    ->send();
            });
    }
}
