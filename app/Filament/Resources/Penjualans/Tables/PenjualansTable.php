<?php

namespace App\Filament\Resources\Penjualans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PenjualansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_penjualan')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')           // format bebas
                    ->timezone('Asia/Jakarta')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->alignCenter(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('bayar')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('kembalian')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('metode')
                    ->searchable()
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
                //
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
