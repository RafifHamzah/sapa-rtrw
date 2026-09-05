<?php

namespace App\Filament\Resources\LetterTypes\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LetterTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Jenis Surat')
                    ->columns(2)
                    ->schema([
                        Select::make('rt_id')
                            ->label('RT')
                            ->relationship('rt', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->label('Nama Surat')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Kode')
                            ->helperText('Dipakai pada nomor surat, mis. DOM → 001/DOM/RT05/2026.')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (string $state): string => strtoupper($state)),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        TextInput::make('description')
                            ->label('Keterangan')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Template Surat')
                    ->schema([
                        Textarea::make('template_body')
                            ->label('Isi Template')
                            ->required()
                            ->rows(10)
                            ->helperText('Placeholder tersedia: {nama}, {nik}, {alamat}, {keperluan}, {tempat_lahir}, {pekerjaan}, serta {nama_field} dari field tambahan di bawah.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Field Tambahan (diisi pemohon)')
                    ->description('Field ekstra yang wajib/opsional diisi warga. Nama field menjadi placeholder di template, mis. nama_usaha → {nama_usaha}.')
                    ->schema([
                        Repeater::make('required_fields')
                            ->label('')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Field (key)')
                                    ->required()
                                    ->helperText('tanpa spasi, mis. nama_usaha')
                                    ->maxLength(50),
                                TextInput::make('label')
                                    ->label('Label')
                                    ->required()
                                    ->maxLength(100),
                                Select::make('type')
                                    ->label('Tipe')
                                    ->options([
                                        'text' => 'Teks',
                                        'textarea' => 'Teks Panjang',
                                        'number' => 'Angka',
                                        'date' => 'Tanggal',
                                    ])
                                    ->default('text')
                                    ->required(),
                                Toggle::make('required')
                                    ->label('Wajib')
                                    ->default(true),
                            ])
                            ->columns(4)
                            ->addActionLabel('Tambah Field')
                            ->reorderable()
                            ->collapsible()
                            ->default([]),
                    ]),
            ]);
    }
}
