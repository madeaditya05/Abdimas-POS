<?php

namespace App\Filament\Resources\PembelianBahans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PembelianBahansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_pembelian')
                    ->label('Kode')
                    ->searchable()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')           // format bebas
                    ->timezone('Asia/Jakarta')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('supplier_nama')
                    ->label('Supplier')
                    ->searchable()
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('total')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([ EditAction::make() ])
            ->toolbarActions([
                BulkActionGroup::make([ DeleteBulkAction::make() ]),
            ]);
    }
}
