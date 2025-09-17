<?php

namespace App\Filament\Resources\BahanBakus\Tables;

use App\Models\BahanBaku;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use App\Filament\Resources\BahanBakus\Schemas\BahanBakuForm;

class BahanBakusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_bahan')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('nama_bahan')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->alignCenter(),

                // pakai kolom yang benar di DB
                TextColumn::make('satuan_beli')
                    ->label('Satuan')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('satuan_pakai')
                    ->label('Satuan Pakai')
                    ->sortable()
                    ->alignCenter(),

                // ⬇️ Fallback: stok_minimum || min_stok || min_stock
                TextColumn::make('stok_minimum')
                    ->label('Min Stok')
                    ->formatStateUsing(function ($state, BahanBaku $record) {
                        return $state
                            ?? $record->getAttribute('min_stok')
                            ?? $record->getAttribute('min_stock');
                    })
                    ->numeric()
                    ->alignCenter()
                    ->placeholder('—'),

                // harga beli terakhir (computed dari pembelian detail)
                TextColumn::make('harga_beli_terakhir')
                    ->label('Harga beli')
                    ->state(function (BahanBaku $record) {
                        $last = $record->pembelianDetails()
                            ->latest('created_at')
                            ->first();
                        // ganti 'harga_satuan' kalau kolomnya beda
                        return $last?->harga_satuan ?? null;
                    })
                    ->money('IDR')
                    ->alignCenter()
                    ->sortable(false)
                    ->toggleable(),

                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Created at')
                    ->since()
                    ->alignCenter(),
            ])
            ->defaultSort('nama_bahan')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options(BahanBakuForm::OPSI_KATEGORI),
            ]);
    }
}
