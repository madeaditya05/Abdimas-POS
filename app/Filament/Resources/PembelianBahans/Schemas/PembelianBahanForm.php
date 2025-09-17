<?php

namespace App\Filament\Resources\PembelianBahans\Schemas;

use App\Models\PembelianBahan;
use App\Models\BahanBaku;
use Filament\Facades\Filament;
use Filament\Forms\Components\{Hidden, TextInput, DateTimePicker, Repeater, Select, DatePicker};
use Filament\Schemas\Schema;

class PembelianBahanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('user_id')
                ->default(fn () => (int) Filament::auth()->id())
                ->dehydrated(true),

            TextInput::make('kode_pembelian')
                ->label('Kode')
                ->default(fn () => PembelianBahan::generateKodeHarian())
                ->readOnly()
                ->dehydrated(true),

            DateTimePicker::make('tanggal')
                ->timezone('Asia/Jakarta')
                ->default(now('Asia/Jakarta'))
                ->seconds(false)
                ->displayFormat('dd/MM/yyyy HH:mm')
                ->required(),

            TextInput::make('supplier_nama')->label('Supplier')->maxLength(120),
            TextInput::make('supplier_kontak')->label('Kontak')->maxLength(120),

            TextInput::make('total')->numeric()->prefix('Rp')->readOnly()->dehydrated(true),

            // =======================
            // REPEATER DETAIL BELI
            // =======================
            Repeater::make('details')
                ->relationship()            // relasi PembelianBahan::details()
                ->columnSpanFull()
                ->defaultItems(1)
                ->reorderable(false)
                ->columns(6)
                ->schema([
                    Select::make('bahan_baku_id')
                        ->label('Bahan')
                        ->relationship('bahan','nama_bahan')
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
                        ->required()
                        ->columnSpan(3),

                    TextInput::make('nama_bahan')->readOnly()->dehydrated(true)->columnSpan(3),

                    TextInput::make('satuan_beli')->readOnly()->dehydrated(true)->columnSpan(2),
                    TextInput::make('isi_per_kemasan')->numeric()->readOnly()->dehydrated(true)->columnSpan(2),
                    TextInput::make('konversi_ke_pakai')->numeric()->readOnly()->dehydrated(true)->columnSpan(2),

                    DatePicker::make('expired_date')->label('Expired')->columnSpan(2),

                    TextInput::make('qty_beli')
                        ->numeric()->default(1)->required()->live()
                        ->afterStateUpdated(fn($s,$set,$get)=>$set('subtotal',(float)($get('harga_satuan')??0)*(float)($s??0)))
                        ->columnSpan(2),

                    TextInput::make('harga_satuan')
                        ->numeric()->default(0)->required()->live()
                        ->afterStateUpdated(fn($s,$set,$get)=>$set('subtotal',(float)($get('qty_beli')??0)*(float)($s??0)))
                        ->prefix('Rp')
                        ->columnSpan(2),

                    TextInput::make('subtotal')
                        ->numeric()->prefix('Rp')->readOnly()->dehydrated(true)
                        ->columnSpan(2),
                ])
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $total = array_sum(array_map(
                        fn ($i) => (float)($i['subtotal'] ?? 0),
                        $get('details') ?? []
                    ));
                    $set('total', $total);
                }),

            TextInput::make('catatan')->columnSpanFull(),
        ]);
    }
}
