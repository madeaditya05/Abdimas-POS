<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UmkmFoodCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedBahanBaku();
            $this->seedKategoriProduk();
            $this->seedProduk();
        });
    }

    private function seedBahanBaku(): void
    {
        $items = [
            [
                'nama_bahan' => 'Beras',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Toko Sembako Makmur',
                'min_order_qty' => 5,
                'catatan' => 'Bahan utama untuk menu nasi.',
            ],
            [
                'nama_bahan' => 'Ayam Potong',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'pcs',
                'satuan_beli' => 'pcs',
                'default_supplier_nama' => 'Supplier Ayam Segar',
                'is_perishable' => true,
                'penyimpanan' => 'chiller',
                'masa_simpan_hari' => 2,
                'min_order_qty' => 10,
            ],
            [
                'nama_bahan' => 'Telur Ayam',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'butir',
                'satuan_beli' => 'butir',
                'default_supplier_nama' => 'Agen Telur Jaya',
                'is_perishable' => true,
                'penyimpanan' => 'room',
                'masa_simpan_hari' => 7,
                'min_order_qty' => 30,
            ],
            [
                'nama_bahan' => 'Tepung Terigu',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Toko Sembako Makmur',
                'min_order_qty' => 2,
            ],
            [
                'nama_bahan' => 'Tepung Bumbu',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Distributor Bumbu Nusantara',
                'min_order_qty' => 2,
            ],
            [
                'nama_bahan' => 'Tepung Roti',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Bumbu Nusantara',
                'min_order_qty' => 5,
            ],
            [
                'nama_bahan' => 'Minyak Goreng',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'liter',
                'satuan_beli' => 'liter',
                'default_supplier_nama' => 'Toko Sembako Makmur',
                'min_order_qty' => 5,
            ],
            [
                'nama_bahan' => 'Gula Pasir',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Toko Sembako Makmur',
                'min_order_qty' => 2,
            ],
            [
                'nama_bahan' => 'Garam',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Toko Sembako Makmur',
                'min_order_qty' => 1,
            ],
            [
                'nama_bahan' => 'Kaldu Bubuk',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Bumbu Nusantara',
                'min_order_qty' => 5,
            ],
            [
                'nama_bahan' => 'Merica Bubuk',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Bumbu Nusantara',
                'min_order_qty' => 3,
            ],
            [
                'nama_bahan' => 'Bawang Putih',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Pasar Induk',
                'min_order_qty' => 1,
            ],
            [
                'nama_bahan' => 'Bawang Merah',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Pasar Induk',
                'min_order_qty' => 1,
            ],
            [
                'nama_bahan' => 'Cabai Merah',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Pasar Induk',
                'min_order_qty' => 1,
            ],
            [
                'nama_bahan' => 'Cabai Rawit',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Pasar Induk',
                'min_order_qty' => 1,
            ],
            [
                'nama_bahan' => 'Daun Teh',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'box',
                'satuan_beli' => 'box',
                'default_supplier_nama' => 'Distributor Minuman',
                'min_order_qty' => 3,
            ],
            [
                'nama_bahan' => 'Jeruk Peras',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Pasar Buah Segar',
                'is_perishable' => true,
                'penyimpanan' => 'chiller',
                'masa_simpan_hari' => 4,
                'min_order_qty' => 2,
            ],
            [
                'nama_bahan' => 'Air Mineral Galon',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'liter',
                'satuan_beli' => 'liter',
                'default_supplier_nama' => 'Depot Air Minum',
                'min_order_qty' => 19,
            ],
            [
                'nama_bahan' => 'Es Batu',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Supplier Es Batu',
                'is_perishable' => true,
                'penyimpanan' => 'freezer',
                'masa_simpan_hari' => 2,
                'min_order_qty' => 5,
            ],
            [
                'nama_bahan' => 'Mi Spaghetti',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Bahan Makanan',
                'min_order_qty' => 5,
            ],
            [
                'nama_bahan' => 'Saus Bolognese',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Bahan Makanan',
                'min_order_qty' => 5,
            ],
            [
                'nama_bahan' => 'Saus Sambal',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'botol',
                'satuan_beli' => 'botol',
                'default_supplier_nama' => 'Distributor Saus',
                'min_order_qty' => 3,
            ],
            [
                'nama_bahan' => 'Saus Tomat',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'botol',
                'satuan_beli' => 'botol',
                'default_supplier_nama' => 'Distributor Saus',
                'min_order_qty' => 3,
            ],
            [
                'nama_bahan' => 'Kecap Manis',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'botol',
                'satuan_beli' => 'botol',
                'default_supplier_nama' => 'Distributor Saus',
                'min_order_qty' => 3,
            ],
            [
                'nama_bahan' => 'Mayones',
                'kategori' => 'bumbu',
                'satuan_pakai' => 'botol',
                'satuan_beli' => 'botol',
                'default_supplier_nama' => 'Distributor Saus',
                'min_order_qty' => 2,
            ],
            [
                'nama_bahan' => 'Margarin',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Bakery',
                'min_order_qty' => 5,
            ],
            [
                'nama_bahan' => 'Pisang',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Pasar Buah Segar',
                'is_perishable' => true,
                'penyimpanan' => 'room',
                'masa_simpan_hari' => 4,
                'min_order_qty' => 2,
            ],
            [
                'nama_bahan' => 'Meses Cokelat',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Bakery',
                'min_order_qty' => 4,
            ],
            [
                'nama_bahan' => 'Keju Cheddar',
                'kategori' => 'susu',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Dairy',
                'penyimpanan' => 'chiller',
                'is_perishable' => true,
                'masa_simpan_hari' => 10,
                'min_order_qty' => 3,
            ],
            [
                'nama_bahan' => 'Roti Tawar',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Bakery',
                'is_perishable' => true,
                'masa_simpan_hari' => 5,
                'min_order_qty' => 4,
            ],
            [
                'nama_bahan' => 'Kentang',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'kg',
                'satuan_beli' => 'kg',
                'default_supplier_nama' => 'Pasar Induk',
                'min_order_qty' => 3,
            ],
            [
                'nama_bahan' => 'Sosis',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'pcs',
                'satuan_beli' => 'pcs',
                'default_supplier_nama' => 'Distributor Frozen Food',
                'penyimpanan' => 'freezer',
                'is_perishable' => true,
                'masa_simpan_hari' => 30,
                'min_order_qty' => 10,
            ],
            [
                'nama_bahan' => 'Nugget',
                'kategori' => 'lainnya',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Frozen Food',
                'penyimpanan' => 'freezer',
                'is_perishable' => true,
                'masa_simpan_hari' => 30,
                'min_order_qty' => 5,
            ],
            [
                'nama_bahan' => 'Kopi Bubuk',
                'kategori' => 'kopi',
                'satuan_pakai' => 'pack',
                'satuan_beli' => 'pack',
                'default_supplier_nama' => 'Distributor Minuman',
                'min_order_qty' => 5,
            ],
            [
                'nama_bahan' => 'Susu Kental Manis',
                'kategori' => 'susu',
                'satuan_pakai' => 'box',
                'satuan_beli' => 'box',
                'default_supplier_nama' => 'Distributor Dairy',
                'min_order_qty' => 3,
            ],
        ];

        foreach ($items as $item) {
            $payload = array_merge([
                'aktif' => true,
                'konversi_beli_ke_pakai' => 1,
                'isi_per_kemasan' => 1,
                'penyimpanan' => 'room',
                'is_perishable' => false,
                'masa_simpan_hari' => null,
                'kelola_expired' => false,
                'allergen_flag' => 'none',
                'status_halal' => 'halal',
                'supplier_kontak' => null,
                'lead_time_hari' => 1,
                'min_order_qty' => 1,
                'yield_persen' => 100,
                'dipakai_di_resep' => false,
                'foto_path' => null,
                'catatan' => null,
            ], $item);

            BahanBaku::updateOrCreate(
                ['nama_bahan' => $payload['nama_bahan']],
                $payload
            );
        }
    }

    private function seedKategoriProduk(): void
    {
        $items = [
            ['nama' => 'Pasta', 'slug' => 'pasta', 'urutan' => 1, 'deskripsi' => 'Menu pasta dan spaghetti.'],
            ['nama' => 'Katsu', 'slug' => 'katsu', 'urutan' => 2, 'deskripsi' => 'Menu ayam katsu dan variasinya.'],
            ['nama' => 'Nasi', 'slug' => 'nasi', 'urutan' => 3, 'deskripsi' => 'Menu nasi, nasi goreng, dan rice bowl.'],
            ['nama' => 'Mie', 'slug' => 'mie', 'urutan' => 4, 'deskripsi' => 'Menu mie dan indomie.'],
            ['nama' => 'Snack', 'slug' => 'snack', 'urutan' => 5, 'deskripsi' => 'Menu camilan.'],
            ['nama' => 'Minuman', 'slug' => 'minuman', 'urutan' => 6, 'deskripsi' => 'Menu minuman non-kopi.'],
            ['nama' => 'Coffee', 'slug' => 'coffee', 'urutan' => 7, 'deskripsi' => 'Menu minuman berbasis kopi.'],
            ['nama' => 'Non Coffee', 'slug' => 'non_coffee', 'urutan' => 8, 'deskripsi' => 'Kategori umum non-kopi.'],
        ];

        foreach ($items as $item) {
            KategoriProduk::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'nama' => $item['nama'],
                    'urutan' => $item['urutan'],
                    'aktif' => true,
                    'deskripsi' => $item['deskripsi'],
                ]
            );
        }
    }

    private function seedProduk(): void
    {
        $items = $this->produkFromStorage();

        if ($items === []) {
            return;
        }

        foreach ($items as $item) {
            $produk = Produk::where('nama_barang', $item['nama_barang'])->first();

            if (! $produk) {
                $produk = new Produk();
                $produk->kode_barang = $this->nextProductCode();
                $produk->nama_barang = $item['nama_barang'];
            }

            $produk->aktif = true;
            $produk->harga = ((int) $produk->harga > 0) ? $produk->harga : $item['harga'];
            $produk->kategori = $item['kategori'];
            $produk->deskripsi = $item['deskripsi'];
            $produk->gambar = $item['gambar'];
            $produk->save();
        }
    }

    private function produkFromStorage(): array
    {
        $files = collect(Storage::disk('public')->files('produk'))
            ->filter(fn (string $path) => $this->isProdukImage($path))
            ->sortBy(fn (string $path) => Str::lower($this->menuNameFromPath($path)))
            ->unique(fn (string $path) => Str::lower($this->menuNameFromPath($path)))
            ->values();

        return $files
            ->map(function (string $path) {
                $nama = $this->menuNameFromPath($path);
                $kategori = $this->kategoriForProduk($nama);

                return [
                    'nama_barang' => $nama,
                    'harga' => $this->hargaForProduk($nama, $kategori),
                    'kategori' => $kategori,
                    'gambar' => $path,
                    'deskripsi' => 'Menu ' . $nama . '.',
                ];
            })
            ->all();
    }

    private function isProdukImage(string $path): bool
    {
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
        $name = Str::lower(pathinfo($path, PATHINFO_FILENAME));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
            && ! Str::contains($name, 'logo');
    }

    private function menuNameFromPath(string $path): string
    {
        $name = Str::of(pathinfo($path, PATHINFO_FILENAME))
            ->replace(['_', '-'], ' ')
            ->squish()
            ->value();

        if ($name === Str::lower($name)) {
            return Str::of($name)->title()->value();
        }

        return $name;
    }

    private function kategoriForProduk(string $nama): string
    {
        $name = Str::lower($nama);

        if (Str::contains($name, ['coffee', 'kopi'])) {
            return 'coffee';
        }

        if (Str::contains($name, ['spaghetti', 'spghetti', 'pasta', 'aglio', 'alfredo', 'bolognese', 'carbonara', 'macaroni', 'mac and cheese', 'mushrom', 'negigoma'])) {
            return 'pasta';
        }

        if (Str::startsWith($name, 'nasi ') || Str::contains($name, ['nasgor', 'nagor', 'ricebowl', 'nasimi'])) {
            return 'nasi';
        }

        if (Str::contains($name, ['indomi', 'indomie', ' mie'])) {
            return 'mie';
        }

        if (Str::contains($name, ['katsu'])) {
            return 'katsu';
        }

        if (Str::contains($name, ['chickenbites'])) {
            return 'snack';
        }

        if (preg_match('/\b(es|teh|tea|susu|milk|milkshake|milo|nutrisari|yakult|smoothies|chocomilk)\b/u', $name) === 1) {
            return 'minuman';
        }

        return 'non_coffee';
    }

    private function hargaForProduk(string $nama, string $kategori): int
    {
        $name = Str::lower($nama);

        if ($kategori === 'coffee') {
            return 12000;
        }

        if ($kategori === 'minuman') {
            if (Str::contains($name, ['teh manis'])) return 5000;
            if (Str::contains($name, ['nutrisari', 'susu', 'lemon tea'])) return 7000;
            if (Str::contains($name, ['milo', 'milkshake', 'smoothies', 'yakult', 'chocomilk'])) return 12000;

            return 8000;
        }

        if ($kategori === 'pasta') {
            if (Str::contains($name, ['katsu'])) return 25000;
            if (Str::contains($name, ['macaroni', 'mac and cheese'])) return 18000;

            return 20000;
        }

        if ($kategori === 'nasi') {
            if (Str::contains($name, ['katsu', 'chickenbites', 'ricebowl'])) return 22000;
            if (Str::contains($name, ['telur'])) return 15000;
            if (Str::contains($name, ['nasgor', 'goreng'])) return 18000;

            return 17000;
        }

        if ($kategori === 'mie') {
            return Str::contains($name, ['katsu']) ? 20000 : 15000;
        }

        if ($kategori === 'katsu') {
            return 18000;
        }

        if ($kategori === 'snack') {
            return 15000;
        }

        return 15000;
    }

    private function nextProductCode(): string
    {
        $lastCode = Produk::query()
            ->where('kode_barang', 'like', 'CF%')
            ->orderByDesc('kode_barang')
            ->value('kode_barang');

        $next = 1;
        if ($lastCode && preg_match('/^CF(\d{4,})$/', $lastCode, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        return 'CF' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
