<?php

namespace App\Http\Controllers;

use App\Models\PembelianBahan;
use App\Models\BahanBaku;
use App\Http\Requests\StorePembelianBahanRequest;
use App\Http\Requests\UpdatePembelianBahanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianBahanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // search sederhana: kode / supplier
        $search = trim((string) $request->get('q', ''));

        // sort & direction
        $sort = $request->get('sort', 'tanggal');
        $dir  = $request->get('dir', 'desc');

        $allowedSort = ['tanggal', 'kode_pembelian', 'supplier_nama', 'total'];
        if (! in_array($sort, $allowedSort, true)) {
            $sort = 'tanggal';
        }

        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $query = PembelianBahan::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_pembelian', 'like', '%'.$search.'%')
                  ->orWhere('supplier_nama', 'like', '%'.$search.'%');
            });
        }

        $query->orderBy($sort, $dir);

        $items = $query->paginate(10)->withQueryString();

        return view('Pembelian-Bahan.index', [
            'items'  => $items,
            'search' => $search,
            'sort'   => $sort,
            'dir'    => $dir,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // instance kosong untuk dioper ke form
        $row = new PembelianBahan();
        $row->tanggal = now();

        // daftar bahan baku untuk dropdown di detail
        $bahanOptions = BahanBaku::orderBy('nama_bahan')->get();

        return view('Pembelian-Bahan.create', [
            'row'          => $row,
            'bahanOptions' => $bahanOptions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePembelianBahanRequest $request)
    {
        $data    = $request->validated();
        $details = $request->input('details', []);

        // bersihkan & filter baris detail
        $cleanDetails = [];
        foreach ($details as $row) {
            $bahanId = $row['bahan_baku_id'] ?? null;
            $qty     = (float) ($row['qty_beli'] ?? 0);
            $harga   = (float) ($row['harga_satuan'] ?? 0);

            if (!$bahanId || $qty <= 0) {
                continue; // skip baris kosong / tidak valid
            }

            $cleanDetails[] = [
                'bahan_baku_id' => $bahanId,
                'qty_beli'      => $qty,
                'harga_satuan'  => $harga,
                'expired_date'  => $row['expired_date'] ?? null,
                'catatan'       => $row['catatan'] ?? null,
            ];
        }

        if (count($cleanDetails) === 0) {
            return back()
                ->withInput()
                ->withErrors(['details' => 'Minimal satu baris detail dengan bahan & qty > 0.']);
        }

        DB::transaction(function () use ($request, $data, $cleanDetails, &$pembelian) {
            // header – kode & total akan diurus oleh model (booted)
            $pembelian = PembelianBahan::create([
                'tanggal'         => $data['tanggal'] ?? now(),
                'supplier_nama'   => $data['supplier_nama']   ?? null,
                'supplier_kontak' => $data['supplier_kontak'] ?? null,
                'catatan'         => $data['catatan']         ?? null,
                'user_id'         => $request->user()?->id,
            ]);

            // detail – subtotal, snapshot, mutasi stok, total & jurnal
            // semua di-handle oleh event model PembelianBahanDetail & PembelianBahan
            foreach ($cleanDetails as $d) {
                $pembelian->details()->create($d);
            }
        });

        return redirect()
            ->route('pembelian-bahan.index')
            ->with('status', 'Pembelian berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PembelianBahan $pembelianBahan)
    {
        // tidak dipakai (pakai index + edit saja)
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PembelianBahan $pembelianBahan)
    {
        $pembelianBahan->load('details');

        $bahanOptions = BahanBaku::orderBy('nama_bahan')->get();

        return view('Pembelian-Bahan.edit', [
            'row'          => $pembelianBahan,
            'bahanOptions' => $bahanOptions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePembelianBahanRequest $request, PembelianBahan $pembelianBahan)
    {
        $data    = $request->validated();
        $details = $request->input('details', []);

        $cleanDetails = [];
        foreach ($details as $row) {
            $bahanId = $row['bahan_baku_id'] ?? null;
            $qty     = (float) ($row['qty_beli'] ?? 0);
            $harga   = (float) ($row['harga_satuan'] ?? 0);

            if (!$bahanId || $qty <= 0) {
                continue;
            }

            $cleanDetails[] = [
                'bahan_baku_id' => $bahanId,
                'qty_beli'      => $qty,
                'harga_satuan'  => $harga,
                'expired_date'  => $row['expired_date'] ?? null,
                'catatan'       => $row['catatan'] ?? null,
            ];
        }

        if (count($cleanDetails) === 0) {
            return back()
                ->withInput()
                ->withErrors(['details' => 'Minimal satu baris detail dengan bahan & qty > 0.']);
        }

        DB::transaction(function () use ($request, $data, $cleanDetails, $pembelianBahan) {
            // update header
            $pembelianBahan->update([
                'tanggal'         => $data['tanggal'] ?? $pembelianBahan->tanggal,
                'supplier_nama'   => $data['supplier_nama']   ?? null,
                'supplier_kontak' => $data['supplier_kontak'] ?? null,
                'catatan'         => $data['catatan']         ?? null,
                'user_id'         => $request->user()?->id,
            ]);

            // hapus semua detail lama satu per satu (supaya event deleted kepanggil,
            // stok & jurnal ikut dibersihkan)
            $pembelianBahan->load('details');
            foreach ($pembelianBahan->details as $detail) {
                $detail->delete();
            }

            // buat ulang detail baru
            foreach ($cleanDetails as $d) {
                $pembelianBahan->details()->create($d);
            }
        });

        return redirect()
            ->route('pembelian-bahan.index')
            ->with('status', 'Pembelian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PembelianBahan $pembelianBahan)
    {
        // hapus header: relasi detail cascade + event di detail akan
        // menghapus mutasi & update total/jurnal
        $pembelianBahan->delete();

        return redirect()
            ->route('pembelian-bahan.index')
            ->with('status', 'Pembelian berhasil dihapus.');
    }
}
