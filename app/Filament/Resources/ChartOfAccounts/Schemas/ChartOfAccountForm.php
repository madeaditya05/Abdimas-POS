<?php

namespace App\Filament\Resources\ChartOfAccounts\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class ChartOfAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        // Daftar nama akun umum (bisa ditambah sewaktu-waktu)
        $accountNames = [
            'Kas'                                   => 'Kas',
            'Bank'                                  => 'Bank',
            'Piutang Usaha'                         => 'Piutang Usaha',
            'Persediaan Bahan Baku'                 => 'Persediaan Bahan Baku',
            'Persediaan Bahan Penolong'            => 'Persediaan Bahan Penolong',
            'Peralatan Kedai'                       => 'Peralatan Kedai',
            'Perabot & Perlengkapan'                => 'Perabot & Perlengkapan',
            'Akumulasi Penyusutan Peralatan'        => 'Akumulasi Penyusutan Peralatan',
            'Hutang Usaha'                          => 'Hutang Usaha',
            'Hutang Gaji'                           => 'Hutang Gaji',
            'Hutang Pajak'                          => 'Hutang Pajak',
            'Pinjaman Bank'                         => 'Pinjaman Bank',
            'Modal Pemilik'                         => 'Modal Pemilik',
            'Prive'                                 => 'Prive',
            'Laba Ditahan'                          => 'Laba Ditahan',
            'Penjualan Minuman'                     => 'Penjualan Minuman',
            'Penjualan Makanan'                     => 'Penjualan Makanan',
            'Penjualan Lain-lain'                   => 'Penjualan Lain-lain',
            'Retur & Potongan Penjualan'            => 'Retur & Potongan Penjualan',
            'HPP Bahan Baku Minuman'                => 'HPP Bahan Baku Minuman',
            'HPP Bahan Baku Makanan'                => 'HPP Bahan Baku Makanan',
            'Beban Gaji Karyawan'                   => 'Beban Gaji Karyawan',
            'Beban Listrik & Air'                   => 'Beban Listrik & Air',
            'Beban Sewa'                            => 'Beban Sewa',
            'Beban Perlengkapan Kedai'              => 'Beban Perlengkapan Kedai',
            'Beban Perawatan & Servis Mesin'        => 'Beban Perawatan & Servis Mesin',
            'Beban Transportasi / Delivery'         => 'Beban Transportasi / Delivery',
            'Beban Marketing & Promosi'             => 'Beban Marketing & Promosi',
            'Beban Lain-lain'                       => 'Beban Lain-lain',
        ];

        return $schema->components([
            TextInput::make('code')
                ->label('Kode Akun')
                ->placeholder('Misal: 1001')
                ->maxLength(20)
                ->required()
                ->unique(ignoreRecord: true),

            Select::make('name')
                ->label('Nama Akun')
                ->options($accountNames)
                ->placeholder('Pilih nama akun')
                ->searchable()
                ->preload()
                ->native(false)
                ->required(),

            Select::make('type')
                ->label('Tipe Akun')
                ->options([
                    'asset'     => 'Aset',
                    'liability' => 'Liabilitas',
                    'equity'    => 'Ekuitas',
                    'revenue'   => 'Pendapatan',
                    'expense'   => 'Beban',
                ])
                ->placeholder('Pilih tipe akun')
                ->native(false)
                ->searchable()
                ->required(),

            Select::make('normal_side')
                ->label('Saldo Normal')
                ->options([
                    'debit'  => 'Debit',
                    'credit' => 'Kredit',
                ])
                ->placeholder('Pilih saldo normal')
                ->native(false)
                ->required(),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
