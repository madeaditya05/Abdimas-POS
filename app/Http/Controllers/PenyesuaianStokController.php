<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\StokMutasi;
use Carbon\Carbon;
use App\Services\JournalPoster;

class PenyesuaianStokController extends Controller
{
    public function index(Request $request)
    {
        $items = StokMutasi::query()
            ->with('bahan')
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('penyesuaian-stok.index', compact('items'));
    }

    public function create()
    {
        $bahanList = BahanBaku::where('aktif', true)
            ->orderBy('nama_bahan')
            ->get();

        $produkList = Produk::with('resepAktif.details.bahanBaku')
            ->whereHas('resepAktif')
            ->orderBy('nama_barang')
            ->get();

        return view('penyesuaian-stok.create', [
            'bahanList'  => $bahanList,
            'produkList' => $produkList,
            'today'      => now()->toDateString(),
        ]);
    }

    public function createMenu()
    {
        $produkList = Produk::with('resepAktif.details.bahanBaku')
            ->whereHas('resepAktif')
            ->orderBy('nama_barang')
            ->get();

        return view('penyesuaian-stok.create-menu', [
            'produkList' => $produkList,
            'today'      => now()->toDateString(),
        ]);
    }

    public function store(Request $request, JournalPoster $poster)
    {
        if ($request->input('input_type') === 'menu') {
            return $this->storeMenu($request, $poster);
        }

        $data = $request->validate([
            'bahan_baku_id' => ['required', 'exists:bahan_baku,id'],
            'mode'          => ['required', 'in:plus,minus'],
            'qty'           => ['required', 'numeric', 'min:0.0001'],
            'tanggal'       => ['required', 'date'],
            'note'          => ['nullable', 'string'],
        ]);

        $deltaQty = $data['mode'] === 'plus'
            ? (float) $data['qty']
            : -1 * (float) $data['qty'];

        $mutasi = StokMutasi::adjustStock(
            bahanBakuId: (int) $data['bahan_baku_id'],
            deltaQty:    $deltaQty,
            note:        $data['note'] ?? null,
            tanggal:     Carbon::parse($data['tanggal'])
        );

        // PERIODIK: pastikan tidak ada jurnal HPP realtime
        $poster->deleteFor(StokMutasi::class, $mutasi->id);

        return redirect()
            ->route('penyesuaian-stok.index')
            ->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    public function storeMenu(Request $request, JournalPoster $poster)
    {
        $data = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'qty_menu'  => ['required', 'integer', 'min:1'],
            'tanggal'   => ['required', 'date'],
            'note'      => ['nullable', 'string'],
        ]);

        $produk = Produk::with('resepAktif.details.bahanBaku')->findOrFail($data['produk_id']);
        $resep  = $produk->resepAktif;

        if (! $resep || $resep->details->isEmpty()) {
            return back()->withErrors(['produk_id' => 'Produk belum punya resep aktif.']);
        }

        $qtyMenu = (int) $data['qty_menu'];
        $tanggal = Carbon::parse($data['tanggal']);
        $noteBase = $data['note'] ?: "Penyesuaian per menu {$produk->nama_barang} x {$qtyMenu}";

        foreach ($resep->details as $detail) {
            $delta = -1 * ($qtyMenu * (float) $detail->qty_per_porsi);
            if ($delta == 0.0) continue;

            $mutasi = StokMutasi::adjustStock(
                bahanBakuId: (int) $detail->bahan_baku_id,
                deltaQty:    $delta,
                note:        $noteBase,
                tanggal:     $tanggal
            );

            // PERIODIK: tidak posting jurnal HPP realtime
            $poster->deleteFor(StokMutasi::class, $mutasi->id);
        }

        return redirect()
            ->route('penyesuaian-stok.index')
            ->with('success', 'Penyesuaian stok per menu berhasil disimpan.');
    }
}
