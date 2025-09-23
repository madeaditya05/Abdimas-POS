<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                // Posisi: kasir / barista / admin (default: kasir)
                Select::make('posisi')
                    ->label('Posisi')
                    ->options([
                        'kasir'   => 'Kasir',
                        'barista' => 'Barista',
                    ])
                    ->required(),

                DateTimePicker::make('email_verified_at'),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    // hanya required saat create
                    ->required(fn (string $context) => $context === 'create')
                    // hash saat di-submit; saat edit kosongkan berarti tidak diubah
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state)),
            ]);
    }
}
