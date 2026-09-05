<?php

namespace App\Filament\Resources\Families\Schemas;

use App\Models\Family;
use App\Models\Rt;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class FamilyForm
{
    public const RT_STATUSES = [
        'Tetap' => 'Tetap',
        'Kontrak' => 'Kontrak',
        'Kos' => 'Kos',
        'Lainnya' => 'Lainnya',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kartu Keluarga')
                    ->columns(2)
                    ->schema([
                        TextInput::make('kk_number')
                            ->label('Nomor KK')
                            ->required()
                            ->maxLength(16)
                            // Bukan ->numeric(): angka 16 digit akan rusak jadi float.
                            ->rule('digits:16')
                            ->unique(ignoreRecord: true),
                        Select::make('rt_id')
                            ->label('RT')
                            ->relationship('rt', 'number')
                            ->default(fn (): ?int => Rt::query()->value('id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('house_number')
                            ->label('Nomor Rumah')
                            ->maxLength(50),
                        Select::make('rt_status')
                            ->label('Status Tinggal')
                            ->options(self::RT_STATUSES),
                        Select::make('head_resident_id')
                            ->label('Kepala Keluarga')
                            ->relationship(
                                'headResident',
                                'full_name',
                                modifyQueryUsing: fn (Builder $query, ?Family $record) => $record
                                    ? $query->where('family_id', $record->id)
                                    : $query,
                            )
                            ->searchable()
                            ->preload()
                            ->hiddenOn('create')
                            ->helperText('Pilih dari anggota keluarga yang sudah didaftarkan di bawah.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
