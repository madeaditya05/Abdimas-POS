<?php

namespace App\Filament\Resources\Produks\Tables;

use App\Models\KategoriProduk;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProduksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('stok')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('harga')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->alignCenter(),

                // Kategori dengan badge & warna
                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->alignCenter()
                    ->color(fn (string $state) => match ($state) {
                        'coffee'     => 'success',
                        'non_coffee' => 'warning',
                        'snack'      => 'danger',
                        default      => 'secondary',
                    })
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->kategoriProduk?->nama
                        ?? Str::of((string) $state)->replace('_', ' ')->title()
                    ),

                ImageColumn::make('gambar')
                    ->label('Gambar')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter kategori
                SelectFilter::make('kategori')
            
            ->label('Kategori')
            ->options(fn () => KategoriProduk::allOptions())
            ->preload()
            ->native(false),

                Filter::make('stok_menipis')
            ->label('Stok < 5')
            ->query(fn (Builder $query) => $query->where('stok', '<', 5)),
                    ]);
    }
}   

            
