<?php

namespace App\Filament\Resources\PembelianBahanDetails\Schemas;

use App\Models\BahanBaku;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PembelianBahanDetailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('pembelian_bahan_id')
                ->label('Pembelian')
                ->relationship('header', 'kode_pembelian')
                ->searchable()->preload()->required(),

            Select::make('bahan_baku_id')
                ->label('Bahan')
                ->relationship('bahan', 'nama_bahan')
                ->searchable()->preload()->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    $b = BahanBaku::find($state);
                    if ($b) {
                        $set('nama_bahan', $b->nama_bahan);
                        $set('satuan_beli', $b->satuan_beli);
                        $set('isi_per_kemasan', (float) ($b->isi_per_kemasan ?? 0));
                        $set('konversi_ke_pakai', (float) ($b->konversi_beli_ke_pakai ?? 1));
                    }
                })
                ->required(),

            TextInput::make('nama_bahan')->readOnly()->dehydrated(true),
            TextInput::make('satuan_beli')->readOnly()->dehydrated(true),
            TextInput::make('isi_per_kemasan')->numeric()->readOnly()->dehydrated(true),
            TextInput::make('konversi_ke_pakai')->numeric()->readOnly()->dehydrated(true),

            DatePicker::make('expired_date')->label('Expired (opsional)'),

            TextInput::make('qty_beli')
                ->numeric()->default(1)->required()->live()
                ->afterStateUpdated(fn($s,$set,$get)=>$set('subtotal',(float)($get('harga_satuan')??0)*(float)($s??0))),

            TextInput::make('harga_satuan')
                ->numeric()->default(0)->required()->live()
                ->afterStateUpdated(fn($s,$set,$get)=>$set('subtotal',(float)($get('qty_beli')??0)*(float)($s??0))),

            TextInput::make('subtotal')->numeric()->prefix('Rp')->readOnly()->dehydrated(true),

            TextInput::make('catatan')->columnSpanFull(),
        ]);
    }
}
