<?php

namespace App\Http\Controllers;

use App\Models\ResepDetail;
use Illuminate\Http\Request;
use App\Http\Requests\StoreResepDetailRequest;
use App\Http\Requests\UpdateResepDetailRequest;

class ResepDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index(Request $request)
    {
        // Ambil parameter search & filter dari query string
        $search      = trim((string) $request->get('q', ''));
        $resepId     = $request->get('resep_id');        // optional, future filter
        $bahanBakuId = $request->get('bahan_baku_id');   // optional, future filter

        // Query dasar: ResepDetail + relasi yang dibutuhkan di tabel
        $query = ResepDetail::query()
            ->with([
                'resep.produk',   // supaya bisa tampilkan nama produk
                'bahanBaku',      // supaya bisa tampilkan nama bahan baku
            ])
            ->orderByDesc('updated_at');

        // Filter by ID resep (kalau someday kamu pakai dropdown filter)
        if (! empty($resepId)) {
            $query->where('resep_id', $resepId);
        }

        // Filter by ID bahan baku (optional future dropdown)
        if (! empty($bahanBakuId)) {
            $query->where('bahan_baku_id', $bahanBakuId);
        }

        // Global search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';

                // Cari ke header resep + produk
                $q->whereHas('resep', function ($qr) use ($like) {
                    $qr->where('id', 'like', $like)
                        ->orWhereHas('produk', function ($qp) use ($like) {
                            $qp->where('nama_barang', 'like', $like);
                        });
                })
                // Atau cari ke nama bahan baku
                ->orWhereHas('bahanBaku', function ($qb) use ($like) {
                    $qb->where('nama_bahan', 'like', $like);
                });
            });
        }

        // Paginate + pertahankan query string (q, resep_id, bahan_baku_id)
        $items = $query->paginate(10)->withQueryString();

        return view('resep_detail.index', [
            'items'      => $items,
            'search'     => $search,
            'resepId'    => $resepId,
            'bahanBakuId'=> $bahanBakuId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreResepDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ResepDetail $resepDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ResepDetail $resepDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateResepDetailRequest $request, ResepDetail $resepDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResepDetail $resepDetail)
    {
        //
    }
}
