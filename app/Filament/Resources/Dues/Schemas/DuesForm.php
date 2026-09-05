<?php

namespace App\Filament\Resources\Dues\Schemas;

use App\Enums\DuesStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class DuesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('rt_id')
                    ->label('RT')
                    ->relationship('rt', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('family_id')
                    ->label('Keluarga (KK)')
                    ->relationship('family', 'kk_number')
                    ->searchable()
                    ->preload()
                    ->required()
                    // Cegah tagihan ganda untuk satu keluarga pada periode yang sama
                    // (mengikuti unique index dues: family_id + period_month + period_year).
                    ->unique(
                        table: 'dues',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                            ->where('period_month', $get('period_month'))
                            ->where('period_year', $get('period_year')),
                    )
                    ->validationMessages([
                        'unique' => 'Tagihan iuran untuk keluarga ini pada periode tersebut sudah ada.',
                    ]),
                Select::make('period_month')
                    ->label('Bulan')
                    ->options(self::monthOptions())
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
                    ->label('Nominal')
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options(DuesStatus::class)
                    ->default(DuesStatus::Unpaid)
                    ->required(),
                DatePicker::make('due_date')
                    ->label('Jatuh Tempo'),
            ]);
    }

    /**
     * @return array<int, string>
     */
    public static function monthOptions(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }
}
