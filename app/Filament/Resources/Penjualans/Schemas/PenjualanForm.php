<?php

namespace App\Filament\Resources\Penjualans\Schemas;

use App\Models\Penjualan;
use App\Models\Produk;
use Filament\Facades\Filament;
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

            // user yang login
            Hidden::make('user_id')
                ->default(fn () => (int) Filament::auth()->id())
                ->dehydrated(true),

            // kode otomatis (tetap diisi oleh model sebagai fallback)
            TextInput::make('kode_penjualan')
                ->label('Kode')
                ->default(fn () => Penjualan::nextKode())
                ->disabled()
                ->dehydrated(false),

            DateTimePicker::make('tanggal')
                ->label('Tanggal')
                ->timezone('Asia/Jakarta')
                ->default(now('Asia/Jakarta'))
                ->seconds(false)
                ->displayFormat('dd/MM/yyyy HH:mm')
                ->required(),

            Select::make('metode')
                ->label('Metode')
                ->options([
                    'cash'     => 'Cash',
                    'qris'     => 'QRIS',
                    'debit'    => 'Debit',
                    'transfer' => 'Transfer',
                ])
                ->default('cash')
                ->required(),

            // ==== BAYAR ====
            TextInput::make('bayar')
                ->label('Dibayar')
                ->numeric()
                // tampil sebagai rupiah
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                // isi nilai dari DB saat Edit (hindari tampil 0)
                ->afterStateHydrated(function ($state, callable $set) {
                    $set('bayar', (float) $state);
                })
                // simpan sebagai angka murni (hilangkan 'Rp', titik, spasi)
                ->dehydrateStateUsing(fn ($state) =>
                    (float) preg_replace('/[^\d]/', '', (string) $state)
                )
                ->default(0)
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $total = array_sum(array_map(
                        fn ($i) => (float) ($i['subtotal'] ?? 0),
                        $get('details') ?? []
                    ));
                    $bayar = (float) preg_replace('/[^\d]/', '', (string) $state);
                    $set('total', $total);
                    $set('kembalian', max(0, $bayar - $total));
                }),

            // ==== TOTAL ====
            TextInput::make('total')
                ->label('Total')
                ->numeric()
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                ->disabled()
                ->dehydrated(true),

            // ==== KEMBALIAN ====
            TextInput::make('kembalian')
                ->label('Kembalian')
                ->numeric()
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                // hitung ulang saat Edit dibuka supaya tidak 0
                ->afterStateHydrated(function ($state, callable $set, callable $get) {
                    $total = (float) ($get('total') ?? 0);
                    $bayar = (float) ($get('bayar') ?? 0);
                    $set('kembalian', max(0, $bayar - $total));
                })
                // simpan sebagai angka murni
                ->dehydrateStateUsing(fn ($state) =>
                    (float) preg_replace('/[^\d]/', '', (string) $state)
                )
                ->readOnly()
                ->dehydrated(true),

            // =======================
            //         REPEATER
            // =======================
            Repeater::make('details')
                ->relationship('details')          // relasi Penjualan::details()
                ->columnSpanFull()
                ->defaultItems(1)
                ->columns(4)

                // Saat Edit dibuka: isi harga/subtotal kalau 0, lalu sum total
                ->afterStateHydrated(function ($state, callable $set, callable $get) {
                    $items   = $state ?? [];
                    $changed = false;

                    foreach ($items as $idx => $row) {
                        $pid   = $row['produk_id'] ?? null;
                        $harga = (float) ($row['harga'] ?? 0);
                        $qty   = (float) ($row['qty'] ?? 0);

                        if ($pid && $harga <= 0) {
                            $hargaDb = Produk::find($pid)?->harga ?? 0;
                            $items[$idx]['harga']    = (float) $hargaDb;
                            $items[$idx]['subtotal'] = (float) $hargaDb * $qty;
                            $changed = true;
                        } elseif (!isset($row['subtotal'])) {
                            $items[$idx]['subtotal'] = $harga * $qty;
                            $changed = true;
                        }
                    }

                    if ($changed) {
                        $set('details', $items);
                    }

                    $total = array_sum(array_map(fn ($i) => (float) ($i['subtotal'] ?? 0), $items));
                    $set('total', $total);
                    $set('kembalian', max(0, (float) ($get('bayar') ?? 0) - $total));
                })

                ->schema([
                    Select::make('produk_id')
                        ->label('Produk')
                        // GANTI jika kolom nama produk berbeda (mis. 'name' / 'nama')
                        ->relationship('produk', 'nama_barang')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $harga = Produk::find($state)?->harga ?? 0;
                            $set('harga', (float) $harga);
                            $set('subtotal', (float) $harga * (float) ($get('qty') ?? 0));
                        })
                        ->required(),

                    TextInput::make('harga')
                        ->label('Harga')
                        ->numeric()
                        ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                        ->live()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $harga = (float) preg_replace('/[^\d.]/', '', (string) $state);
                            $qty   = (float) ($get('qty') ?? 0);
                            $set('subtotal', $harga * $qty);
                        }),

                    TextInput::make('qty')
                        ->label('Qty')
                        ->numeric()
                        ->default(1)
                        ->live()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $harga = (float) ($get('harga') ?? 0);
                            $set('subtotal', $harga * (float) ($state ?? 0));
                        }),

                    TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
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
