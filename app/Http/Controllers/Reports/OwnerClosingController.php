<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ClosingPeriod;
use App\Models\JournalEntry;
use App\Services\ClosingBookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerClosingController extends Controller
{
    private function getMonthsList(): array
    {
        $monthsList = [];
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $currentDate = now();
        for ($i = 0; $i < 12; $i++) {
            $temp = (clone $currentDate)->subMonths($i);
            $year = $temp->format('Y');
            $monthNum = (int)$temp->format('m');
            $monthVal = $temp->format('Y-m');
            $label = $months[$monthNum] . ' ' . $year;
            $monthsList[$monthVal] = $label;
        }

        return $monthsList;
    }

    private function getSelectedMonth(Request $request): string
    {
        $selectedMonth = $request->get('month');
        if (!$selectedMonth) {
            $date = $request->get('start_date') ?: $request->get('end_date');
            $selectedMonth = $date ? \Illuminate\Support\Carbon::parse($date)->format('Y-m') : now()->format('Y-m');
        }
        return $selectedMonth;
    }

    public function index(Request $request, ClosingBookService $svc)
    {
        $selectedMonth = $this->getSelectedMonth($request);
        
        $start = \Illuminate\Support\Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $end   = \Illuminate\Support\Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();

        $preview = $svc->preview($start, $end);

        $already = ClosingPeriod::query()
            ->whereDate('period_start', $start)
            ->whereDate('period_end', $end)
            ->where('is_closed', true)
            ->first();

        $monthsList = $this->getMonthsList();

        return view('reports.owner_tutupbuku', compact('start', 'end', 'selectedMonth', 'preview', 'already', 'monthsList'));
    }

    public function pdf(Request $request, ClosingBookService $svc)
    {
        $selectedMonth = $this->getSelectedMonth($request);
        
        $start = \Illuminate\Support\Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $end   = \Illuminate\Support\Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();
        $preview = $svc->preview($start, $end);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.owner_tutupbuku_pdf', compact('start', 'end', 'preview'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Closing-Tutup-Buku_{$selectedMonth}.pdf");
    }

    public function excel(Request $request, ClosingBookService $svc)
    {
        $selectedMonth = $this->getSelectedMonth($request);
        
        $start = \Illuminate\Support\Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $end   = \Illuminate\Support\Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();
        $preview = $svc->preview($start, $end);

        $html = view('reports.excel.owner_tutupbuku_excel', compact('start', 'end', 'preview'))->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"Closing-Tutup-Buku_{$selectedMonth}.xls\"");
    }

    public function close(Request $request, ClosingBookService $svc)
    {
        $request->validate([
            'month' => 'required|string|regex:/^\d{4}-\d{2}$/',
        ]);

        $selectedMonth = $request->input('month');
        $start = \Illuminate\Support\Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $end   = \Illuminate\Support\Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();

        if ($svc->isClosed($start, $end)) {
            return redirect()
                ->route('owner.tutupbuku', ['month' => $selectedMonth])
                ->with('error', "Periode tutup buku untuk bulan {$selectedMonth} sudah ditutup. Tidak bisa closing dua kali.");
        }

        $cp = $svc->close($start, $end);

        return redirect()
            ->route('owner.tutupbuku', ['month' => $selectedMonth])
            ->with('success', "Tutup Buku berhasil ✅ Jurnal closing ID: {$cp->journal_entry_id}");
    }

    public function reopen(Request $request, ClosingPeriod $closingPeriod)
    {
        $selectedMonth = $closingPeriod->period_start?->format('Y-m') ?: $request->get('month');

        if (!$closingPeriod->is_closed) {
            return redirect()
                ->route('owner.tutupbuku', ['month' => $selectedMonth])
                ->with('error', 'Periode ini tidak dalam status ditutup.');
        }

        DB::transaction(function () use ($closingPeriod) {
            if ($closingPeriod->journal_entry_id) {
                $entry = JournalEntry::find($closingPeriod->journal_entry_id);
                if ($entry) {
                    $entry->lines()->delete();
                    $entry->delete();
                }
            }

            $closingPeriod->is_closed = false;
            $closingPeriod->closed_at = null;
            $closingPeriod->closed_by = null;
            $closingPeriod->journal_entry_id = null;
            $closingPeriod->save();
        });

        return redirect()
            ->route('owner.tutupbuku', ['month' => $selectedMonth])
            ->with('success', 'Periode tutup buku berhasil dibuka kembali. Jurnal penutup telah dihapus.');
    }
}
