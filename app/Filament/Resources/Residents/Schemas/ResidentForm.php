<?php

namespace App\Filament\Resources\Residents\Schemas;

use App\Enums\Gender;
use App\Enums\ResidentRelationship;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ResidentForm
{
    public const RELIGIONS = [
        'Islam' => 'Islam',
        'Kristen Protestan' => 'Kristen Protestan',
        'Katolik' => 'Katolik',
        'Hindu' => 'Hindu',
        'Buddha' => 'Buddha',
        'Konghucu' => 'Konghucu',
        'Lainnya' => 'Lainnya',
    ];

    public const MARITAL_STATUSES = [
        'Belum Kawin' => 'Belum Kawin',
        'Kawin' => 'Kawin',
        'Cerai Hidup' => 'Cerai Hidup',
        'Cerai Mati' => 'Cerai Mati',
    ];

    /**
     * @param  bool  $withFamily  Sertakan pemilih Kartu Keluarga. Dimatikan saat
     *                            dipakai di Relation Manager (KK sudah ditentukan induk).
     */
    public static function configure(Schema $schema, bool $withFamily = true): Schema
    {
        return $schema
            ->components([
                Section::make('Data Diri')
                    ->columns(2)
                    ->schema(array_filter([
                        $withFamily
                            ? Select::make('family_id')
                                ->label('Kartu Keluarga')
                                ->relationship('family', 'kk_number')
                                ->searchable()
                                ->preload()
                                ->required()
                            : null,
                        TextInput::make('full_name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->maxLength(16)
                            // Bukan ->numeric(): angka 16 digit akan rusak jadi float.
                            ->rule('digits:16')
                            ->helperText('16 digit. Disimpan terenkripsi.'),
                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options(Gender::class)
                            ->required(),
                        Select::make('relationship')
                            ->label('Status dalam Keluarga')
                            ->options(ResidentRelationship::class)
                            ->required(),
                        TextInput::make('birth_place')
                            ->label('Tempat Lahir')
                            ->maxLength(255),
                        DatePicker::make('birth_date')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->maxDate(now())
                            ->displayFormat('d M Y'),
                    ])),

                Section::make('Agama, Pekerjaan & Kontak')
                    ->columns(2)
                    ->schema([
                        Select::make('religion')
                            ->label('Agama')
                            ->options(self::RELIGIONS),
                        Select::make('marital_status')
                            ->label('Status Perkawinan')
                            ->options(self::MARITAL_STATUSES),
                        TextInput::make('occupation')
                            ->label('Pekerjaan')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('No. Telepon')
                            ->tel()
                            ->maxLength(30),
                        Select::make('user_id')
                            ->label('Tautkan Akun (opsional)')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Kaitkan warga ini dengan akun login miliknya.'),
                        Toggle::make('is_active')
                            ->label('Warga Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
