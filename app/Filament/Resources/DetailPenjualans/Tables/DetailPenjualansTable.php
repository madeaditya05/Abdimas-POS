<?php

namespace App\Filament\Resources\DetailPenjualans\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;

class DetailPenjualansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Header penjualan
                TextColumn::make('penjualan.kode_penjualan')
                    ->label('Kode')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('penjualan.tanggal')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->alignCenter(),

                // Detail
                TextColumn::make('produk.nama_barang')
                    ->label('Produk')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('harga')
                    ->label('Harga')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                // // Range tanggal (dari header penjualan.tanggal)
                // Filter::make('range_tanggal')
                //     ->form([
                        
                //         DatePicker::make('from')->label('Dari'),
                //         DatePicker::make('until')->label('Sampai'),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         $query->when(
                //             $data['from'] ?? null,
                //             fn (Builder $q, $d) =>
                //                 $q->whereHas('penjualan', fn (Builder $h) => $h->whereDate('tanggal', '>=', $d))
                //         );

                //         $query->when(
                //             $data['until'] ?? null,
                //             fn (Builder $q, $d) =>
                //                 $q->whereHas('penjualan', fn (Builder $h) => $h->whereDate('tanggal', '<=', $d))
                //         );

                //         return $query;
                //     }),

                SelectFilter::make('produk_id')
                    ->relationship('produk', 'nama_barang')
                    ->label('Produk')
                    ->preload()
                    ->searchable(),
            ]);
    }
}
