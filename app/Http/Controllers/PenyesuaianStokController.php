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
    /**
     * Tampilkan daftar penyesuaian stok (riwayat).
     */
    public function index(Request $request)
    {
        $items = StokMutasi::query()
            ->with('bahan') // relasi ke BahanBaku
            ->orderByDesc('tanggal')
            ->paginate(15);

        return view('penyesuaian-stok.index', compact('items'));
    }

    /**
     * Form buat input penyesuaian stok (default: per bahan).
     */
    public function create()
    {
        // list bahan aktif
        $bahanList = BahanBaku::where('aktif', true)
            ->orderBy('nama_bahan')
            ->get();

        // list produk yang punya resep aktif
        $produkList = Produk::with('resepAktif.details.bahanBaku')
            ->whereHas('resepAktif')
            ->orderBy('nama_barang')
            ->get();

        $today = now()->toDateString();

        return view('penyesuaian-stok.create', [
            'bahanList'  => $bahanList,
            'produkList' => $produkList,
            'today'      => $today,
        ]);
    }

    /**
     * Alias route GET /penyesuaian-stok/menu
     * → tetap pakai view yang sama, cuma kasih hint mode=menu.
     */
    public function createMenu()
    {
        // reuse create() biar nggak dobel logic
        $bahanList = BahanBaku::where('aktif', true)
            ->orderBy('nama_bahan')
            ->get();

        $produkList = Produk::with('resepAktif.details.bahanBaku')
            ->whereHas('resepAktif')
            ->orderBy('nama_barang')
            ->get();

        $today = now()->toDateString();

        // kirim query param mode = 'menu'
        return view('penyesuaian-stok.create', [
            'bahanList'  => $bahanList,
            'produkList' => $produkList,
            'today'      => $today,
        ])->with(['mode' => 'menu']);
    }

    /**
     * Simpan penyesuaian stok:
     * - kalau input_type = 'bahan' → jalur lama
     * - kalau input_type = 'menu'  → jalur baru (per menu / resep)
     */
    public function store(Request $request, JournalPoster $poster)
    {
        $inputType = $request->input('input_type', 'bahan');

        if ($inputType === 'menu') {
            return $this->storeMenu($request, $poster);
        }

        // ================== JALUR PER BAHAN (LAMA) ==================
        $data = $request->validate([
            'input_type'    => ['required', 'in:bahan,menu'],
            'bahan_baku_id' => ['required', 'exists:bahan_baku,id'],
            'mode'          => ['required', 'in:plus,minus'],   // plus = tambah stok, minus = kurangi
            'qty'           => ['required', 'numeric', 'min:0.0001'],
            'tanggal'       => ['required', 'date'],
            'note'          => ['nullable', 'string'],
        ]);

        $deltaQty = $data['mode'] === 'plus'
            ? (float) $data['qty']     // stok bertambah
            : -1 * (float) $data['qty']; // stok berkurang

        $mutasi = StokMutasi::adjustStock(
            bahanBakuId:  (int) $data['bahan_baku_id'],
            deltaQty:     $deltaQty,
            note:         $data['note'] ?? null,
            tanggal:      Carbon::parse($data['tanggal'])
        );

        // kalau stok berkurang → auto-post HPP
        if ($deltaQty < 0) {
            $poster->postForPenyesuaianStok($mutasi);
        } else {
            // kalau stok nambah, pastikan jurnal HPP untuk mutasi ini nggak ada
            $poster->deleteFor(StokMutasi::class, $mutasi->id);
        }

        return redirect()
            ->route('penyesuaian-stok.index')
            ->with('success', 'Penyesuaian stok per bahan berhasil disimpan.');
    }

    /**
     * Jalur PER MENU (dipanggil dari store() kalau input_type = menu).
     * Mengurangi stok semua bahan berdasarkan resep aktif * qty_menu.
     */
    public function storeMenu(Request $request, JournalPoster $poster)
    {
        $data = $request->validate([
            'input_type' => ['required', 'in:bahan,menu'],
            'produk_id'  => ['required', 'exists:produk,id'],
            'qty_menu'   => ['required', 'numeric', 'min:0.0001'],
            'tanggal'    => ['required', 'date'],
            'note'       => ['nullable', 'string'],
        ]);

        $produk   = Produk::with('resepAktif.details.bahanBaku')->findOrFail($data['produk_id']);
        $resep    = $produk->resepAktif;

        if (! $resep || $resep->details->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['produk_id' => 'Produk ini belum punya resep aktif / detail bahan.']);
        }

        $qtyMenu  = (float) $data['qty_menu'];
        $tanggal  = Carbon::parse($data['tanggal']);
        $noteBase = $data['note'] ?: "Penyesuaian per menu {$produk->nama_barang} x {$qtyMenu}";

        foreach ($resep->details as $detail) {
            // berapa banyak bahan per menu * jumlah menu
            $delta = -1 * ($qtyMenu * (float) $detail->qty_per_porsi);

            if ($delta === 0.0) {
                continue;
            }

            $note = $noteBase;
            if (! empty($detail->keterangan)) {
                $note .= " ({$detail->keterangan})";
            }

            $mutasi = StokMutasi::adjustStock(
                bahanBakuId:  (int) $detail->bahan_baku_id,
                deltaQty:     $delta,
                note:         $note,
                tanggal:      $tanggal
            );

            // setiap kali stok keluar → post HPP
            $poster->postForPenyesuaianStok($mutasi);
        }

        return redirect()
            ->route('penyesuaian-stok.index')
            ->with('success', 'Penyesuaian stok per menu berhasil disimpan.');
    }
}
