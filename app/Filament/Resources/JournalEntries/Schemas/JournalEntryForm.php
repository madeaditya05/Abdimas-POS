<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use App\Models\ChartOfAccount;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('entry_no')
                ->label('No. Jurnal')
                ->helperText('Otomatis saat disimpan')
                ->disabled()
                ->dehydrated(false)
                ->extraAttributes(['class' => 'w-full']),

            DatePicker::make('date')
                ->label('Tanggal')
                ->required()
                ->default(now()->toDateString()) // <-- bukan now() object
                ->extraAttributes(['class' => 'w-full']),

            TextInput::make('ref_no')
                ->label('No. Referensi')
                ->helperText('Otomatis saat disimpan')
                ->disabled()
                ->dehydrated(false)
                ->extraAttributes(['class' => 'w-full']),

            Textarea::make('memo')
                ->label('Memo')
                ->rows(3)
                ->columnSpanFull()
                ->extraAttributes(['class' => 'w-full']),

            Repeater::make('lines')
                ->label('Detail Jurnal')
                ->relationship('lines')
                ->minItems(2)
                ->defaultItems(2)
                ->addActionLabel('Tambah Baris')
                ->columnSpanFull()
                ->columns(12)
                ->schema([
                    Select::make('account_id')
                        ->label('Akun')
                        ->options(fn () => ChartOfAccount::query()
                            ->where('is_active', true)
                            ->orderBy('code')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(8)
                        ->extraAttributes(['class' => 'w-full']),

                    TextInput::make('debit')
                        ->label('Debit')
                        ->prefix('Rp')
                        ->numeric()
                        ->default(0)
                        ->helperText('Isi salah satu: Debit atau Kredit')
                        ->columnSpan(2)
                        ->extraAttributes(['class' => 'w-full']),

                    TextInput::make('credit')
                        ->label('Kredit')
                        ->prefix('Rp')
                        ->numeric()
                        ->default(0)
                        ->helperText('Isi salah satu: Debit atau Kredit')
                        ->columnSpan(2)
                        ->extraAttributes(['class' => 'w-full']),

                    TextInput::make('memo')
                        ->label('Keterangan')
                        ->columnSpan(12)
                        ->extraAttributes(['class' => 'w-full']),
                ]),
        ]);
    }
}
