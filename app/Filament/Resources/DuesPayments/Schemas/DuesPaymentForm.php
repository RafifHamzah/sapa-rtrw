<?php

namespace App\Filament\Resources\DuesPayments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Dues;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class DuesPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('dues_id')
                    ->label('Tagihan')
                    ->relationship('dues', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Dues $record): string => sprintf(
                        'KK %s — %02d/%d (Rp %s)',
                        $record->family?->kk_number ?? '-',
                        $record->period_month,
                        $record->period_year,
                        number_format($record->amount, 0, ',', '.'),
                    ))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('amount')
                    ->label('Nominal')
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                Select::make('payment_method')
                    ->label('Metode')
                    ->options(PaymentMethod::class)
                    ->default(PaymentMethod::Cash)
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options(PaymentStatus::class)
                    ->default(PaymentStatus::Paid)
                    ->helperText('Setel "Lunas" untuk otomatis mencatat kas & menandai tagihan lunas.')
                    ->required(),
                DateTimePicker::make('paid_at')
                    ->label('Waktu Bayar')
                    ->default(now()),
                TextInput::make('note')
                    ->label('Catatan')
                    ->maxLength(255),
                TextInput::make('midtrans_order_id')
                    ->label('Midtrans Order ID')
                    ->disabled()
                    ->visible(fn ($record): bool => filled($record?->midtrans_order_id))
                    ->columnSpanFull(),
                TextInput::make('midtrans_status')
                    ->label('Midtrans Status')
                    ->disabled()
                    ->visible(fn ($record): bool => filled($record?->midtrans_status)),
                Hidden::make('recorded_by')
                    ->default(fn (): ?int => Auth::id()),
            ]);
    }
}
