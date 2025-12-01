<?php

namespace App\Http\Controllers;

use App\Models\PembelianBahanDetail;
use Illuminate\Http\Request;

class PembelianBahanDetailController extends Controller
{
    /**
     * Tampilkan daftar Pembelian Bahan Detail (view-only).
     */
    public function index(Request $request)
{
    $search = trim((string) $request->get('q', ''));
    $sort   = $request->get('sort', 'created_at');
    $dir    = $request->get('dir', 'desc');

    $allowedSort = ['created_at', 'expired_date', 'qty_beli', 'harga_satuan', 'subtotal'];
    if (! in_array($sort, $allowedSort, true)) {
        $sort = 'created_at';
    }

    $dir = $dir === 'asc' ? 'asc' : 'desc';

    $query = PembelianBahanDetail::query()->with('header'); // relasi ke PembelianBahan

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->whereHas('header', function ($qh) use ($search) {
                $qh->where('kode_pembelian', 'like', '%'.$search.'%');
            })->orWhere('nama_bahan', 'like', '%'.$search.'%');
        });
    }

    $query->orderBy($sort, $dir);

    $items = $query->paginate(10)->withQueryString();

    return view('pembelian_bahan_detail.index', [
        'items'  => $items,
        'search' => $search,
        'sort'   => $sort,
        'dir'    => $dir,
    ]);
}

}
