<?php

namespace App\Filament\Resources\PembelianBahanDetails\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PembelianBahanDetailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('header.kode_pembelian')->label('Pembelian')->searchable()->alignCenter(),
                TextColumn::make('nama_bahan')->searchable()->alignCenter(),
                TextColumn::make('satuan_beli')->alignCenter(),
                TextColumn::make('qty_beli')->numeric()->sortable()->alignCenter(),
                TextColumn::make('harga_satuan')->numeric()->sortable()->alignCenter(),
                TextColumn::make('subtotal')->numeric()->sortable()->alignCenter(),
                TextColumn::make('expired_date')->date()->sortable()->alignCenter(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true)->alignCenter(),
            ])
            ->recordActions([ EditAction::make() ])
            ->toolbarActions([ BulkActionGroup::make([ DeleteBulkAction::make() ]) ]);
    }
}
