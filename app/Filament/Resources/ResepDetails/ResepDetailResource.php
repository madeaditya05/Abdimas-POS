<?php

namespace App\Filament\Resources\ResepDetails;

use App\Filament\Resources\ResepDetails\Pages\CreateResepDetail;
use App\Filament\Resources\ResepDetails\Pages\EditResepDetail;
use App\Filament\Resources\ResepDetails\Pages\ListResepDetails;
use App\Filament\Resources\ResepDetails\Schemas\ResepDetailForm;
use App\Filament\Resources\ResepDetails\Tables\ResepDetailsTable;
use App\Models\ResepDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ResepDetailResource extends Resource
{
    protected static ?string $model = ResepDetail::class;

    // Icon beda dari bahan baku; pilih yang "detail" vibes
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    // Taruh di group Master Data biar bareng Resep & Bahan Baku
    protected static \UnitEnum|string|null $navigationGroup = 'Additional';

    // Biar aman, pakai 'id' sebagai judul record
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ResepDetailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResepDetailsTable::configure($table);
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
            'index'  => ListResepDetails::route('/'),
            'create' => CreateResepDetail::route('/create'),
            'edit'   => EditResepDetail::route('/{record}/edit'),
        ];
    }
}
