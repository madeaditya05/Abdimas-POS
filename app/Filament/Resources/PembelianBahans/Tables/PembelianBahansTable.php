<?php

namespace App\Filament\Resources\PembelianBahans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
// use Filament\Support\Enums\Alignment; // jika ingin alignment enum di v4

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
                    ->dateTime('d M Y H:i')
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
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignCenter(),

                // === Thumbnail bukti bayar (catatan) ===
                ImageColumn::make('catatan')
                    ->label('Bukti')
                    ->disk('public')
                    ->alignCenter()
                    ->visibility('public'),


                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
