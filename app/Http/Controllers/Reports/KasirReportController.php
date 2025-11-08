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

        // Per Produk
        $items = DB::table(DB::raw('`order` as o'))
            ->join('order_item as oi', 'oi.order_id', '=', 'o.id')
            ->join('payment as p', 'p.order_id', '=', 'o.id')
            ->where('o.status', 'paid')
            ->where('p.transaction_status', 'settlement')
            ->whereBetween('o.created_at', [$start, $end])
            ->selectRaw('
                oi.product_id,
                oi.name,
                SUM(oi.qty) AS qty,
                SUM(oi.price * oi.qty) AS total
            ')
            ->groupBy('oi.product_id', 'oi.name')
            ->orderByDesc('total')
            ->get();

        // Per Metode Pembayaran (detail)
        $payments = DB::table('payment as p')
            ->join(DB::raw('`order` as o'), 'o.id', '=', 'p.order_id')
            ->where('p.transaction_status', 'settlement')
            ->whereBetween('p.paid_at', [$start, $end])
            ->selectRaw('p.pg_payment_type, COUNT(*) trx, SUM(p.gross_amount) total')
            ->groupBy('p.pg_payment_type')
            ->orderByDesc('total')
            ->get();

        // Rekapitulasi Tunai vs Non-Tunai (gabungan)
        $payUnified = DB::table('payment as p')
            ->join(DB::raw('`order` as o'), 'o.id', '=', 'p.order_id')
            ->where('p.transaction_status', 'settlement')
            ->whereBetween('p.paid_at', [$start, $end])
            ->selectRaw("
                CASE WHEN p.pg_payment_type='cash' THEN 'Tunai' ELSE 'Non Tunai' END as kategori,
                COUNT(*) trx, SUM(p.gross_amount) total
            ")
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get();

        // Jurnal Umum
        $journal = DB::table('journal_entry as je')
            ->join('journal_line as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
            ->whereBetween('je.date', [$start, $end])
            ->orderBy('je.date')
            ->orderBy('je.entry_no')
            ->selectRaw('
                je.date, je.entry_no, je.ref_no, je.memo as entry_memo,
                coa.code, coa.name, coa.normal_side,
                jl.debit, jl.credit
            ')
            ->get();

        // Buku Besar (running balance per akun)
        $ledger = [];
        foreach ($journal as $row) {
            $key = $row->code.' - '.$row->name;
            if (!isset($ledger[$key])) {
                $ledger[$key] = [
                    'normal' => $row->normal_side,
                    'rows' => [],
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'balance' => 0,
                ];
            }
            $balanceChange = ($row->normal_side === 'DEBIT')
                ? ($row->debit - $row->credit)
                : ($row->credit - $row->debit);

            $ledger[$key]['balance'] += $balanceChange;
            $ledger[$key]['total_debit'] += $row->debit;
            $ledger[$key]['total_credit'] += $row->credit;

            $ledger[$key]['rows'][] = [
                'date'   => $row->date,
                'entry'  => $row->entry_no,
                'ref'    => $row->ref_no,
                'memo'   => $row->entry_memo,
                'debit'  => $row->debit,
                'credit' => $row->credit,
                'saldo'  => $ledger[$key]['balance'],
            ];
        }

        $meta = compact('start','end');
        return view('reports.kasir_rekap', compact('items','payments','payUnified','journal','ledger','meta'));
    }

    public function pdf(Request $r)
    {
        [$start, $end] = $this->range($r);

        // Per Produk
        $items = DB::table(DB::raw('`order` as o'))
            ->join('order_item as oi', 'oi.order_id', '=', 'o.id')
            ->join('payment as p', 'p.order_id', '=', 'o.id')
            ->where('o.status', 'paid')
            ->where('p.transaction_status', 'settlement')
            ->whereBetween('o.created_at', [$start, $end])
            ->selectRaw('
                oi.product_id,
                oi.name,
                SUM(oi.qty) AS qty,
                SUM(oi.price * oi.qty) AS total
            ')
            ->groupBy('oi.product_id', 'oi.name')
            ->orderByDesc('total')
            ->get();

        // Per Metode Pembayaran
        $payments = DB::table('payment as p')
            ->join(DB::raw('`order` as o'), 'o.id', '=', 'p.order_id')
            ->where('p.transaction_status', 'settlement')
            ->whereBetween('p.paid_at', [$start, $end])
            ->selectRaw('p.pg_payment_type, COUNT(*) trx, SUM(p.gross_amount) total')
            ->groupBy('p.pg_payment_type')
            ->orderByDesc('total')
            ->get();

        // Rekapitulasi Tunai vs Non-Tunai
        $payUnified = DB::table('payment as p')
            ->join(DB::raw('`order` as o'), 'o.id', '=', 'p.order_id')
            ->where('p.transaction_status', 'settlement')
            ->whereBetween('p.paid_at', [$start, $end])
            ->selectRaw("
                CASE WHEN p.pg_payment_type='cash' THEN 'Tunai' ELSE 'Non Tunai' END as kategori,
                COUNT(*) trx, SUM(p.gross_amount) total
            ")
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get();

        // Jurnal Umum
        $journal = DB::table('journal_entry as je')
            ->join('journal_line as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
            ->whereBetween('je.date', [$start, $end])
            ->orderBy('je.date')
            ->orderBy('je.entry_no')
            ->selectRaw('
                je.date, je.entry_no, je.ref_no, je.memo as entry_memo,
                coa.code, coa.name, coa.normal_side,
                jl.debit, jl.credit
            ')
            ->get();

        // Buku Besar (running balance per akun)
    $ledger = [];
    foreach ($journal as $row) {
        $key = $row->code.' - '.$row->name;
        if (!isset($ledger[$key])) {
            $ledger[$key] = [
                'normal' => $row->normal_side,   // 'DEBIT' atau 'CREDIT'
                'rows' => [],
                'total_debit' => 0,
                'total_credit' => 0,
                'balance' => 0,
            ];
        }
        $change = ($row->normal_side === 'DEBIT')
            ? ($row->debit - $row->credit)
            : ($row->credit - $row->debit);

        $ledger[$key]['balance']      += $change;
        $ledger[$key]['total_debit']  += (float)$row->debit;
        $ledger[$key]['total_credit'] += (float)$row->credit;

        $ledger[$key]['rows'][] = [
            'date'   => $row->date,
            'entry'  => $row->entry_no,
            'ref'    => $row->ref_no,
            'memo'   => $row->entry_memo,
            'debit'  => (float)$row->debit,
            'credit' => (float)$row->credit,
            'saldo'  => $ledger[$key]['balance'],
        ];
    }

        $pdf = Pdf::loadView('reports.pdf.kasir_rekap_pdf', [
            'items'      => $items,
            'payments'   => $payments,
            'payUnified' => $payUnified,
            'journal'    => $journal,
            'ledger'     => $ledger,
            'meta'       => ['start' => $start, 'end' => $end],
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Rekap-Kasir_{$start}_sd_{$end}.pdf");
    }



    private function range(Request $r): array
    {
        $start = $r->get('start_date') ?: now()->toDateString();
        $end   = $r->get('end_date')   ?: now()->toDateString();
        return [$start.' 00:00:00', $end.' 23:59:59'];
    }
}
