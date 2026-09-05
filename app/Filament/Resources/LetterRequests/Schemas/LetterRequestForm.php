<?php

namespace App\Filament\Resources\LetterRequests\Schemas;

use App\Enums\LetterStatus;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LetterRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Permohonan')
                    ->columns(2)
                    ->schema([
                        Select::make('letter_type_id')
                            ->label('Jenis Surat')
                            ->relationship('letterType', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('resident_id')
                            ->label('Pemohon (Warga)')
                            ->relationship('resident', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Textarea::make('purpose')
                            ->label('Keperluan')
                            ->required()
                            ->columnSpanFull(),
                        KeyValue::make('form_data')
                            ->label('Data Tambahan')
                            ->columnSpanFull(),
                    ]),

                Section::make('Status & Hasil')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(LetterStatus::class)
                            ->required(),
                        TextInput::make('letter_number')
                            ->label('Nomor Surat')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->label('Catatan / Alasan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
