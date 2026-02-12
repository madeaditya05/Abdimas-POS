<?php

namespace App\Http\Controllers;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianBahanDetailController extends Controller
{
    /**
     * LAPORAN PEMBELIAN BAHAN (AGREGAT)
     */
    public function index(Request $request)
    {
        $start = $request->get('start_date') ?? now()->toDateString();
        $end   = $request->get('end_date') ?? now()->toDateString();
        $bahanId = $request->get('bahan_id');

        $mulai = $start . ' 00:00:00';
        $akhir = $end   . ' 23:59:59';
        $bahanList = BahanBaku::orderBy('nama_bahan')->get();


        // ================= BASE QUERY =================
        $q = DB::table('pembelian_bahan_detail as d')
            ->join('pembelian_bahan as pb', 'pb.id', '=', 'd.pembelian_bahan_id')
            ->whereBetween('pb.tanggal', [$mulai, $akhir]);

        if ($bahanId) {
            $q->where('d.bahan_baku_id', $bahanId);
        }

        // ================= TABLE DATA =================
        $rows = (clone $q)
            ->selectRaw('
                DATE(pb.tanggal) as tanggal,
                d.bahan_baku_id,
                d.nama_bahan,
                SUM(d.qty_beli) qty,
                AVG(d.harga_satuan) avg_harga,
                SUM(d.subtotal) total
            ')
            ->groupBy('tanggal','d.bahan_baku_id','d.nama_bahan')
            ->orderBy('tanggal')
            ->get();

        // ================= STATISTIK =================
        $stats = (clone $q)->selectRaw('
            SUM(d.subtotal) grand_total,
            MIN(d.harga_satuan) min_harga,
            MAX(d.harga_satuan) max_harga,
            AVG(d.harga_satuan) avg_harga
        ')->first();

        $pembelianDetail = [
            'rows' => $rows,
            'stats' => [
                'grand_total' => (float)($stats->grand_total ?? 0),
                'min' => (float)($stats->min_harga ?? 0),
                'max' => (float)($stats->max_harga ?? 0),
                'avg' => (float)($stats->avg_harga ?? 0),
            ]
        ];

        return view('pembelian_bahan_detail.index', ['pembelianDetail' => $pembelianDetail, 'bahanList' => $bahanList,]);
    }
}
