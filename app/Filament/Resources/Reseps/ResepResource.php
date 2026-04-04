<?php

namespace App\Filament\Resources\Reseps;

use App\Filament\Resources\Reseps\Pages\CreateResep;
use App\Filament\Resources\Reseps\Pages\EditResep;
use App\Filament\Resources\Reseps\Pages\ListReseps;
use App\Filament\Resources\Reseps\Schemas\ResepForm;
use App\Filament\Resources\Reseps\Tables\ResepsTable;
use App\Models\Resep;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ResepResource extends Resource
{
    protected static ?string $model = Resep::class;

    // ganti icon (bukan beaker) & set group "Master Data"
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $recordTitleAttribute = 'Resep';

    public static function form(Schema $schema): Schema
    {
        return ResepForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResepsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListReseps::route('/'),
            'create' => CreateResep::route('/create'),
            'edit'   => EditResep::route('/{record}/edit'),
        ];
    }
}
