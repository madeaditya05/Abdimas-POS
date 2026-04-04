<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UmkmFoodCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedBahanBaku();
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

    private function seedProduk(): void
    {
        $items = [
            ['nama_barang' => 'Spageti Bolognese', 'harga' => 22000, 'kategori' => 'non_coffee', 'deskripsi' => 'Pasta spageti dengan saus bolognese gurih.'],
            ['nama_barang' => 'Ayam Goreng', 'harga' => 18000, 'kategori' => 'non_coffee', 'deskripsi' => 'Ayam goreng renyah dengan bumbu sederhana.'],
            ['nama_barang' => 'Ayam Geprek', 'harga' => 20000, 'kategori' => 'non_coffee', 'deskripsi' => 'Ayam goreng crispy dengan sambal geprek.'],
            ['nama_barang' => 'Nasi Ayam Goreng', 'harga' => 23000, 'kategori' => 'non_coffee', 'deskripsi' => 'Nasi hangat dengan ayam goreng dan sambal.'],
            ['nama_barang' => 'Telur Dadar', 'harga' => 12000, 'kategori' => 'non_coffee', 'deskripsi' => 'Telur dadar rumahan untuk lauk tambahan.'],
            ['nama_barang' => 'Nasi Telur Dadar', 'harga' => 17000, 'kategori' => 'non_coffee', 'deskripsi' => 'Paket nasi dengan telur dadar.'],
            ['nama_barang' => 'Nasi Goreng', 'harga' => 20000, 'kategori' => 'non_coffee', 'deskripsi' => 'Nasi goreng favorit dengan bumbu UMKM.'],
            ['nama_barang' => 'Mie Goreng', 'harga' => 18000, 'kategori' => 'non_coffee', 'deskripsi' => 'Mie goreng sederhana dengan topping telur.'],
            ['nama_barang' => 'Kentang Goreng', 'harga' => 15000, 'kategori' => 'snack', 'deskripsi' => 'Camilan kentang goreng renyah.'],
            ['nama_barang' => 'Sosis Goreng', 'harga' => 15000, 'kategori' => 'snack', 'deskripsi' => 'Sosis goreng cocok untuk camilan dan sharing.'],
            ['nama_barang' => 'Nugget Goreng', 'harga' => 16000, 'kategori' => 'snack', 'deskripsi' => 'Nugget goreng dengan saus sambal.'],
            ['nama_barang' => 'Pisang Cokelat', 'harga' => 14000, 'kategori' => 'snack', 'deskripsi' => 'Pisang dengan topping cokelat manis.'],
            ['nama_barang' => 'Roti Bakar Cokelat Keju', 'harga' => 17000, 'kategori' => 'snack', 'deskripsi' => 'Roti bakar klasik dengan cokelat dan keju.'],
            ['nama_barang' => 'Es Teh', 'harga' => 6000, 'kategori' => 'non_coffee', 'deskripsi' => 'Minuman teh dingin manis.'],
            ['nama_barang' => 'Teh Hangat', 'harga' => 5000, 'kategori' => 'non_coffee', 'deskripsi' => 'Teh hangat untuk teman makan.'],
            ['nama_barang' => 'Es Jeruk', 'harga' => 8000, 'kategori' => 'non_coffee', 'deskripsi' => 'Minuman jeruk segar dingin.'],
            ['nama_barang' => 'Jeruk Hangat', 'harga' => 7000, 'kategori' => 'non_coffee', 'deskripsi' => 'Jeruk hangat segar dan sederhana.'],
            ['nama_barang' => 'Air Mineral', 'harga' => 4000, 'kategori' => 'non_coffee', 'deskripsi' => 'Air mineral kemasan dingin atau suhu ruang.'],
            ['nama_barang' => 'Es Kopi Susu', 'harga' => 15000, 'kategori' => 'coffee', 'deskripsi' => 'Es kopi susu UMKM yang ringan dan familiar.'],
            ['nama_barang' => 'Kopi Hitam', 'harga' => 10000, 'kategori' => 'coffee', 'deskripsi' => 'Kopi hitam panas sederhana.'],
        ];

        foreach ($items as $item) {
            $produk = Produk::where('nama_barang', $item['nama_barang'])->first();

            if (! $produk) {
                $produk = new Produk();
                $produk->kode_barang = $this->nextProductCode();
                $produk->nama_barang = $item['nama_barang'];
            }

            $produk->aktif = true;
            $produk->harga = $item['harga'];
            $produk->kategori = $item['kategori'];
            $produk->deskripsi = $item['deskripsi'];
            $produk->gambar = null;
            $produk->save();
        }
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
