<?php

namespace App\Filament\Resources\Penjualans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
// use Filament\Tables\Actions\Action;

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
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('bayar')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignCenter(),
                TextColumn::make('kembalian')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
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

                // Action::make('cetakTiket')
                // ->label('Cetak Tiket')
                // ->icon('heroicon-o-printer')
                // ->url(fn ($record) => route('tickets.penjualan', $record))
                // ->openUrlInNewTab()
                // ->color('success'),

                
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                
            ]);
    }
}
