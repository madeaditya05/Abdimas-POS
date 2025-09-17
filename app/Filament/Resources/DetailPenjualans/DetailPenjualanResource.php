<?php

namespace App\Filament\Resources\DetailPenjualans;

use App\Filament\Resources\DetailPenjualans\Pages\ListDetailPenjualans;
use App\Filament\Resources\DetailPenjualans\Pages\ViewDetailPenjualan; // kalau kamu generate view=yes
use App\Filament\Resources\DetailPenjualans\Tables\DetailPenjualansTable;
use App\Models\PenjualanDetail;                                       // <-- ini yang benar
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DetailPenjualanResource extends Resource
{
    protected static ?string $model = PenjualanDetail::class;         // <-- ini juga dibetulkan

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;
    protected static ?string $navigationLabel = 'Detail Penjualan';
    // Taruh di group Master Data biar bareng Resep & Bahan Baku
    protected static \UnitEnum|string|null $navigationGroup = 'Additional';
    protected static ?string $recordTitleAttribute = 'id';

    // Kita tidak menggunakan form untuk create/edit (read-only list)
    public static function table(Table $table): Table
    {
        return DetailPenjualansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        // Kalau tadi jawab "yes" untuk View, biarkan 'view' di sini.
        // Kalau tidak mau halaman View, hapus baris 'view' saja.
        return [
            'index' => ListDetailPenjualans::route('/'),
            'view'  => ViewDetailPenjualan::route('/{record}'), // <-- hapus kalau tidak dipakai
            // JANGAN daftarkan create/edit: kita read-only
        ];
    }
}
