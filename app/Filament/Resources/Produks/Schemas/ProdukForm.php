<?php

namespace App\Filament\Resources\Produks\Schemas;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms;

class ProdukForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_barang')
                    ->label('Kode Barang')
                    ->default(fn () => \App\Models\Produk::generateKodeBarang())
                    ->disabled()
                    ->required(),
                TextInput::make('nama_barang')
                    ->required(),
                TextInput::make('stok')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('harga')
                    ->required()
                    ->prefix('Rp')
                    // ->mask(fn ($mask) => $mask->money('Rp', ',', 2))
                    ->numeric(),
                Select::make('kategori')
                    ->label('Kategori')
                    ->options(fn (?Produk $record) => KategoriProduk::ordered()
                        ->when($record?->kategori, function ($query) use ($record) {
                            $query->where(function ($q) use ($record) {
                                $q->where('aktif', true)
                                    ->orWhere('slug', $record->kategori);
                            });
                        }, function ($query) {
                            $query->where('aktif', true);
                        })
                        ->pluck('nama', 'slug')
                        ->all())
                    ->placeholder('Pilih kategori')   // biar default-nya kosong
                    ->native(false)                   // dropdown gaya Filament (TomSelect)
                    ->searchable()                    // kalau nanti opsi makin banyak
                    ->required()                      // wajib pilih (opsional)
                    ->extraAttributes(['class' => 'capitalize']), // contoh kasih class (opsional)
                Forms\Components\FileUpload::make('gambar')
                    // ->directory('produk-images')
                    ->image()
                    ->default(null),
                Textarea::make('deskripsi')
                    ->default(null)
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
