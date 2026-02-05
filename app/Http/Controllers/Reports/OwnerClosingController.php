<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ClosingPeriod;
use App\Services\ClosingBookService;
use Illuminate\Http\Request;

class OwnerClosingController extends Controller
{
    public function index(Request $request, ClosingBookService $svc)
    {
        // default: bulan ini
        $start = $request->get('start_date') ?: now()->startOfMonth()->toDateString();
        $end   = $request->get('end_date')   ?: now()->endOfMonth()->toDateString();

        $preview = $svc->preview($start, $end);

        // lebih aman pakai whereDate biar ga ke-miss gara-gara cast / format
        $already = ClosingPeriod::query()
            ->whereDate('period_start', $start)
            ->whereDate('period_end', $end)
            ->where('is_closed', true)
            ->first();

        return view('reports.owner_tutupbuku', compact('start', 'end', 'preview', 'already'));
    }

    public function close(Request $request, ClosingBookService $svc)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start = substr($request->input('start_date'), 0, 10);
        $end   = substr($request->input('end_date'), 0, 10);

        // ✅ BONUS GUARD (UX): kalau sudah closed, tolak rapi tanpa error page
        // (Walau service kamu juga udah guard, ini bikin user dapet pesan yang enak.)
        if ($svc->isClosed($start, $end)) {
            return redirect()
                ->route('owner.tutupbuku', ['start_date' => $start, 'end_date' => $end])
                ->with('error', "Periode {$start} s/d {$end} sudah ditutup. Tidak bisa closing dua kali.");
        }

        $cp = $svc->close($start, $end);

        return redirect()
            ->route('owner.tutupbuku', ['start_date' => $start, 'end_date' => $end])
            ->with('success', "Tutup Buku berhasil ✅ Jurnal closing ID: {$cp->journal_entry_id}");
    }
}
