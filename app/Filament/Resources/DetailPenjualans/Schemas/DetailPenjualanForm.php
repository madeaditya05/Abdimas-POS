<?php

namespace App\Filament\Resources\DetailPenjualans\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class DetailPenjualanForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\TextInput::make('produk_id')
                ->label('Produk')
                ->required(),

            Forms\Components\TextInput::make('qty')
                ->label('Jumlah')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('harga')
                ->label('Harga')
                ->numeric()
                ->required(),
        ];
    }
}
