<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Penjualan;
use App\Models\PembelianBahan;
use Illuminate\Http\Request;

class LaporanJurnalController extends Controller
{
    /* ============================================================
     * 1) JURNAL UMUM
     * ============================================================ */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $from   = $request->query('from');
        $to     = $request->query('to');
        $source = $request->query('source', 'all'); // all | sales | purchase

        $query = JournalEntry::query()->with(['lines.coa']);

        // filter sumber transaksi
        if ($source === 'sales') {
            $query->where('source_type', Penjualan::class);
        } elseif ($source === 'purchase') {
            $query->where('source_type', PembelianBahan::class);
        }

        // filter search
        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('entry_no', 'like', $like)
                  ->orWhere('ref_no', 'like', $like)
                  ->orWhere('memo', 'like', $like);
            });
        }

        // filter tanggal
        if (!empty($from)) {
            $query->whereDate('date', '>=', $from);
        }
        if (!empty($to)) {
            $query->whereDate('date', '<=', $to);
        }

        $items = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // hitung ringkasan per jurnal
        $items->getCollection()->transform(function (JournalEntry $entry) {
            $lines = $entry->lines ?? collect();

            $entry->total_debit  = (int) $lines->sum('debit');
            $entry->total_credit = (int) $lines->sum('credit');

            $akunList = $lines
                ->map(fn(JournalLine $l) => $l->coa?->code.' '.$l->coa?->name)
                ->filter()
                ->unique()
                ->values();

            if ($akunList->isEmpty()) {
                $entry->akun_ringkas = null;
            } elseif ($akunList->count() <= 2) {
                $entry->akun_ringkas = $akunList->implode(', ');
            } else {
                $entry->akun_ringkas = $akunList->take(2)->implode(', ') . ' +' . ($akunList->count()-2) . ' akun';
            }

            return $entry;
        });

        return view('laporan.jurnal.index', [
            'items'  => $items,
            'rows'   => $items,
            'search' => $search,
            'from'   => $from,
            'to'     => $to,
            'source' => $source,
        ]);
    }

    /* ============================================================
     * 2) DETAIL JURNAL (HEADER + BARIS)
     * ============================================================ */
    public function show(int $id)
    {
        $entry = JournalEntry::with(['lines.coa'])->findOrFail($id);

        $lines = $entry->lines()
            ->with('coa')
            ->orderBy('line_no')
            ->orderBy('id')
            ->get();

        return view('laporan.jurnal.show', [
            'entry' => $entry,
            'lines' => $lines,
        ]);
    }

    /* ============================================================
     * 3) AUDIT TRAIL (SEMUA BARIS JURNAL)
     * ============================================================ */
    public function lines(Request $request)
    {
        $search    = trim((string) $request->query('q', ''));
        $accountId = $request->query('account_id');
        $from      = $request->query('from');
        $to        = $request->query('to');

        $query = JournalLine::query()->with(['entry','coa']);

        if (!empty($accountId)) {
            $query->where('account_id', (int) $accountId);
        }

        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->whereHas('coa', fn($qc) => $qc->where('name','like',$like)->orWhere('code','like',$like))
                  ->orWhereHas('entry', fn($qe) => $qe->where('entry_no','like',$like)->orWhere('memo','like',$like))
                  ->orWhere('memo','like',$like);
            });
        }

        if (!empty($from)) {
            $query->whereHas('entry', fn($qe) => $qe->whereDate('date','>=',$from));
        }
        if (!empty($to)) {
            $query->whereHas('entry', fn($qe) => $qe->whereDate('date','<=',$to));
        }

        $items = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $opsiAkun = ChartOfAccount::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn($c) => [
                $c->id => $c->code.' - '.$c->name
            ])
            ->toArray();

        return view('laporan.jurnal.lines', [
            'items'     => $items,
            'rows'      => $items,
            'opsiAkun'  => $opsiAkun,
            'search'    => $search,
            'accountId' => $accountId,
            'from'      => $from,
            'to'        => $to,
        ]);
    }

    /* ============================================================
     * 4) BUKU BESAR
     * ============================================================ */
    public function ledger(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        $query = JournalLine::query()
            ->with(['entry','coa'])
            ->orderBy('entry.date')
            ->orderBy('entry.id')
            ->orderBy('line_no');

        if (!empty($from)) {
            $query->whereHas('entry', fn($q) => $q->whereDate('date','>=',$from));
        }
        if (!empty($to)) {
            $query->whereHas('entry', fn($q) => $q->whereDate('date','<=',$to));
        }

        $rows = $query->get();

        $ledger = [];
        foreach ($rows as $line) {
            $coa = $line->coa;
            if (!$coa) continue;

            $accKey = "{$coa->code} - {$coa->name}";
            $normalSide = strtoupper($coa->normal_side ?? 'DEBIT');

            $ledger[$accKey] ??= [
                'normal'       => $normalSide,
                'rows'         => [],
                'total_debit'  => 0,
                'total_credit' => 0,
                'balance'      => 0,
            ];

            $entryNo = $line->entry?->entry_no ?? '';
            $date    = $line->entry?->date?->toDateString() ?? now()->toDateString();

            $ledger[$accKey]['rows'][] = [
                'date'   => $date,
                'entry'  => $entryNo,
                'memo'   => $line->memo ?? $line->entry?->memo,
                'debit'  => $line->debit,
                'credit' => $line->credit,
            ];

            $ledger[$accKey]['total_debit']  += $line->debit;
            $ledger[$accKey]['total_credit'] += $line->credit;

            // saldo berjalan (D meningkat saldo debit, C meningkatkan saldo kredit)
            if ($normalSide === 'DEBIT') {
                $ledger[$accKey]['balance'] += $line->debit - $line->credit;
            } else {
                $ledger[$accKey]['balance'] += $line->credit - $line->debit;
            }
        }

        return view('laporan.jurnal.ledger', [
            'ledger' => $ledger,
            'from'   => $from,
            'to'     => $to,
        ]);
    }

    /* ============================================================
     * 5) LABA RUGI (sisi pembelian saja)
     * ============================================================ */
    public function labaRugi(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        // ambil akun tipe "EXPENSE"
        $expenseAccounts = ChartOfAccount::where('type','EXPENSE')->pluck('id')->toArray();

        $query = JournalLine::query()
            ->with(['entry','coa'])
            ->whereIn('account_id', $expenseAccounts)
            ->orderBy('entry.date');

        if (!empty($from)) {
            $query->whereHas('entry', fn($q) => $q->whereDate('date','>=',$from));
        }
        if (!empty($to)) {
            $query->whereHas('entry', fn($q) => $q->whereDate('date','<=',$to));
        }

        $rows = $query->get();

        $data = [];
        foreach ($rows as $line) {
            $coa = $line->coa;
            if (!$coa) continue;

            $key = "{$coa->code} - {$coa->name}";
            $data[$key] ??= [
                'akun'  => $key,
                'debit' => 0,
                'credit'=> 0,
            ];

            $data[$key]['debit']  += $line->debit;
            $data[$key]['credit'] += $line->credit;
        }

        return view('laporan.jurnal.labarugi', [
            'items' => $data,
            'from'  => $from,
            'to'    => $to,
        ]);
    }
}
