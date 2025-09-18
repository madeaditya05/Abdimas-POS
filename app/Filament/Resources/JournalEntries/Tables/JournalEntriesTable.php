<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entry_no')
                    ->label('No. Jurnal')
                    ->searchable()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ref_no')
                    ->label('Referensi')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('memo')
                    ->label('Memo')
                    ->limit(40)
                    ->alignCenter()
                    ->wrap(),

                Tables\Columns\TextColumn::make('lines_count')
                    ->label('Baris')
                    ->state(fn (\App\Models\JournalEntry $record) => $record->lines()->count())
                    ->sortable(false),
            ])
            ->defaultSort('date', 'desc')
            ->filters([]);
    }
}
