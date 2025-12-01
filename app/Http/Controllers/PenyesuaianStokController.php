<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanBaku;
use App\Models\StokMutasi;
use Carbon\Carbon;

class PenyesuaianStokController extends Controller
{
    /**
     * Tampilkan daftar penyesuaian stok (riwayat).
     */
    public function index(Request $request)
    {
        $items = StokMutasi::query()
            ->with('bahan') // relasi ke BahanBaku
            // ->where('sumber_type', PenyesuaianStok::class) // ⬅ filter khusus penyesuaian
            ->orderByDesc('tanggal')
            ->paginate(15);

        return view('penyesuaian-stok.index', compact('items'));
    }

    /**
     * Form buat input penyesuaian stok baru.
     */
    public function create()
    {
        // ambil bahan aktif saja biar listnya nggak kebanyakan
        $bahanList = BahanBaku::where('aktif', true)
            ->orderBy('nama_bahan')
            ->get();

        $today = now()->toDateString();

        return view('penyesuaian-stok.create', compact('bahanList', 'today'));
    }

    /**
     * Simpan penyesuaian stok.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'bahan_baku_id' => ['required', 'exists:bahan_baku,id'],
            'mode'          => ['required', 'in:plus,minus'],   // plus = tambah stok, minus = kurangi
            'qty'           => ['required', 'numeric', 'min:0.0001'],
            'tanggal'       => ['required', 'date'],
            'note'          => ['nullable', 'string'],
        ]);

        $deltaQty = $data['mode'] === 'plus'
            ? (float) $data['qty']     // stok bertambah
            : -1 * (float) $data['qty']; // stok berkurang

        StokMutasi::adjustStock(
            bahanBakuId:  (int) $data['bahan_baku_id'],
            deltaQty:     $deltaQty,
            note:         $data['note'] ?? null,
            tanggal:      Carbon::parse($data['tanggal'])
        );

        return redirect()
            ->route('penyesuaian-stok.index')
            ->with('success', 'Penyesuaian stok berhasil disimpan.');
    }
}
