<?php

namespace App\Filament\Resources\BahanBakus;

use App\Filament\Resources\BahanBakus\Pages;
use App\Filament\Resources\BahanBakus\Schemas\BahanBakuForm;
use App\Filament\Resources\BahanBakus\Tables\BahanBakusTable;
use App\Models\BahanBaku;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BahanBakuResource extends Resource
{
    protected static ?string $model = BahanBaku::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';
    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'nama_bahan';

    public static function getNavigationLabel(): string
    {
        return 'Bahan Baku';
    }

    public static function getPluralLabel(): string
    {
        return 'Bahan Baku';
    }

    public static function form(Schema $schema): Schema
    {
        return BahanBakuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BahanBakusTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBahanBakus::route('/'),
            'create' => Pages\CreateBahanBaku::route('/create'),
            'edit'   => Pages\EditBahanBaku::route('/{record}/edit'),
        ];
    }
}
