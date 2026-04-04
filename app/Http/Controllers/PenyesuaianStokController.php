<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\StokMutasi;
use App\Services\JournalPoster;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        return view('penyesuaian-stok.create', [
            'bahanList' => $bahanList,
            'today' => now()->toDateString(),
        ]);
    }

    public function store(Request $request, JournalPoster $poster)
    {
        $data = $request->validate([
            'bahan_baku_id' => ['required', 'exists:bahan_baku,id'],
            'mode' => ['required', 'in:plus,minus'],
            'qty' => ['required', 'numeric', 'min:0.0001'],
            'tanggal' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $deltaQty = $data['mode'] === 'plus'
            ? (float) $data['qty']
            : -1 * (float) $data['qty'];

        $mutasi = StokMutasi::adjustStock(
            bahanBakuId: (int) $data['bahan_baku_id'],
            deltaQty: $deltaQty,
            note: $data['note'] ?? null,
            tanggal: Carbon::parse($data['tanggal'])
        );

        $poster->deleteFor(StokMutasi::class, $mutasi->id);

        return redirect()
            ->route('penyesuaian-stok.index')
            ->with('success', 'Penyesuaian stok berhasil disimpan.');
    }
}
