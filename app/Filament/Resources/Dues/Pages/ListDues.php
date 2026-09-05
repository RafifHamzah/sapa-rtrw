<?php

namespace App\Filament\Resources\Dues\Pages;

use App\Filament\Resources\Dues\DuesResource;
use App\Filament\Resources\Dues\Schemas\DuesForm;
use App\Services\DuesGenerator;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListDues extends ListRecords
{
    protected static string $resource = DuesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->generateBulkDuesAction(),
            CreateAction::make(),
        ];
    }

    /**
     * Generate tagihan iuran massal untuk semua KK dalam satu RT & periode.
     */
    private function generateBulkDuesAction(): Action
    {
        return Action::make('generateBulkDues')
            ->label('Generate Tagihan Massal')
            ->icon(Heroicon::OutlinedSparkles)
            ->color('primary')
            ->modalHeading('Generate Tagihan Iuran Massal')
            ->modalDescription('Buat tagihan untuk SEMUA keluarga di RT terpilih pada periode ini. Keluarga yang sudah ditagih pada periode yang sama akan dilewati.')
            ->schema([
                Select::make('rt_id')
                    ->label('RT')
                    ->relationship('rt', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('period_month')
                    ->label('Bulan')
                    ->options(DuesForm::monthOptions())
                    ->default((int) now()->format('n'))
                    ->required(),
                TextInput::make('period_year')
                    ->label('Tahun')
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->default((int) now()->format('Y'))
                    ->required(),
                TextInput::make('amount')
                    ->label('Nominal per KK')
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                DatePicker::make('due_date')
                    ->label('Jatuh Tempo'),
            ])
            ->action(function (array $data): void {
                $result = app(DuesGenerator::class)->generate(
                    rtId: (int) $data['rt_id'],
                    month: (int) $data['period_month'],
                    year: (int) $data['period_year'],
                    amount: (int) $data['amount'],
                    dueDate: $data['due_date'] ?? null,
                );

                Notification::make()
                    ->title('Tagihan iuran dibuat')
                    ->body("{$result['created']} tagihan baru dibuat, {$result['skipped']} dilewati (sudah ada).")
                    ->success()
                    ->send();
            });
    }
}
