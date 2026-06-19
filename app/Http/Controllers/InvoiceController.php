<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\JournalPoster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->getInvoiceReportData($request);
        $invoices = $data['query']
            ->orderBy('tanggal_jatuh_tempo')
            ->orderBy('tanggal_invoice')
            ->paginate(15)
            ->withQueryString();

        $summary = $data['summary'];
        $filters = $data['filters'];

        return view('invoices.index', compact('invoices', 'summary', 'filters'));
    }

    public function pdf(Request $request)
    {
        $data = $this->getInvoiceReportData($request);
        $invoices = $data['query']
            ->orderBy('tanggal_jatuh_tempo')
            ->orderBy('tanggal_invoice')
            ->get();

        $summary = $data['summary'];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.invoices_pdf', compact('invoices', 'summary'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Laporan-Piutang-Invoice_" . now()->format('Ymd_His') . ".pdf");
    }

    public function excel(Request $request)
    {
        $data = $this->getInvoiceReportData($request);
        $invoices = $data['query']
            ->orderBy('tanggal_jatuh_tempo')
            ->orderBy('tanggal_invoice')
            ->get();

        $summary = $data['summary'];

        $html = view('reports.excel.invoices_excel', compact('invoices', 'summary'))->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"Laporan-Piutang-Invoice_" . now()->format('Ymd_His') . ".xls\"");
    }

    private function getInvoiceReportData(Request $request)
    {
        $filters = $request->validate([
            'nama_toko' => ['nullable', 'string', 'max:255'],
            'due_from' => ['nullable', 'date'],
            'due_to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,unpaid,paid'],
        ]);

        $query = Invoice::query()
            ->with('penjualan');

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['nama_toko'])) {
            $query->where('nama_toko', 'like', '%' . $filters['nama_toko'] . '%');
        }

        if (! empty($filters['due_from'])) {
            $query->whereDate('tanggal_jatuh_tempo', '>=', $filters['due_from']);
        }

        if (! empty($filters['due_to'])) {
            $query->whereDate('tanggal_jatuh_tempo', '<=', $filters['due_to']);
        }

        $today = today()->toDateString();
        $summaryQuery = clone $query;

        $summary = [
            'count' => (clone $summaryQuery)->count(),
            'unpaid' => (clone $summaryQuery)->where('status', Invoice::STATUS_UNPAID)->count(),
            'paid' => (clone $summaryQuery)->where('status', Invoice::STATUS_PAID)->count(),
            'overdue' => (clone $summaryQuery)
                ->where('status', Invoice::STATUS_UNPAID)
                ->whereDate('tanggal_jatuh_tempo', '<', $today)
                ->count(),
            'total' => (float) (clone $summaryQuery)->sum('total_tagihan'),
        ];

        return compact('query', 'summary', 'filters');
    }

    public function updateStatusLunas(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === Invoice::STATUS_PAID) {
            return back()->with('success', "Invoice {$invoice->nomor_invoice} sudah lunas.");
        }

        DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => Invoice::STATUS_PAID,
            ]);

            $penjualan = $invoice->penjualan()->lockForUpdate()->first();

            if (! $penjualan) {
                return;
            }

            $penjualan->update([
                'bayar' => $penjualan->total,
                'kembalian' => 0,
            ]);

            $payment = Payment::where('penjualan_id', $penjualan->id)
                ->latest()
                ->lockForUpdate()
                ->first();

            $payload = [
                'pg' => 'tempo',
                'pg_payment_type' => 'tempo',
                'gross_amount' => $invoice->total_tagihan,
                'transaction_status' => 'settlement',
                'paid_at' => now(),
            ];

            if ($payment) {
                $payment->update($payload);
            } else {
                Payment::create($payload + [
                    'penjualan_id' => $penjualan->id,
                    'kode_penjualan' => $penjualan->kode_penjualan,
                    'meta' => [
                        'order_no' => $penjualan->kode_penjualan,
                        'paid_manually' => true,
                    ],
                ]);
            }

            app(JournalPoster::class)->postForInvoicePayment($invoice);
        });

        return back()->with('success', "Invoice {$invoice->nomor_invoice} berhasil ditandai lunas.");
    }
}
