<?php

namespace App\Filament\Resources\Reseps\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class ResepForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // === Header Resep ===
            Select::make('produk_id')
                ->label('Produk')
                // tampilkan nama produk di dropdown (lebih ramah). 
                // kalau mau persis contohmu, ubah 'nama_barang' -> 'id'
                ->relationship('produk', 'nama_barang')
                ->searchable()
                ->preload()
                ->required(),

            Toggle::make('is_active')
                ->label('Aktif?')
                ->default(true)
                ->required(),

            // === Komposisi (detail resep) ===
            Repeater::make('details')
                ->relationship()          // ke hasMany ResepDetail
                ->defaultItems(1)
                ->minItems(1)
                ->addActionLabel('Tambah Bahan')
                ->schema([
                    Select::make('bahan_baku_id')
                        ->label('Bahan Baku')
                        // pakai relasi di model ResepDetail: bahanBaku()
                        ->relationship('bahanBaku', 'nama_bahan')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('qty_per_porsi')
                        ->label('Qty per porsi')
                        ->numeric()
                        ->required()
                        ->helperText('Isi sesuai satuan pakai (gram/ml/pcs).'),

                    Textarea::make('keterangan')
                        ->rows(1)
                        ->nullable(),
                ])
                ->reorderableWithDragAndDrop()
                ->collapsible(),
        ]);
    }
}
