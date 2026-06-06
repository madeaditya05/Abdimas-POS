<?php

namespace App\Services;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProdukCatalogSyncService
{
    public function syncFromStorage(): int
    {
        return DB::transaction(function () {
            $this->syncKategoriProduk();

            $count = 0;
            foreach ($this->produkFromStorage() as $item) {
                $produk = Produk::where('nama_barang', $item['nama_barang'])->first();
                $isNew = false;

                if (! $produk) {
                    $isNew = true;
                    $produk = new Produk();
                    $produk->kode_barang = $this->nextProductCode();
                    $produk->nama_barang = $item['nama_barang'];
                    $produk->aktif = true;
                }

                $produk->harga = ((int) $produk->harga > 0) ? $produk->harga : $item['harga'];
                $produk->kategori = ($isNew || empty($produk->kategori)) ? $item['kategori'] : $produk->kategori;
                $produk->deskripsi = $produk->deskripsi ?: $item['deskripsi'];
                $produk->gambar = $item['gambar'];
                $produk->save();

                $count++;
            }

            return $count;
        });
    }

    public function produkFromStorage(): array
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

    private function syncKategoriProduk(): void
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
