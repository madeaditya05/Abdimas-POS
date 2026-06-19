<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirReportController extends Controller
{
    public function index(Request $r)
    {
        [$start, $end] = $this->range($r);
        $sections = $this->sections($r);
        $data = $this->buildReportData($start, $end);
        $meta = compact('start', 'end');

        $selectedAccount = $r->get('account_code');
        if (!empty($selectedAccount)) {
            $data['ledger'] = array_filter($data['ledger'], function($key) use ($selectedAccount) {
                return str_starts_with($key, $selectedAccount . ' -');
            }, ARRAY_FILTER_USE_KEY);
        }

        $accounts = DB::table('chart_of_account')
            ->where('is_active', 1)
            ->orderBy('code')
            ->select('code', 'name')
            ->get();

        return view('reports.kasir_rekap', [
            'items' => $data['items'],
            'payments' => $data['payments'],
            'payUnified' => $data['payUnified'],
            'journal' => $data['journal'],
            'ledger' => $data['ledger'],
            'meta' => $meta,
            'sections' => $sections,
            'accounts' => $accounts,
        ]);
    }

    public function pdf(Request $r)
    {
        [$start, $end] = $this->range($r);
        $sections = $this->sections($r);
        $data = $this->buildReportData($start, $end);

        $selectedAccount = $r->get('account_code');
        if (!empty($selectedAccount)) {
            $data['ledger'] = array_filter($data['ledger'], function($key) use ($selectedAccount) {
                return str_starts_with($key, $selectedAccount . ' -');
            }, ARRAY_FILTER_USE_KEY);
        }

        $pdf = Pdf::loadView('reports.pdf.kasir_rekap_pdf', [
            'items' => $data['items'],
            'payments' => $data['payments'],
            'payUnified' => $data['payUnified'],
            'journal' => $data['journal'],
            'ledger' => $data['ledger'],
            'meta' => compact('start', 'end'),
            'sections' => $sections,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Rekap-Kasir_{$start}_sd_{$end}.pdf");
    }

    public function excel(Request $r)
    {
        [$start, $end] = $this->range($r);
        $sections = $this->sections($r);
        $data = $this->buildReportData($start, $end);

        $selectedAccount = $r->get('account_code');
        if (!empty($selectedAccount)) {
            $data['ledger'] = array_filter($data['ledger'], function($key) use ($selectedAccount) {
                return str_starts_with($key, $selectedAccount . ' -');
            }, ARRAY_FILTER_USE_KEY);
        }

        $html = view('reports.excel.kasir_rekap_excel', [
            'items' => $data['items'],
            'payments' => $data['payments'],
            'payUnified' => $data['payUnified'],
            'journal' => $data['journal'],
            'ledger' => $data['ledger'],
            'meta' => compact('start', 'end'),
            'sections' => $sections,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"Rekap-Kasir_{$start}_sd_{$end}.xls\"");
    }

    private function buildReportData(string $start, string $end): array
    {
        $items = DB::table('penjualan as pjl')
            ->join('penjualan_detail as d', 'd.penjualan_id', '=', 'pjl.id')
            ->join('produk as pr', 'pr.id', '=', 'd.produk_id')
            ->where('pjl.total', '>', 0)
            ->whereColumn('pjl.bayar', '>=', 'pjl.total')
            ->whereBetween('pjl.tanggal', [$start, $end])
            ->selectRaw('
                pr.id as product_id,
                pr.nama_barang as name,
                SUM(d.qty) AS qty,
                SUM(d.subtotal) AS total
            ')
            ->groupBy('pr.id', 'pr.nama_barang')
            ->orderByDesc('total')
            ->get();

        $payments = DB::table('penjualan as pjl')
            ->where('pjl.total', '>', 0)
            ->whereColumn('pjl.bayar', '>=', 'pjl.total')
            ->whereBetween('pjl.tanggal', [$start, $end])
            ->selectRaw("
                LOWER(COALESCE(NULLIF(pjl.metode, ''), 'cash')) as pg_payment_type,
                COUNT(*) trx,
                SUM(pjl.total) total
            ")
            ->groupBy('pg_payment_type')
            ->orderByDesc('total')
            ->get();

        $payUnified = DB::table('penjualan as pjl')
            ->where('pjl.total', '>', 0)
            ->whereColumn('pjl.bayar', '>=', 'pjl.total')
            ->whereBetween('pjl.tanggal', [$start, $end])
            ->selectRaw("
                CASE
                    WHEN LOWER(COALESCE(NULLIF(pjl.metode, ''), 'cash')) IN ('cash', 'tunai') THEN 'Tunai'
                    ELSE 'Non Tunai'
                END as kategori,
                COUNT(*) trx,
                SUM(pjl.total) total
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

        return [
            'items' => $items,
            'payments' => $payments,
            'payUnified' => $payUnified,
            'journal' => $journal,
            'ledger' => $this->buildLedger($journal),
        ];
    }

    private function buildLedger($journal): array
    {
        $ledger = [];

        foreach ($journal as $row) {
            $key = $row->code . ' - ' . $row->name;
            $normal = strtoupper($row->normal_side);

            if (! isset($ledger[$key])) {
                $ledger[$key] = [
                    'normal' => $normal,
                    'rows' => [],
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'balance' => 0,
                ];
            }

            $change = ($normal === 'DEBIT')
                ? ((float) $row->debit - (float) $row->credit)
                : ((float) $row->credit - (float) $row->debit);

            $ledger[$key]['balance'] += $change;
            $ledger[$key]['total_debit'] += (float) $row->debit;
            $ledger[$key]['total_credit'] += (float) $row->credit;

            $ledger[$key]['rows'][] = [
                'date' => $row->date,
                'entry' => $row->entry_no,
                'ref' => $row->ref_no,
                'memo' => $row->line_memo ?: $row->entry_memo,
                'debit' => (float) $row->debit,
                'credit' => (float) $row->credit,
                'saldo' => $ledger[$key]['balance'],
            ];
        }

        return $ledger;
    }

    private function range(Request $r): array
    {
        $start = $r->get('start_date') ?: now()->toDateString();
        $end = $r->get('end_date') ?: now()->toDateString();

        return [$start . ' 00:00:00', $end . ' 23:59:59'];
    }

    private function sections(Request $r): array
    {
        $all = ['items', 'payments', 'unified', 'journal', 'ledger'];
        $sel = $r->input('sec', $all);
        if (! is_array($sel)) {
            $sel = [$sel];
        }

        return [
            'items' => in_array('items', $sel, true),
            'payments' => in_array('payments', $sel, true),
            'unified' => in_array('unified', $sel, true),
            'journal' => in_array('journal', $sel, true),
            'ledger' => in_array('ledger', $sel, true),
        ];
    }
}
