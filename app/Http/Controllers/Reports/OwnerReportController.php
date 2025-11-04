<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OwnerReportController extends Controller
{
    public function index(Request $r)
    {
        [$start, $end] = $this->range($r);
        $lr = $this->agg($start, $end);

        return view('reports.owner_labarugi', [
            'lr'   => $lr,
            'meta' => ['start'=>$start, 'end'=>$end]
        ]);
    }

    public function pdf(Request $r)
    {
        [$start, $end] = $this->range($r);
        $lr = $this->agg($start, $end);

        $pdf = Pdf::loadView('reports.pdf.owner_labarugi_pdf', [
            'lr'=>$lr, 'meta'=>['start'=>$start,'end'=>$end]
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Laba-Rugi_{$start}_sd_{$end}.pdf");
    }

    private function agg($start, $end)
    {
        $rows = DB::table('journal_line as jl')
            ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
            ->join('journal_entry as je', 'je.id', '=', 'jl.journal_entry_id')
            ->whereBetween('je.date', [$start, $end])
            ->select('coa.type',
                DB::raw('SUM(jl.debit) as debit'),
                DB::raw('SUM(jl.credit) as credit')
            )
            ->groupBy('coa.type')
            ->get()
            ->keyBy('type');

        $revenue = ($rows['REVENUE']->credit ?? 0) - ($rows['REVENUE']->debit ?? 0);
        $cogs    = ($rows['COGS']->debit ?? 0)    - ($rows['COGS']->credit ?? 0);
        $expense = ($rows['EXPENSE']->debit ?? 0) - ($rows['EXPENSE']->credit ?? 0);

        return [
            'revenue'    => $revenue,
            'cogs'       => $cogs,
            'gross'      => $revenue - $cogs,
            'expense'    => $expense,
            'net_income' => $revenue - $cogs - $expense,
            'rows'       => $rows
        ];
    }

    private function range(Request $r): array
    {
        $start = $r->get('start_date') ?: now()->toDateString();
        $end   = $r->get('end_date')   ?: now()->toDateString(); 
        return [$start.' 00:00:00', $end.' 23:59:59'];
    }
}
