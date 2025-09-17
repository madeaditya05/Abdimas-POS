<?php

namespace App\Filament\Resources\ResepDetails\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;

class ResepDetailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Pilih resep (boleh tampil id dulu; bisa diganti label produk nanti)
            Select::make('resep_id')
                ->label('Resep')
                ->relationship('resep', 'id')
                ->preload()
                ->searchable()
                ->required(),

            // Pilih bahan baku
            Select::make('bahan_baku_id')
                ->label('Bahan Baku')
                ->relationship('bahanBaku', 'nama_bahan')
                ->preload()
                ->searchable()
                ->required(),

            // Qty per porsi
            TextInput::make('qty_per_porsi')
                ->label('Qty per porsi')
                ->numeric()
                ->required()
                ->helperText('Isi sesuai satuan pakai (gram/ml/pcs).'),

            // Keterangan opsional
            Textarea::make('keterangan')
                ->rows(1)
                ->nullable(),

            // (Opsional) metadata tambahan sebagai JSON
            KeyValue::make('additional')
                ->label('Additional (opsional)')
                ->keyLabel('Key')
                ->valueLabel('Value')
                // ->addButtonLabel('Tambah item')
                ->reorderable()
                ->nullable(),
        ]);
    }
}
