<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKategoriProdukRequest;
use App\Http\Requests\UpdateKategoriProdukRequest;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KategoriProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = KategoriProduk::query()->withCount('produks');

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('aktif')) {
            $query->where('aktif', $request->boolean('aktif'));
        }

        $items = $query
            ->orderBy('urutan')
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('kategori-produk.index', [
            'items' => $items,
            'search' => $search ?? '',
            'selectedAktif' => $request->input('aktif', ''),
        ]);
    }

    public function create()
    {
        return view('kategori-produk.create', [
            'row' => new KategoriProduk([
                'aktif' => true,
                'urutan' => KategoriProduk::nextUrutan(),
            ]),
            'mode' => 'create',
        ]);
    }

    public function store(StoreKategoriProdukRequest $request)
    {
        $data = $request->validated();
        $data['aktif'] = $request->boolean('aktif', true);
        $data['urutan'] = (int) ($data['urutan'] ?? KategoriProduk::nextUrutan());
        $data['slug'] = $this->generateUniqueSlug($data['nama']);

        KategoriProduk::create($data);

        return redirect()
            ->route('kategori-produk.index')
            ->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function edit(KategoriProduk $kategori_produk)
    {
        return view('kategori-produk.edit', [
            'row' => $kategori_produk,
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateKategoriProdukRequest $request, KategoriProduk $kategori_produk)
    {
        $data = $request->validated();
        $data['aktif'] = $request->boolean('aktif');
        $data['urutan'] = (int) ($data['urutan'] ?? $kategori_produk->urutan);

        $oldSlug = $kategori_produk->slug;
        $newSlug = $this->generateUniqueSlug($data['nama'], $kategori_produk->id);

        DB::transaction(function () use ($kategori_produk, $data, $oldSlug, $newSlug) {
            $kategori_produk->update(array_merge($data, ['slug' => $newSlug]));

            if ($oldSlug !== $newSlug) {
                Produk::where('kategori', $oldSlug)->update(['kategori' => $newSlug]);
            }
        });

        return redirect()
            ->route('kategori-produk.index')
            ->with('success', 'Kategori produk berhasil diperbarui.');
    }

    public function destroy(KategoriProduk $kategori_produk)
    {
        if ($kategori_produk->produks()->exists()) {
            return back()->with('error', 'Kategori masih dipakai oleh produk, jadi belum bisa dihapus.');
        }

        $kategori_produk->delete();

        return back()->with('success', 'Kategori produk berhasil dihapus.');
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '_');
        $base = $base !== '' ? $base : 'kategori';
        $slug = $base;
        $suffix = 2;

        while (
            KategoriProduk::query()
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '_' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
