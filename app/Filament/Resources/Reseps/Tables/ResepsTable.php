<?php

namespace App\Filament\Resources\Reseps\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class ResepsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('produk.nama_barang')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('produk.kategori')
                    ->label('Kategori')
                    ->badge()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Biar bisa toggle aktif/nonaktif langsung di tabel:
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('details_count')
                    ->label('# Bahan')
                    ->counts('details')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(40)
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
                Tables\Filters\SelectFilter::make('produk_id')
                    ->label('Produk')
                    ->relationship('produk', 'nama_barang')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
