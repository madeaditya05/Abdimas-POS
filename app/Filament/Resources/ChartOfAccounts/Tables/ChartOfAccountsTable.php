<?php

namespace App\Filament\Resources\ChartOfAccounts\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class ChartOfAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Akun')
                    ->searchable()
                    ->wrap(),

                // TextColumn::badge() (pengganti BadgeColumn)
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'asset'     => 'Aset',
                        'liability' => 'Liabilitas',
                        'equity'    => 'Ekuitas',
                        'revenue'   => 'Pendapatan',
                        'expense'   => 'Beban',
                        default     => $state,
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'asset'     => 'warning',
                        'liability' => 'danger',
                        'equity'    => 'success',
                        'revenue'   => 'primary',
                        'expense'   => 'info',
                        default     => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('normal_side')
                    ->label('Normal')
                    ->formatStateUsing(fn (string $state) => $state === 'debit' ? 'Debit' : 'Kredit')
                    ->badge()
                    ->color(fn (string $state) => $state === 'debit' ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('code')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Akun')
                    ->options([
                        'asset'     => 'Aset',
                        'liability' => 'Liabilitas',
                        'equity'    => 'Ekuitas',
                        'revenue'   => 'Pendapatan',
                        'expense'   => 'Beban',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ]);
    }
}
