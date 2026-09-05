<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionType;
use App\Models\TransactionCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TransactionForm
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
                Select::make('transaction_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    // Jenis transaksi mengikuti jenis kategori yang dipilih.
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $type = TransactionCategory::find($state)?->type;
                        if ($type) {
                            $set('type', $type->value);
                        }
                    })
                    ->required(),
                Select::make('type')
                    ->label('Jenis')
                    ->options(TransactionType::class)
                    ->required(),
                TextInput::make('amount')
                    ->label('Nominal')
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                TextInput::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('transaction_date')
                    ->label('Tanggal')
                    ->default(now())
                    ->required(),
                FileUpload::make('receipt_path')
                    ->label('Foto Nota')
                    ->image()
                    ->disk('public')
                    ->directory('receipts')
                    ->imageEditor()
                    ->maxSize(4096)
                    ->columnSpanFull(),
                Hidden::make('created_by')
                    ->default(fn (): ?int => Auth::id()),
            ]);
    }
}
