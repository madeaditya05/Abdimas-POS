<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $product = Product::orderBy('name')->get();
        return view('product.index', compact('product'));
    }

    /** Form create */
    public function create()
    {
        return view('product.create');
    }

    /** Simpan */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|min:2|max:255',
            'sku'       => 'nullable|max:100',
            'price'     => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // checkbox
        $validated['is_active'] = $request->boolean('is_active');

        Product::create($validated);
        return redirect()->route('product.index')->with('success','Produk berhasil ditambahkan');
    }

    /** Form edit */
    public function edit(Product $product)
    {
        return view('product.edit', compact('product'));
    }

    /** Update */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'      => 'required|min:2|max:255',
            'sku'       => 'nullable|max:100',
            'price'     => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);
        return redirect()->route('product.index')->with('success','Produk berhasil diperbarui');
    }

    /** Hapus (versi GET untuk modal) */
    public function destroy($id)
    {
        $p = Product::findOrFail($id);
        $p->delete();
        return redirect()->route('product.index')->with('success','Produk berhasil dihapus');
    }
}
