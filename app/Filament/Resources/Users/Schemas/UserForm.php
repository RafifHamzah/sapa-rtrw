<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserStatus;
use App\Models\Resident;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('No. Telepon')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->revealable()
                            // Wajib saat membuat user baru; saat edit, biarkan kosong
                            // untuk mempertahankan kata sandi lama. Cast 'hashed' di
                            // model User yang meng-hash otomatis.
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255),
                    ]),

                Section::make('Peran & Verifikasi')
                    ->columns(2)
                    ->schema([
                        Select::make('rt_id')
                            ->label('RT')
                            ->relationship('rt', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Status')
                            ->options(UserStatus::class)
                            ->default(UserStatus::Active)
                            ->required(),
                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                        Select::make('resident_id')
                            ->label('Tautkan ke Data Warga')
                            ->helperText('Hubungkan akun ini ke satu data penduduk (residents.user_id).')
                            ->searchable()
                            ->preload()
                            // Bukan kolom di tabel users; disinkronkan manual di
                            // halaman Create/Edit (relasi hasOne User->Resident).
                            ->options(function (?User $record): array {
                                return Resident::query()
                                    ->with('family')
                                    ->where(function ($query) use ($record): void {
                                        $query->whereNull('user_id');

                                        if ($record) {
                                            $query->orWhere('user_id', $record->id);
                                        }
                                    })
                                    ->get()
                                    ->mapWithKeys(fn (Resident $resident): array => [
                                        $resident->id => $resident->full_name
                                            . ' — KK ' . ($resident->family?->kk_number ?? '-'),
                                    ])
                                    ->all();
                            })
                            ->columnSpanFull(),
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->visible(fn (?User $record): bool => (bool) $record?->isRejected())
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
