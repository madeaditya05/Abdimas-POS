<?php

namespace App\Filament\Resources\ResepDetails\Tables;

use Filament\Tables\Table;
use Filament\Tables;

class ResepDetailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('resep.id')
                    ->label('ID Resep')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('resep.produk.nama_barang')
                    ->label('Produk')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('bahanBaku.nama_bahan')
                    ->label('Bahan Baku')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('qty_per_porsi')
                    ->label('Qty')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('resep_id')
                    ->label('Resep')
                    ->relationship('resep', 'id')
                    ->preload()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('bahan_baku_id')
                    ->label('Bahan Baku')
                    ->relationship('bahanBaku', 'nama_bahan')
                    ->preload()
                    ->searchable(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
