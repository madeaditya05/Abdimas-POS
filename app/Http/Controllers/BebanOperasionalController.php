<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BebanOperasionalController extends Controller
{
    private const SOURCE_TYPE = 'BEBAN_OPERASIONAL';

    public function index(Request $request)
    {
        $data = $this->getBebanData($request);
        $entries = $data['entries'];
        $start = $data['start'];
        $end = $data['end'];
        return view('beban-operasional.index', compact('entries', 'start', 'end'));
    }

    public function pdf(Request $request)
    {
        $data = $this->getBebanData($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.beban_operasional_pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download("Laporan-Beban-Operasional_{$data['start']}_sd_{$data['end']}.pdf");
    }

    public function excel(Request $request)
    {
        $data = $this->getBebanData($request);
        $html = view('reports.excel.beban_operasional_excel', $data)->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"Laporan-Beban-Operasional_{$data['start']}_sd_{$data['end']}.xls\"");
    }

    private function getBebanData(Request $request)
    {
        $start = $request->get('start_date', now()->startOfMonth()->toDateString());
        $end   = $request->get('end_date', now()->toDateString());

        $entries = DB::table('journal_entry as je')
            ->select('je.id', 'je.entry_no', 'je.date', 'je.ref_no', 'je.memo')
            ->where('je.source_type', self::SOURCE_TYPE)
            ->whereBetween('je.date', [$start, $end])
            ->orderByDesc('je.date')
            ->orderByDesc('je.id')
            ->get();

        $entryIds = collect($entries)->pluck('id')->all();
        $totals = [];

        if (!empty($entryIds)) {
            $rows = DB::table('journal_line as jl')
                ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
                ->select('jl.journal_entry_id', DB::raw('SUM(jl.debit) as total_debit_beban'))
                ->whereIn('jl.journal_entry_id', $entryIds)
                ->where('coa.code', 'like', '6%')
                ->groupBy('jl.journal_entry_id')
                ->get();

            foreach ($rows as $r) {
                $totals[$r->journal_entry_id] = (float) $r->total_debit_beban;
            }
        }

        return compact('entries', 'totals', 'start', 'end');
    }

    public function create(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        // akun beban: type expense atau code 6xxx
        $akunBeban = DB::table('chart_of_account')
            ->select('id', 'code', 'name')
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('type', 'expense')
                  ->orWhere('code', 'like', '6%');
            })
            ->orderBy('code')
            ->get();

        // akun bayar: kas/bank (1001/1002)
        $akunBayar = DB::table('chart_of_account')
            ->select('id', 'code', 'name')
            ->where('is_active', 1)
            ->whereIn('code', ['1001', '1002'])
            ->orderBy('code')
            ->get();

        return view('beban-operasional.create', compact('date', 'akunBeban', 'akunBayar'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'            => ['required', 'date'],
            'expense_account' => ['required', 'integer'],
            'pay_account'     => ['required', 'integer'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'memo'            => ['nullable', 'string', 'max:255'],
            'ref_no'          => ['nullable', 'string', 'max:50'],
        ]);

        $date   = $validated['date'];
        $amount = round((float) $validated['amount'], 2);

        $payCoa = DB::table('chart_of_account')
            ->select('id', 'code', 'name')
            ->where('id', $validated['pay_account'])
            ->first();

        if (!$payCoa || !in_array($payCoa->code, ['1001', '1002'], true)) {
            return back()->withInput()->withErrors([
                'pay_account' => 'Akun pembayaran harus Kas (1001) atau Bank (1002).'
            ]);
        }

        $expCoa = DB::table('chart_of_account')
            ->select('id', 'code', 'name', 'type')
            ->where('id', $validated['expense_account'])
            ->first();

        if (!$expCoa) {
            return back()->withInput()->withErrors([
                'expense_account' => 'Akun beban tidak ditemukan.'
            ]);
        }

        $isExpense = (Str::startsWith((string)$expCoa->code, '6') || $expCoa->type === 'expense');
        if (!$isExpense) {
            return back()->withInput()->withErrors([
                'expense_account' => 'Akun yang dipilih bukan akun beban (6xxx).'
            ]);
        }

        DB::transaction(function () use ($validated, $date, $amount, $expCoa, $payCoa) {
            // entry_no format: BEB-YYYYMMDD-XXXX
            $prefix = 'BEB-' . str_replace('-', '', $date) . '-';
            $lastNo = DB::table('journal_entry')
                ->where('entry_no', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->value('entry_no');

            $nextSeq = 1;
            if ($lastNo) {
                $tail = (int) substr($lastNo, -4);
                $nextSeq = $tail + 1;
            }

            $entryNo = $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

            $entryId = DB::table('journal_entry')->insertGetId([
                'entry_no'    => $entryNo,
                'date'        => $date,
                'ref_no'      => $validated['ref_no'] ?? null,
                'memo'        => $validated['memo'] ?? ('Beban Operasional: ' . $expCoa->name),
                'source_type' => self::SOURCE_TYPE,
                'source_id'   => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('journal_line')->insert([
                'journal_entry_id' => $entryId,
                'account_id'       => $expCoa->id,
                'debit'            => $amount,
                'credit'           => 0,
                'memo'             => $validated['memo'] ?? null,
                'line_no'          => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::table('journal_line')->insert([
                'journal_entry_id' => $entryId,
                'account_id'       => $payCoa->id,
                'debit'            => 0,
                'credit'           => $amount,
                'memo'             => $validated['memo'] ?? null,
                'line_no'          => 2,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        });

        return redirect()
            ->route('beban-operasional.index')
            ->with('success', 'Beban operasional berhasil disimpan ke jurnal.');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $entry = DB::table('journal_entry')
                ->where('id', $id)
                ->where('source_type', self::SOURCE_TYPE)
                ->first();

            if (!$entry) abort(404);

            DB::table('journal_line')->where('journal_entry_id', $id)->delete();
            DB::table('journal_entry')->where('id', $id)->delete();
        });

        return back()->with('success', 'Beban operasional berhasil dihapus.');
    }
}
