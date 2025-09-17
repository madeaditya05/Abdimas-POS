<?php

namespace App\Filament\Resources\PembelianBahans;

use App\Filament\Resources\PembeLianBahans\Pages\CreatePembelianBahan;
use App\Filament\Resources\PembeLianBahans\Pages\EditPembelianBahan;
use App\Filament\Resources\PembeLianBahans\Pages\ListPembelianBahans;
use App\Filament\Resources\PembelianBahans\Schemas\PembelianBahanForm;
use App\Filament\Resources\PembelianBahans\Tables\PembelianBahansTable;
use App\Models\PembelianBahan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PembelianBahanResource extends Resource
{
    protected static ?string $model = PembelianBahan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;
    protected static \UnitEnum|string|null $navigationGroup = 'Transaksi';
    protected static ?string $modelLabel = 'Pembelian Bahan';

    public static function form(Schema $schema): Schema
    {
        return PembelianBahanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PembelianBahansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPembelianBahans::route('/'),
            'create' => CreatePembelianBahan::route('/create'),
            'edit'   => EditPembelianBahan::route('/{record}/edit'),
        ];
    }
}
