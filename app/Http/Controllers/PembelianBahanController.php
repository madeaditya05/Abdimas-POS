<?php

namespace App\Http\Controllers;

use App\Models\PembelianBahan;
use App\Models\BahanBaku;
use App\Http\Requests\StorePembelianBahanRequest;
use App\Http\Requests\UpdatePembelianBahanRequest;
use App\Services\JournalPoster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembelianBahanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        // tetap dipertahankan (biar UI lama ga rusak)
        $sort = $request->get('sort', 'id');
        $dir  = $request->get('dir', 'desc');

        $query = PembelianBahan::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_pembelian', 'like', '%'.$search.'%')
                  ->orWhere('supplier_nama', 'like', '%'.$search.'%');
            });
        }

        $query
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

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
        $row = new PembelianBahan();
        $row->tanggal = now();

        $bahanOptions = BahanBaku::orderBy('nama_bahan')->get();

        return view('Pembelian-Bahan.create', [
            'row'          => $row,
            'bahanOptions' => $bahanOptions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePembelianBahanRequest $request, JournalPoster $poster)
    {
        $data    = $request->validated();
        $details = $request->input('details', []);

        $cleanDetails = [];
        foreach ($details as $row) {
            $bahanId = $row['bahan_baku_id'] ?? null;
            $qty     = (float) ($row['qty_beli'] ?? 0);
            $harga   = (float) ($row['harga_satuan'] ?? 0);

            if (!$bahanId || $qty <= 0) continue;

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

        $buktiPath = null;
        if ($request->hasFile('bukti_file')) {
            $file = $request->file('bukti_file');
            if ($file && $file->isValid()) {
                $buktiPath = $file->store('bukti-pembelian', 'public');
            }
        }

        $pembelian = DB::transaction(function () use ($request, $data, $cleanDetails, $buktiPath) {
            $pembelian = PembelianBahan::create([
                'tanggal'         => $data['tanggal'] ?? now(),
                'supplier_nama'   => $data['supplier_nama']   ?? null,
                'supplier_kontak' => $data['supplier_kontak'] ?? null,
                'catatan'         => $data['catatan']         ?? null,
                'bukti_file'      => $buktiPath,
                'user_id'         => $request->user()?->id,
            ]);

            foreach ($cleanDetails as $d) {
                $pembelian->details()->create($d);
            }

            // ================= FIX TOTAL HEADER =================
            $total = DB::table('pembelian_bahan_detail')
                ->where('pembelian_bahan_id', $pembelian->id)
                ->sum('subtotal');

            $pembelian->update([
                'total' => $total
            ]);
            // ===================================================

            return $pembelian;
        });

        $pembelian = $pembelian->fresh();
        $poster->postForPembelianBahan($pembelian);

        return redirect()
            ->route('pembelian-bahan.index')
            ->with('status', 'Pembelian berhasil disimpan.');
    }

    public function show(PembelianBahan $pembelianBahan)
    {
        abort(404);
    }

    public function edit(PembelianBahan $pembelianBahan)
    {
        $pembelianBahan->load('details');

        $bahanOptions = BahanBaku::orderBy('nama_bahan')->get();

        return view('Pembelian-Bahan.edit', [
            'row'          => $pembelianBahan,
            'bahanOptions' => $bahanOptions,
        ]);
    }

    public function update(UpdatePembelianBahanRequest $request, PembelianBahan $pembelianBahan, JournalPoster $poster)
    {
        $data    = $request->validated();
        $details = $request->input('details', []);

        $cleanDetails = [];
        foreach ($details as $row) {
            $bahanId = $row['bahan_baku_id'] ?? null;
            $qty     = (float) ($row['qty_beli'] ?? 0);
            $harga   = (float) ($row['harga_satuan'] ?? 0);

            if (!$bahanId || $qty <= 0) continue;

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

        $newBuktiPath = null;
        $hasNewUpload = $request->hasFile('bukti_file');

        if ($hasNewUpload) {
            $file = $request->file('bukti_file');
            if ($file && $file->isValid()) {
                $newBuktiPath = $file->store('bukti-pembelian', 'public');
            }
        }

        DB::transaction(function () use ($request, $data, $cleanDetails, $pembelianBahan, $hasNewUpload, $newBuktiPath) {

            if ($hasNewUpload && $newBuktiPath) {
                $old = $pembelianBahan->bukti_file;
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }

            $pembelianBahan->update([
                'tanggal'         => $data['tanggal'] ?? $pembelianBahan->tanggal,
                'supplier_nama'   => $data['supplier_nama'] ?? $pembelianBahan->supplier_nama,
                'supplier_kontak' => $data['supplier_kontak'] ?? $pembelianBahan->supplier_kontak,
                'catatan'         => $data['catatan'] ?? $pembelianBahan->catatan,
                'bukti_file'      => ($hasNewUpload && $newBuktiPath)
                                        ? $newBuktiPath
                                        : $pembelianBahan->bukti_file,
                'user_id'         => $request->user()?->id,
            ]);


            $pembelianBahan->load('details');
            foreach ($pembelianBahan->details as $detail) {
                $detail->delete();
            }

            foreach ($cleanDetails as $d) {
                $pembelianBahan->details()->create($d);
            }

            $total = DB::table('pembelian_bahan_detail')
                ->where('pembelian_bahan_id', $pembelianBahan->id)
                ->sum('subtotal');

            $pembelianBahan->update([
                'total' => $total
            ]);
        });

        $poster->postForPembelianBahan($pembelianBahan->fresh());

        return redirect()
            ->route('pembelian-bahan.index')
            ->with('status', 'Pembelian berhasil diperbarui.');
    }

    public function destroy(PembelianBahan $pembelianBahan, JournalPoster $poster)
    {
        $poster->deleteFor(PembelianBahan::class, (int) $pembelianBahan->id);

        $old = $pembelianBahan->bukti_file;
        if ($old) {
            Storage::disk('public')->delete($old);
        }

        $pembelianBahan->delete();

        return redirect()
            ->route('pembelian-bahan.index')
            ->with('status', 'Pembelian berhasil dihapus.');
    }
}
