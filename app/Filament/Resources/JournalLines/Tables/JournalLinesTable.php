<?php

namespace App\Filament\Resources\JournalLines\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class JournalLinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Dari header (relasi entry)
                Tables\Columns\TextColumn::make('entry.date')
                    ->label('Tanggal')
                    ->date('Y-m-d')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('entry.entry_no')
                    ->label('No. Jurnal')
                    ->searchable()
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('coa.name')
                    ->label('Keterangan')
                    ->wrap()
                    ->alignStart()                   
                    ->searchable(),

                // Tables\Columns\TextColumn::make('line_no')
                //     ->label('#')
                //     ->alignEnd()
                //     ->sortable(),

                // Akun (relasi coa)
                Tables\Columns\TextColumn::make('coa.code')
                    ->label('Kode Akun')
                    ->toggleable()
                    ->alignCenter()
                    ->sortable(),


                // Angka
                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit')
                    ->alignEnd()                   
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('credit')
                    ->label('Kredit')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                // Tables\Columns\TextColumn::make('memo')
                //     ->label('Keterangan')
                //     ->limit(60)
                //     ->wrap()
                //     ->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                // Filter akun
                Tables\Filters\SelectFilter::make('account_id')
                    ->label('Akun')
                    ->relationship('coa', 'name') // tampilkan nama COA
                    ->preload()
                    ->searchable(),
          ]);

            //     // Filter tanggal (range) berdasarkan entry.date
            //     Tables\Filters\Filter::make('date_range')
            //         ->form([
            //             \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
            //             \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
            //         ])
            //         ->query(function ($query, array $data) {
            //             return $query
            //                 ->when($data['from'] ?? null, fn ($q, $from) => $q->whereHas('entry', fn ($qq) => $qq->whereDate('date', '>=', $from)))
            //                 ->when($data['until'] ?? null, fn ($q, $until) => $q->whereHas('entry', fn ($qq) => $qq->whereDate('date', '<=', $until)));
            //         }),
            // ])
            // ->actions([
            //     // read-only: tanpa Edit/Delete
            // ])
            // ->bulkActions([
            //     // kosong juga
    }
}
