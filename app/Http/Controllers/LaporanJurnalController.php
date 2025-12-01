<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\Request;

class LaporanJurnalController extends Controller
{
    /**
     * Halaman Jurnal Umum (daftar header jurnal).
     *
     * Filter:
     * - q    : search entry_no / ref_no / memo
     * - from : tanggal mulai
     * - to   : tanggal sampai
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $from   = $request->query('from');
        $to     = $request->query('to');

        $query = JournalEntry::query()
            ->with(['lines.coa']);   // <- load detail + akun

        // filter search
        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function ($q) use ($like) {
                $q->where('entry_no', 'like', $like)
                  ->orWhere('ref_no', 'like', $like)
                  ->orWhere('memo', 'like', $like);
            });
        }

        // filter tanggal
        if (! empty($from)) {
            $query->whereDate('date', '>=', $from);
        }

        if (! empty($to)) {
            $query->whereDate('date', '<=', $to);
        }

        $items = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // === HITUNG RINGKASAN PER JURNAL ===
        $items->getCollection()->transform(function (JournalEntry $entry) {
            $lines = $entry->lines ?? collect();

            // total debit & kredit
            $entry->total_debit  = (int) $lines->sum('debit');
            $entry->total_credit = (int) $lines->sum('credit');

            // ringkasan akun (maks 2 akun + info tambahan)
            $akunList = $lines
                ->map(function (JournalLine $line) {
                    $coa = $line->coa;
                    if (! $coa) {
                        return null;
                    }
                    return $coa->code . ' ' . $coa->name;
                })
                ->filter()
                ->unique()
                ->values();

            if ($akunList->isEmpty()) {
                $entry->akun_ringkas = null;
            } elseif ($akunList->count() <= 2) {
                $entry->akun_ringkas = $akunList->implode(', ');
            } else {
                $sisa = $akunList->count() - 2;
                $entry->akun_ringkas = $akunList->take(2)->implode(', ') . " +{$sisa} akun";
            }

            return $entry;
        });

        // $rows dipakai di Blade → alias ke $items
        return view('laporan.jurnal.index', [
            'items'  => $items,
            'rows'   => $items,
            'search' => $search,
            'from'   => $from,
            'to'     => $to,
        ]);
    }

    /**
     * Detail 1 jurnal: header + baris debit/kredit.
     */
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

    /**
     * Daftar semua baris jurnal (audit detail).
     *
     * Filter:
     * - q          : search nama akun / kode akun / memo / entry_no
     * - account_id : filter akun tertentu
     * - from       : entry.date >= from
     * - to         : entry.date <= to
     */
    public function lines(Request $request)
    {
        $search    = trim((string) $request->query('q', ''));
        $accountId = $request->query('account_id');
        $from      = $request->query('from');
        $to        = $request->query('to');

        $query = JournalLine::query()
            ->with(['entry', 'coa']);

        if (! empty($accountId)) {
            $query->where('account_id', (int) $accountId);
        }

        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function ($q) use ($like) {
                $q->whereHas('coa', function ($qc) use ($like) {
                    $qc->where('name', 'like', $like)
                       ->orWhere('code', 'like', $like);
                })
                ->orWhereHas('entry', function ($qe) use ($like) {
                    $qe->where('entry_no', 'like', $like)
                       ->orWhere('memo', 'like', $like);
                })
                ->orWhere('memo', 'like', $like);
            });
        }

        if (! empty($from)) {
            $query->whereHas('entry', function ($qe) use ($from) {
                $qe->whereDate('date', '>=', $from);
            });
        }

        if (! empty($to)) {
            $query->whereHas('entry', function ($qe) use ($to) {
                $qe->whereDate('date', '<=', $to);
            });
        }

        $items = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $opsiAkun = ChartOfAccount::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(function ($coa) {
                return [
                    $coa->id => $coa->code . ' - ' . $coa->name,
                ];
            })
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
}
