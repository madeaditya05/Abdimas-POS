<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->alignCenter(),

                // Kolom posisi yang baru
                TextColumn::make('posisi')
                    ->label('Posisi')
                    ->sortable()
                    ->searchable()
                     // TAMPILKAN Title Case: "barista" -> "Barista"
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : '-')
                    ->badge()
                    ->colors([
                        'success' => 'Barista',
                        'warning' => 'Kasir',
                    ])
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
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
