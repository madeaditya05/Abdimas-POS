<?php

namespace App\Http\Controllers;

use App\Models\StokMutasi;
use Illuminate\Http\Request;

class StokMutasiController extends Controller
{
    /**
     * Halaman list mutasi stok (view only).
     */
    public function index(Request $request)
    {
        $search       = $request->input('q', '');
        $selectedTipe = $request->input('tipe', '');
        $sort         = $request->input('sort', 'tanggal');
        $dir          = $request->input('dir',  'desc');

        // filter tanggal (opsional)
        $from = $request->input('from', '');
        $to   = $request->input('to',   '');

        $query = StokMutasi::with('bahan');

        // Search berdasarkan nama / kode bahan
        if ($search !== '') {
            $query->whereHas('bahan', function ($q) use ($search) {
                $q->where('nama_bahan', 'like', "%{$search}%")
                  ->orWhere('kode_bahan', 'like', "%{$search}%");
            });
        }

        // Filter tipe IN / OUT / ADJ
        if ($selectedTipe !== '') {
            $query->where('tipe', $selectedTipe);
        }

        // Filter tanggal
        if ($from !== '') {
            $query->whereDate('tanggal', '>=', $from);
        }
        if ($to !== '') {
            $query->whereDate('tanggal', '<=', $to);
        }

        // Sort sederhana, default tanggal desc
        if (! in_array($sort, ['tanggal', 'qty'])) {
            $sort = 'tanggal';
        }
        if (! in_array($dir, ['asc', 'desc'])) {
            $dir = 'desc';
        }

        // Primary sort
        $query->orderBy($sort, $dir);

        /**
         * ✅ Tie-breaker penting:
         * Karena tanggal kamu biasanya "date" (jam 00:00), banyak row jadi "kembar".
         * Maka kalau sort = tanggal, kita urutkan lagi berdasarkan created_at + id,
         * supaya data yang BARU DIBUAT muncul paling atas.
         */
        if ($sort === 'tanggal') {
            $query->orderByDesc('created_at')
                  ->orderByDesc('id');
        }

        // (opsional) kalau sort qty, biar stabil juga
        if ($sort === 'qty') {
            $query->orderByDesc('tanggal')
                  ->orderByDesc('created_at')
                  ->orderByDesc('id');
        }

        $items = $query->paginate(15)->withQueryString();

        return view('Mutasi-Stok.index', [
            'items'        => $items,
            'search'       => $search,
            'selectedTipe' => $selectedTipe,
            'sort'         => $sort,
            'dir'          => $dir,
            'from'         => $from,
            'to'           => $to,
        ]);
    }
}
