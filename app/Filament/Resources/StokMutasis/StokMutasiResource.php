<?php

namespace App\Filament\Resources\StokMutasis;

use App\Filament\Resources\StokMutasis\Pages\ListStokMutasis;
use App\Filament\Resources\StokMutasis\Tables\StokMutasisTable;
use App\Models\StokMutasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StokMutasiResource extends Resource
{
    protected static ?string $model = StokMutasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static \UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Mutasi Stok';
    protected static ?int $navigationSort     = 40;

    // NOTE: read-only → kita nggak pakai form sama sekali.

    public static function table(Table $table): Table
    {
        return StokMutasisTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStokMutasis::route('/'),
        ];
    }
}
