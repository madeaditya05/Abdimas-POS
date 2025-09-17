<?php

namespace App\Filament\Resources\JournalLines\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JournalLineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('journal_entry_id')
                    ->required()
                    ->numeric(),
                TextInput::make('account_id')
                    ->required()
                    ->numeric(),
                TextInput::make('debit')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('credit')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('memo')
                    ->default(null),
                TextInput::make('line_no')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
