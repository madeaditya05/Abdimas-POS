<?php

namespace App\Filament\Resources\StokMutasis\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StokMutasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bahan_baku_id')
                    ->required()
                    ->numeric(),
                Select::make('tipe')
                    ->options(['IN' => 'I n', 'OUT' => 'O u t', 'ADJ' => 'A d j'])
                    ->required(),
                TextInput::make('qty')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('tanggal'),
                TextInput::make('sumber_type')
                    ->required(),
                TextInput::make('sumber_id')
                    ->required()
                    ->numeric(),
                TextInput::make('note')
                    ->default(null),
            ]);
    }
}
