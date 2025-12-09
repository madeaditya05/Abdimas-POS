<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Daftar produk + search + filter.
     */
    public function index(Request $request)
    {
        $query = Produk::query();

        // Search berdasarkan kode_barang atau nama_barang
        $search   = $request->input('search');
        $kategori = $request->input('kategori');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_barang', 'like', '%' . $search . '%')
                  ->orWhere('nama_barang', 'like', '%' . $search . '%');
            });
        }

        // Filter kategori (coffee / non_coffee / snack)
        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        // Urutkan berdasarkan kode
        $query->orderBy('kode_barang', 'asc');

        $produks = $query->paginate(15)->withQueryString();

        $kategoriOptions = $this->kategoriOptions();

        return view('produk.index', [
            'produks'          => $produks,
            'kategoriOptions'  => $kategoriOptions,
            'search'           => $search,
            'selectedKategori' => $kategori,
        ]);
    }

    /**
     * Form create produk.
     */
    public function create()
    {
        $produk = new Produk([
            'kode_barang' => Produk::generateKodeBarang(),
            'stok'        => 0,
        ]);

        $kategoriOptions = $this->kategoriOptions();

        return view('produk.create', [
            'produk'          => $produk,
            'kategoriOptions' => $kategoriOptions,
            'mode'            => 'create',
        ]);
    }

    /**
     * Simpan produk baru.
     */
    public function store(StoreProdukRequest $request)
    {
        $validated = $request->validated();

        // Generate kode barang di backend
        $validated['kode_barang'] = Produk::generateKodeBarang();

        // Handle upload gambar
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('produk', 'public');
            $validated['gambar'] = $path;
        }

        Produk::create($validated);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Form edit produk.
     */
    public function edit(Produk $produk)
    {
        $kategoriOptions = $this->kategoriOptions();

        return view('produk.edit', [
            'produk'          => $produk,
            'kategoriOptions' => $kategoriOptions,
            'mode'            => 'edit',
        ]);
    }

    /**
     * Update produk.
     */
    public function update(UpdateProdukRequest $request, Produk $produk)
    {
        $validated = $request->validated();

        // Jangan ubah kode_barang di update
        $validated['kode_barang'] = $produk->kode_barang;

        // Kalau ada gambar baru, hapus yang lama lalu simpan yang baru
        if ($request->hasFile('gambar')) {
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }

            $path = $request->file('gambar')->store('produk', 'public');
            $validated['gambar'] = $path;
        } else {
            // kalau tidak upload baru, pakai path lama
            $validated['gambar'] = $produk->gambar;
        }

        $produk->update($validated);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Hapus produk.
     */
    public function destroy(Produk $produk)
    {
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $produk->delete();

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Helper: opsi kategori biar gak duplikat dimana-mana.
     */
    protected function kategoriOptions(): array
    {
        return [
            'coffee'     => 'Coffee',
            'non_coffee' => 'Non Coffee',
            'snack'      => 'Snack',
        ];
    }
}
