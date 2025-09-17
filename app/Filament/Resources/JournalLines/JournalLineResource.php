<?php

namespace App\Filament\Resources\JournalLines;

use App\Filament\Resources\JournalLines\Pages\ListJournalLines;
use App\Filament\Resources\JournalLines\Tables\JournalLinesTable;
use App\Models\JournalLine;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;    // pakai Schema biar konsisten dengan pattern kamu
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JournalLineResource extends Resource
{
    protected static ?string $model = JournalLine::class;

    // Taruh di grup "Additional" biar nggak bercampur dengan Cash Flow
    protected static \UnitEnum|string|null $navigationGroup = 'Cash Flow';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText ;
    protected static ?string $navigationLabel = 'Detail Jurnal';
    protected static ?string $recordTitleAttribute = 'line_no';

    // Form tidak dipakai (kita kosongkan)
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return JournalLinesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournalLines::route('/'),
            // tidak ada create/edit/view
        ];
    }

    // Opsional: kalau mau hidden dari sidebar, return false
    // public static function shouldRegisterNavigation(): bool { return false; }
}
