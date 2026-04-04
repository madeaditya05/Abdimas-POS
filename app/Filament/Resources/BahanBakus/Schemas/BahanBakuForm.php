<?php

namespace App\Filament\Resources\BahanBakus\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;

class BahanBakuForm
{
    // ====== DAFTAR OPSI TERPUSAT (tinggal edit di sini kalau mau nambah) ======
    public const OPSI_KATEGORI = [
        'kopi'    => 'Kopi',
        'susu'    => 'Susu',
        'bumbu'   => 'Bumbu',
        'kemasan' => 'Kemasan',
        'lainnya' => 'Lainnya',
    ];

    public const OPSI_SATUAN = [
        'pcs'   => 'pcs',
        'gram'  => 'gram',
        'kg'    => 'kg',
        'ml'    => 'ml',
        'liter' => 'liter',
        'pack'  => 'pack',
        'box'   => 'box',
    ];

    public const OPSI_PENYIMPANAN = [
        'room'    => 'Ruang (room)',
        'chiller' => 'Chiller',
        'freezer' => 'Freezer',
    ];

    public const OPSI_STATUS_HALAL = [
        'halal'      => 'Halal',
        'non_halal'  => 'Non-Halal',
        'unknown'    => 'Tidak diketahui',
    ];

    public const OPSI_ALLERGEN = [
        'none'   => 'Tidak ada',
        'gluten' => 'Gluten',
        'dairy'  => 'Susu',
        'nut'    => 'Kacang',
        'soy'    => 'Kedelai',
        'egg'    => 'Telur',
    ];

    public const OPSI_KONVERSI = [1, 2, 3, 5, 10, 12, 20, 24, 50, 100, 1000];

    public const OPSI_ISI_PER_KEMASAN = [1, 2, 3, 5, 6, 10, 12, 20, 24, 25, 30, 50, 60, 100, 1000];

    public const OPSI_LEAD_TIME_HARI = [
        0, 1, 2, 3, 5, 7, 10, 14, 21, 30,
    ];

    public const OPSI_MIN_ORDER = [
        1, 5, 10, 20, 50, 100, 200, 500,
    ];

    public const OPSI_YIELD_PERSEN = [
        50 => '50 %', 60 => '60 %', 70 => '70 %', 80 => '80 %',
        85 => '85 %', 90 => '90 %', 95 => '95 %', 100 => '100 %',
    ];
    // ==========================================================================

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // === Identitas ===
            TextInput::make('kode_bahan')
                ->label('Kode Bahan')
                ->readOnly()                   // user gak bisa edit
                ->dehydrated(false)            // gak ikut submit; model yang ngisi otomatis
                ->default(fn () => \App\Models\BahanBaku::nextKode('BHK', 4)),

            TextInput::make('nama_bahan')
                ->label('Nama Bahan')
                ->required()
                ->maxLength(150),

            Select::make('kategori')
                ->label('Kategori')
                ->options(self::OPSI_KATEGORI)
                ->searchable()
                ->preload()
                ->native(false)                // tampilan nice select
                ->placeholder('Pilih kategori'),

            Toggle::make('aktif')
                ->label('Aktif')
                ->default(true),

            // === Satuan & Konversi ===
            Select::make('satuan_pakai')
                ->label('Satuan Pakai')
                ->options(self::OPSI_SATUAN)
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->placeholder('Pilih satuan'),

            Select::make('satuan_beli')
                ->label('Satuan Beli')
                ->options(self::OPSI_SATUAN)
                ->searchable()
                ->preload()
                ->native(false)
                ->placeholder('Sama dengan satuan pakai (opsional)'),

            Select::make('konversi_beli_ke_pakai')
                ->label('Konversi Beli → Pakai')
                ->options(collect(self::OPSI_KONVERSI)->mapWithKeys(fn ($v) => [$v => (string) $v])->all())
                ->default(1)
                ->native(false),

            Select::make('isi_per_kemasan')
                ->label('Isi per Kemasan')
                ->options(collect(self::OPSI_ISI_PER_KEMASAN)->mapWithKeys(fn ($v) => [$v => (string) $v])->all())
                ->native(false)
                ->placeholder('Opsional'),

            // === Penyimpanan & Shelf-life ===
            Select::make('penyimpanan')
                ->label('Penyimpanan')
                ->options(self::OPSI_PENYIMPANAN)
                ->default('room')
                ->native(false),

            Toggle::make('is_perishable')
                ->label('Mudah Rusak?')
                ->default(false),

            Select::make('masa_simpan_hari')
                ->label('Masa Simpan (hari)')
                ->options([
                    0=>'0', 3=>'3', 5=>'5', 7=>'7', 14=>'14', 30=>'30', 60=>'60', 90=>'90',
                ])
                ->native(false)
                ->placeholder('Opsional'),

            Toggle::make('kelola_expired')
                ->label('Kelola Expired')
                ->default(false),

            // === Keamanan & Kehalalan ===
            Select::make('allergen_flag')
                ->label('Alergen')
                ->options(self::OPSI_ALLERGEN)
                ->native(false)
                ->default('none'),

            Select::make('status_halal')
                ->label('Status Halal')
                ->options(self::OPSI_STATUS_HALAL)
                ->native(false)
                ->placeholder('Pilih status'),

            // === Supplier (simple) ===
            TextInput::make('default_supplier_nama')
                ->label('Supplier Default')
                ->maxLength(150),

            TextInput::make('supplier_kontak')
                ->label('Kontak Supplier')
                ->maxLength(150),

            Select::make('lead_time_hari')
                ->label('Lead Time (hari)')
                ->options(collect(self::OPSI_LEAD_TIME_HARI)->mapWithKeys(fn ($v) => [$v => (string) $v])->all())
                ->native(false)
                ->placeholder('Opsional'),

            Select::make('min_order_qty')
                ->label('Min Order Qty')
                ->options(collect(self::OPSI_MIN_ORDER)->mapWithKeys(fn ($v) => [$v => (string) $v])->all())
                ->native(false)
                ->placeholder('Opsional'),

            // === Produksi ===
            Select::make('yield_persen')
                ->label('Yield (%)')
                ->options(self::OPSI_YIELD_PERSEN) // simpan angka, tampil % rapi
                ->required()
                ->default(100)
                ->native(false),

            // === Foto (upload) ===
            FileUpload::make('foto_path')
                ->label('Foto')
                ->disk('public')
                ->directory('bahan-baku')
                ->image()
                ->imageEditor()
                ->downloadable()
                ->openable(),

            // === Catatan ===
            Textarea::make('catatan')
                ->label('Catatan')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }
}
