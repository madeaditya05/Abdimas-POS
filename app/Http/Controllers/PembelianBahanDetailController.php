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
        $data = $this->getReportData($request);
        $bahanList = BahanBaku::orderBy('nama_bahan')->get();

        return view('pembelian_bahan_detail.index', [
            'pembelianDetail' => $data['pembelianDetail'],
            'bahanList' => $bahanList,
        ]);
    }

    public function pdf(Request $request)
    {
        $data = $this->getReportData($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.pembelian_bahan_detail_pdf', [
            'pembelianDetail' => $data['pembelianDetail'],
            'start' => $data['start'],
            'end' => $data['end'],
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Laporan-Pembelian-Bahan_{$data['start']}_sd_{$data['end']}.pdf");
    }

    public function excel(Request $request)
    {
        $data = $this->getReportData($request);
        $html = view('reports.excel.pembelian_bahan_detail_excel', [
            'pembelianDetail' => $data['pembelianDetail'],
            'start' => $data['start'],
            'end' => $data['end'],
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"Laporan-Pembelian-Bahan_{$data['start']}_sd_{$data['end']}.xls\"");
    }

    private function getReportData(Request $request)
    {
        $start = $request->get('start_date') ?? now()->toDateString();
        $end   = $request->get('end_date') ?? now()->toDateString();
        $bahanId = $request->get('bahan_id');

        $mulai = $start . ' 00:00:00';
        $akhir = $end   . ' 23:59:59';

        $q = DB::table('pembelian_bahan_detail as d')
            ->join('pembelian_bahan as pb', 'pb.id', '=', 'd.pembelian_bahan_id')
            ->whereBetween('pb.tanggal', [$mulai, $akhir]);

        if ($bahanId) {
            $q->where('d.bahan_baku_id', $bahanId);
        }

        $rows = (clone $q)
            ->selectRaw('
                DATE(pb.tanggal) as tanggal,
                d.bahan_baku_id,
                d.nama_bahan,
                d.satuan_beli,
                SUM(d.qty_beli) qty,
                CASE
                    WHEN SUM(d.qty_beli) > 0
                        THEN SUM(d.subtotal) / SUM(d.qty_beli)
                    ELSE 0
                END avg_harga,
                SUM(d.subtotal) total
            ')
            ->groupBy('tanggal','d.bahan_baku_id','d.nama_bahan','d.satuan_beli')
            ->orderBy('tanggal')
            ->get();

        $stats = (clone $q)->selectRaw('
            SUM(d.subtotal) grand_total,
            MIN(d.harga_satuan) min_harga,
            MAX(d.harga_satuan) max_harga,
            CASE
                WHEN SUM(d.qty_beli) > 0
                    THEN SUM(d.subtotal) / SUM(d.qty_beli)
                ELSE 0
            END avg_harga
        ')->first();

        return [
            'pembelianDetail' => [
                'rows' => $rows,
                'stats' => [
                    'grand_total' => (float)($stats->grand_total ?? 0),
                    'min' => (float)($stats->min_harga ?? 0),
                    'max' => (float)($stats->max_harga ?? 0),
                    'avg' => (float)($stats->avg_harga ?? 0),
                ]
            ],
            'start' => $start,
            'end' => $end,
        ];
    }
}
