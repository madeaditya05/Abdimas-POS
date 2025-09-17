<?php

namespace App\Filament\Resources\StokMutasis\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;


class StokMutasisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('bahan.nama_bahan')
                    ->label('Bahan Baku')
                    ->searchable()           // cari berdasarkan nama bahan
                    ->sortable()
                    ->placeholder('-')
                    ->alignCenter(),

                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->colors([
                        'success' => 'IN',
                        'danger'  => 'OUT',
                        'warning' => 'ADJ',
                    ])
                    ->sortable()
                    ->alignCenter(),


                TextColumn::make('qty')
                    ->label('Jumlah')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->alignCenter(),


                TextColumn::make('sumber_type')
                    ->label('Sumber')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),


                TextColumn::make('sumber_id')
                    ->label('ID Sumber')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),


                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(60)
                    ->wrap()
                    ->alignCenter(),


                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipe')
                    ->label('Filter Tipe')
                    ->options([
                        'IN'  => 'IN (Masuk)',
                        'OUT' => 'OUT (Keluar)',
                        'ADJ' => 'ADJ (Penyesuaian)',
                        
                    ]),
            ])
            // READ-ONLY: tanpa action apa pun
            ->recordActions([]);
    }
}
