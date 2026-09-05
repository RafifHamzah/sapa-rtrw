<?php

namespace App\Filament\Resources\Complaints\Schemas;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComplaintForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Laporan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options(ComplaintCategory::class)
                            ->required(),
                        TextInput::make('location')
                            ->label('Patokan Lokasi')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('photo_path')
                            ->label('Foto')
                            ->image()
                            ->disk('public')
                            ->directory('complaints')
                            ->maxSize(4096)
                            ->columnSpanFull(),
                    ]),

                Section::make('Penanganan')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(ComplaintStatus::class)
                            ->required()
                            ->helperText('Untuk mencatat riwayat, gunakan aksi "Perbarui Status" di tabel.'),
                        Select::make('handled_by')
                            ->label('Ditangani Oleh')
                            ->relationship('handler', 'name')
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('resolved_at')
                            ->label('Waktu Selesai'),
                        Textarea::make('response')
                            ->label('Tanggapan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
