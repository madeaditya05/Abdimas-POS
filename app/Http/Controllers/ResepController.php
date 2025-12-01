<?php

namespace App\Http\Controllers;

use App\Models\Resep;
use App\Models\ResepDetail;
use App\Models\Produk;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResepController extends Controller
{
    public function index(Request $request)
    {
         $search      = $request->query('q');
    $filterAktif = $request->query('aktif');
    $filterProd  = $request->query('produk');

    $query = Resep::query()
        ->join('produk', 'resep.produk_id', '=', 'produk.id')
        ->leftJoin('resep_detail', 'resep.id', '=', 'resep_detail.resep_id')
        ->selectRaw('
            resep.*,
            produk.nama_barang as produk_nama,
            COUNT(resep_detail.id) as jumlah_bahan
        ')
        ->groupBy(
            'resep.id',
            'resep.produk_id',
            'resep.is_active',
            'resep.catatan',
            'resep.created_at',
            'resep.updated_at',
            'produk.nama_barang'
        );

    if ($search) {
        $query->where('produk.nama_barang', 'like', '%' . $search . '%');
    }

    if ($filterProd) {
        $query->where('resep.produk_id', $filterProd);
    }

    if ($filterAktif !== null && $filterAktif !== '') {
        $query->where('resep.is_active', (bool) $filterAktif);
    }

    $items = $query->orderByDesc('resep.updated_at')->paginate(10)->withQueryString();

    $opsiProduk = Produk::orderBy('nama_barang')
        ->pluck('nama_barang', 'id');

    return view('resep.index', [
        'items'        => $items,
        'opsiProduk'   => $opsiProduk,
        'search'       => $search,
        'filterProd'   => $filterProd,
        'filterAktif'  => $filterAktif,
    ]);
    }

    public function create()
    {
        $opsiProduk = Produk::orderBy('nama_barang')
            ->pluck('nama_barang', 'id');

        $opsiBahan = BahanBaku::orderBy('nama_bahan')
            ->get()
            ->mapWithKeys(function ($b) {
                $label = $b->kode_bahan . ' — ' . $b->nama_bahan;
                return [$b->id => $label];
            });

        return view('resep.create', [
            'row'        => null,
            'opsiProduk' => $opsiProduk,
            'opsiBahan'  => $opsiBahan,
            'detailRows' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'produk_id'                 => ['required', 'exists:produk,id'],
            'is_active'                 => ['nullable', 'boolean'],
            'catatan'                   => ['nullable', 'string'],

            'details'                   => ['required', 'array', 'min:1'],
            'details.*.bahan_baku_id'   => ['required', 'exists:bahan_baku,id'],
            'details.*.qty_per_porsi'   => ['required', 'numeric', 'gt:0'],
            'details.*.keterangan'      => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $resep = Resep::create([
                'produk_id' => $data['produk_id'],
                'is_active' => !empty($data['is_active']),
                'catatan'   => $data['catatan'] ?? null,
            ]);

            foreach ($data['details'] as $detail) {
                ResepDetail::create([
                    'resep_id'       => $resep->id,
                    'bahan_baku_id'  => $detail['bahan_baku_id'],
                    'qty_per_porsi'  => $detail['qty_per_porsi'],
                    'keterangan'     => $detail['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('resep.index')
            ->with('success', 'Resep berhasil disimpan.');
    }

    public function edit(Resep $resep)
    {
        $opsiProduk = Produk::orderBy('nama_barang')
            ->pluck('nama_barang', 'id');

        $opsiBahan = BahanBaku::orderBy('nama_bahan')
            ->get()
            ->mapWithKeys(function ($b) {
                $label = $b->kode_bahan . ' — ' . $b->nama_bahan;
                return [$b->id => $label];
            });

        $detailRows = ResepDetail::where('resep_id', $resep->id)
            ->get(['bahan_baku_id', 'qty_per_porsi', 'keterangan']);

        return view('resep.edit', [
            'row'        => $resep,
            'opsiProduk' => $opsiProduk,
            'opsiBahan'  => $opsiBahan,
            'detailRows' => $detailRows,
        ]);
    }

    public function update(Request $request, Resep $resep)
    {
        $data = $request->validate([
            'produk_id'                 => ['required', 'exists:produk,id'],
            'is_active'                 => ['nullable', 'boolean'],
            'catatan'                   => ['nullable', 'string'],

            'details'                   => ['required', 'array', 'min:1'],
            'details.*.bahan_baku_id'   => ['required', 'exists:bahan_baku,id'],
            'details.*.qty_per_porsi'   => ['required', 'numeric', 'gt:0'],
            'details.*.keterangan'      => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $resep) {
            $resep->update([
                'produk_id' => $data['produk_id'],
                'is_active' => !empty($data['is_active']),
                'catatan'   => $data['catatan'] ?? null,
            ]);

            ResepDetail::where('resep_id', $resep->id)->delete();

            foreach ($data['details'] as $detail) {
                ResepDetail::create([
                    'resep_id'       => $resep->id,
                    'bahan_baku_id'  => $detail['bahan_baku_id'],
                    'qty_per_porsi'  => $detail['qty_per_porsi'],
                    'keterangan'     => $detail['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('resep.index')
            ->with('success', 'Resep berhasil diupdate.');
    }

    public function destroy(Resep $resep)
    {
        DB::transaction(function () use ($resep) {
            ResepDetail::where('resep_id', $resep->id)->delete();
            $resep->delete();
        });

        return redirect()
            ->route('resep.index')
            ->with('success', 'Resep berhasil dihapus.');
    }
}
