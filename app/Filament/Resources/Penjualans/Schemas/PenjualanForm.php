<?php

namespace App\Filament\Resources\Penjualans\Schemas;

use App\Models\Penjualan;
use App\Models\Produk;
use Filament\Facades\Filament;                 // <-- WAJIB: pakai guard Filament
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PenjualanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // 1) Otomatis isi user_id dari user yang login di panel
            Hidden::make('user_id')
                ->default(fn () => (int) Filament::auth()->id())
                ->dehydrated(true),                     // <-- perhatikan: KOMA, bukan TITIK KOMA

            // 2) Kode otomatis CFF-YYMMDD-0001 (tetap dijaga di model juga)
            TextInput::make('kode_penjualan')
                ->label('Kode')
                ->default(fn () => Penjualan::generateKodeHarian())
                ->readOnly()
                ->dehydrated(true),

            DateTimePicker::make('tanggal')
                ->timezone('Asia/Jakarta')
                ->default(now('Asia/Jakarta'))
                ->seconds(false)                 // sembunyikan detik
                ->displayFormat('dd/MM/yyyy HH:mm')
                ->required(),

            Select::make('metode')
                ->options([
                    'cash'     => 'Cash',
                    'qris'     => 'QRIS',
                    'debit'    => 'Debit',
                    'transfer' => 'Transfer',
                ])
                ->default('cash')
                ->required(),

            TextInput::make('bayar')
                ->label('Dibayar')
                ->numeric()
                ->prefix('Rp')
                ->default(0)
                ->live() // di v4 lebih dianjurkan pakai live() daripada reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $total = array_sum(array_map(
                        fn ($i) => (float) ($i['subtotal'] ?? 0),
                        $get('details') ?? []
                    ));
                    $set('total', $total);
                    $set('kembalian', max(0, ($state ?? 0) - $total));
                }),

            TextInput::make('total')
                ->numeric()
                ->prefix('Rp')
                ->readOnly()
                ->dehydrated(true),

            TextInput::make('kembalian')
                ->numeric()
                ->prefix('Rp')
                ->readOnly()
                ->dehydrated(true),

            // =======================
            //        REPEATER
            // =======================
            Repeater::make('details')
                ->relationship('details')             // relasi Penjualan::details()
                ->columnSpanFull()   // <-- ini yang bikin melebar full width
                ->defaultItems(1)
                // ->createItemButtonLabel('Tambah Produk')
                ->columns(4)
                ->schema([
                    Select::make('produk_id')
                        ->label('Produk')
                        ->relationship('produk', 'nama_barang') // butuh method produk() di PenjualanDetail
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $harga = Produk::find($state)?->harga ?? 0;
                            $set('harga', $harga);
                        })
                        ->required(),

                    TextInput::make('harga')
                        ->numeric()
                        ->prefix('Rp')
                        ->live()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $qty = (int) ($get('qty') ?? 0);
                            $set('subtotal', (float) ($state ?? 0) * $qty);
                        }),

                    TextInput::make('qty')
                        ->numeric()
                        ->default(1)
                        ->live()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $harga = (float) ($get('harga') ?? 0);
                            $set('subtotal', $harga * (int) ($state ?? 0));
                        }),

                    TextInput::make('subtotal')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly()
                        ->dehydrated(true),
                ])
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $total = array_sum(array_map(
                        fn ($i) => (float) ($i['subtotal'] ?? 0),
                        $get('details') ?? []
                    ));
                    $set('total', $total);
                    $set('kembalian', max(0, ((float) ($get('bayar') ?? 0)) - $total));
                }),
        ]);
    }
}
