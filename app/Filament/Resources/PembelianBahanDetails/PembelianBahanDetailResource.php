<?php

namespace App\Filament\Resources\PembelianBahanDetails;

use App\Filament\Resources\PembelianBahanDetails\Pages\CreatePembelianBahanDetail;
use App\Filament\Resources\PembelianBahanDetails\Pages\EditPembelianBahanDetail;
use App\Filament\Resources\PembelianBahanDetails\Pages\ListPembelianBahanDetails;
use App\Filament\Resources\PembelianBahanDetails\Schemas\PembelianBahanDetailForm;
use App\Filament\Resources\PembelianBahanDetails\Tables\PembelianBahanDetailsTable;
use App\Models\PembelianBahanDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PembelianBahanDetailResource extends Resource
{
    protected static ?string $model = PembelianBahanDetail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PlusCircle;
    protected static \UnitEnum|string|null $navigationGroup = 'Additional';

    public static function form(Schema $schema): Schema
    {
        return PembelianBahanDetailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PembelianBahanDetailsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPembelianBahanDetails::route('/'),
            'create' => CreatePembelianBahanDetail::route('/create'),
            'edit'   => EditPembelianBahanDetail::route('/{record}/edit'),
        ];
    }
}
