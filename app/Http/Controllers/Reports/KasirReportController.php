<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class KasirReportController extends Controller
{
    public function index(Request $r)
    {
        [$start, $end] = $this->range($r);
        $sections = $this->sections($r);

        // =====================================================================
        // REKAP PER PRODUK
        // =====================================================================
        $items = DB::table('penjualan as pjl')
            ->join('penjualan_detail as d', 'd.penjualan_id', '=', 'pjl.id')
            ->join('produk as pr', 'pr.id', '=', 'd.produk_id')
            ->join('payment as pay', 'pay.penjualan_id', '=', 'pjl.id')
            ->where('pay.transaction_status', 'settlement')
            ->whereBetween('pjl.created_at', [$start, $end])
            ->selectRaw('
                pr.id as product_id,
                pr.nama_barang as name,
                SUM(d.qty) AS qty,
                SUM(d.qty * d.harga) AS total
            ')
            ->groupBy('pr.id', 'pr.nama_barang')
            ->orderByDesc('total')
            ->get();

        // =====================================================================
        // REKAP PER METODE PEMBAYARAN
        // =====================================================================
        $payments = DB::table('payment as pay')
            ->join('penjualan as pjl', 'pjl.id', '=', 'pay.penjualan_id')
            ->where('pay.transaction_status', 'settlement')
            ->whereBetween('pay.paid_at', [$start, $end])
            ->selectRaw('pay.pg_payment_type, COUNT(*) trx, SUM(pay.gross_amount) total')
            ->groupBy('pay.pg_payment_type')
            ->orderByDesc('total')
            ->get();

        // =====================================================================
        // TUNAI VS NON TUNAI
        // =====================================================================
        $payUnified = DB::table('payment as pay')
            ->join('penjualan as pjl', 'pjl.id', '=', 'pay.penjualan_id')
            ->where('pay.transaction_status', 'settlement')
            ->whereBetween('pay.paid_at', [$start, $end])
            ->selectRaw("
                CASE 
                    WHEN pay.pg_payment_type='cash' THEN 'Tunai' 
                    ELSE 'Non Tunai' 
                END as kategori,
                COUNT(*) trx,
                SUM(pay.gross_amount) total
            ")
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get();

        // =====================================================================
        // JURNAL
        // =====================================================================
        $journal = DB::table('journal_entry as je')
            ->join('journal_line as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
            ->whereBetween('je.date', [$start, $end])
            ->orderBy('je.date')
            ->orderBy('je.entry_no')
            ->selectRaw('
                je.date,
                je.entry_no,
                je.ref_no,
                je.memo as entry_memo,
                jl.memo as line_memo,
                coa.code,
                coa.name,
                coa.normal_side,
                jl.debit,
                jl.credit
            ')
            ->get();

        // =====================================================================
        // BUKU BESAR (LEDGER)
        // =====================================================================
        $ledger = [];
        foreach ($journal as $row) {
            $key    = $row->code . ' - ' . $row->name;
            $normal = strtoupper($row->normal_side); // 'debit' / 'credit' → 'DEBIT' / 'CREDIT'

            if (!isset($ledger[$key])) {
                $ledger[$key] = [
                    'normal'        => $normal,
                    'rows'          => [],
                    'total_debit'   => 0,
                    'total_credit'  => 0,
                    'balance'       => 0,
                ];
            }

            // DEBIT normal → saldo naik kalau debit
            // KREDIT normal → saldo naik kalau kredit
            $change = ($normal === 'DEBIT')
                ? ($row->debit - $row->credit)
                : ($row->credit - $row->debit);

            $ledger[$key]['balance']      += $change;
            $ledger[$key]['total_debit']  += (float) $row->debit;
            $ledger[$key]['total_credit'] += (float) $row->credit;

            $ledger[$key]['rows'][] = [
                'date'   => $row->date,
                'entry'  => $row->entry_no,
                'ref'    => $row->ref_no,
                // Pakai memo baris (lawan akun). Kalau kosong, pakai memo header.
                'memo'   => $row->line_memo ?: $row->entry_memo,
                'debit'  => (float) $row->debit,
                'credit' => (float) $row->credit,
                'saldo'  => $ledger[$key]['balance'],
            ];
        }

        $meta = compact('start', 'end');

        return view('reports.kasir_rekap', compact(
            'items', 'payments', 'payUnified', 'journal', 'ledger', 'meta', 'sections'
        ));
    }

    // =========================================================================
    // PDF EXPORT
    // =========================================================================
    public function pdf(Request $r)
    {
        [$start, $end] = $this->range($r);
        $sections = $this->sections($r);

        // ===== Query sama dengan index() di atas =====
        $items = DB::table('penjualan as pjl')
            ->join('penjualan_detail as d', 'd.penjualan_id', '=', 'pjl.id')
            ->join('produk as pr', 'pr.id', '=', 'd.produk_id')
            ->join('payment as pay', 'pay.penjualan_id', '=', 'pjl.id')
            ->where('pay.transaction_status', 'settlement')
            ->whereBetween('pjl.created_at', [$start, $end])
            ->selectRaw('
                pr.id as product_id,
                pr.nama_barang as name,
                SUM(d.qty) AS qty,
                SUM(d.qty * d.harga) AS total
            ')
            ->groupBy('pr.id', 'pr.nama_barang')
            ->orderByDesc('total')
            ->get();

        $payments = DB::table('payment as pay')
            ->join('penjualan as pjl', 'pjl.id', '=', 'pay.penjualan_id')
            ->where('pay.transaction_status', 'settlement')
            ->whereBetween('pay.paid_at', [$start, $end])
            ->selectRaw('pay.pg_payment_type, COUNT(*) trx, SUM(pay.gross_amount) total')
            ->groupBy('pay.pg_payment_type')
            ->orderByDesc('total')
            ->get();

        $payUnified = DB::table('payment as pay')
            ->join('penjualan as pjl', 'pjl.id', '=', 'pay.penjualan_id')
            ->where('pay.transaction_status', 'settlement')
            ->whereBetween('pay.paid_at', [$start, $end])
            ->selectRaw("
                CASE 
                    WHEN pay.pg_payment_type='cash' THEN 'Tunai' 
                    ELSE 'Non Tunai' 
                END as kategori,
                COUNT(*) trx,
                SUM(pay.gross_amount) total
            ")
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get();

        $journal = DB::table('journal_entry as je')
            ->join('journal_line as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
            ->whereBetween('je.date', [$start, $end])
            ->orderBy('je.date')
            ->orderBy('je.entry_no')
            ->selectRaw('
                je.date,
                je.entry_no,
                je.ref_no,
                je.memo as entry_memo,
                jl.memo as line_memo,
                coa.code,
                coa.name,
                coa.normal_side,
                jl.debit,
                jl.credit
            ')
            ->get();

        // Ledger sama seperti index()
        $ledger = [];
        foreach ($journal as $row) {
            $key    = $row->code . ' - ' . $row->name;
            $normal = strtoupper($row->normal_side);

            if (!isset($ledger[$key])) {
                $ledger[$key] = [
                    'normal'       => $normal,
                    'rows'         => [],
                    'total_debit'  => 0,
                    'total_credit' => 0,
                    'balance'      => 0,
                ];
            }

            $change = ($normal === 'DEBIT')
                ? ($row->debit - $row->credit)
                : ($row->credit - $row->debit);

            $ledger[$key]['balance']      += $change;
            $ledger[$key]['total_debit']  += (float) $row->debit;
            $ledger[$key]['total_credit'] += (float) $row->credit;

            $ledger[$key]['rows'][] = [
                'date'   => $row->date,
                'entry'  => $row->entry_no,
                'ref'    => $row->ref_no,
                'memo'   => $row->line_memo ?: $row->entry_memo,
                'debit'  => (float) $row->debit,
                'credit' => (float) $row->credit,
                'saldo'  => $ledger[$key]['balance'],
            ];
        }

        $pdf = Pdf::loadView('reports.pdf.kasir_rekap_pdf', [
            'items'     => $items,
            'payments'  => $payments,
            'payUnified'=> $payUnified,
            'journal'   => $journal,
            'ledger'    => $ledger,
            'meta'      => compact('start','end'),
            'sections'  => $sections
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Rekap-Kasir_{$start}_sd_{$end}.pdf");
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
    private function range(Request $r): array
    {
        $start = $r->get('start_date') ?: now()->toDateString();
        $end   = $r->get('end_date') ?: now()->toDateString();
        return [$start.' 00:00:00', $end.' 23:59:59'];
    }

    private function sections(Request $r): array
    {
        $all = ['items','payments','unified','journal','ledger'];
        $sel = $r->input('sec', $all);
        if (!is_array($sel)) $sel = [$sel];

        return [
            'items'    => in_array('items', $sel),
            'payments' => in_array('payments', $sel),
            'unified'  => in_array('unified', $sel),
            'journal'  => in_array('journal', $sel),
            'ledger'   => in_array('ledger', $sel),
        ];
    }
}
