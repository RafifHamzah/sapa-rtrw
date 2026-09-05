<?php

namespace App\Filament\Resources\Announcements\Schemas;

use App\Enums\AnnouncementCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengumuman')
                    ->columns(2)
                    ->schema([
                        Select::make('rt_id')
                            ->label('RT')
                            ->relationship('rt', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options(AnnouncementCategory::class)
                            ->default(AnnouncementCategory::General)
                            ->required(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('Isi')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('attachment_path')
                            ->label('Lampiran')
                            ->disk('public')
                            ->directory('announcements')
                            ->maxSize(8192)
                            ->columnSpanFull(),
                    ]),

                Section::make('Penayangan')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_pinned')
                            ->label('Sematkan di atas')
                            ->default(false),
                        DateTimePicker::make('published_at')
                            ->label('Waktu Tayang')
                            ->helperText('Kosongkan untuk menyimpan sebagai draf. Isi tanggal mendatang untuk menjadwalkan.')
                            ->default(now()),
                        Hidden::make('user_id')
                            ->default(fn (): ?int => Auth::id()),
                    ]),
            ]);
    }
}
