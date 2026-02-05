<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ClosingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OwnerReportController extends Controller
{
    public function index(Request $request)
    {
        [$mulai, $akhir] = $this->rentangTanggal($request);
        $bagian = $this->bagianLaporan($request);

        $items      = $this->ambilRekapProduk($mulai, $akhir);
        $payments   = $this->ambilRekapPembayaran($mulai, $akhir);
        $payUnified = $this->ambilRekapTunaiNonTunai($mulai, $akhir);

        $jurnal     = $this->ambilJurnal($mulai, $akhir);
        $bukuBesar  = $this->susunBukuBesar($jurnal);

        // ✅ LABA RUGI: HPP hanya dari closing_periods (kalau belum ditutup => 0)
        $labarugi   = $this->hitungLabaRugiDariClosing($mulai, $akhir);

        $pembelian  = $this->ambilRekapPembelianBahan($mulai, $akhir);

        $meta = [
            'start' => $mulai,
            'end'   => $akhir,
        ];

        return view('reports.owner_labarugi', [
            'lr'         => $labarugi,
            'items'      => $items,
            'payments'   => $payments,
            'payUnified' => $payUnified,
            'journal'    => $jurnal,
            'ledger'     => $bukuBesar,
            'pembelian'  => $pembelian,
            'meta'       => $meta,
            'sections'   => $bagian,
        ]);
    }

    public function pdf(Request $request)
    {
        [$mulai, $akhir] = $this->rentangTanggal($request);
        $bagian = $this->bagianLaporan($request);

        $items      = $this->ambilRekapProduk($mulai, $akhir);
        $payments   = $this->ambilRekapPembayaran($mulai, $akhir);
        $payUnified = $this->ambilRekapTunaiNonTunai($mulai, $akhir);

        $jurnal     = $this->ambilJurnal($mulai, $akhir);
        $bukuBesar  = $this->susunBukuBesar($jurnal);

        // ✅ konsisten sama index
        $labarugi   = $this->hitungLabaRugiDariClosing($mulai, $akhir);

        $pembelian  = $this->ambilRekapPembelianBahan($mulai, $akhir);

        $meta = [
            'start' => $mulai,
            'end'   => $akhir,
        ];

        $pdf = Pdf::loadView('reports.pdf.owner_labarugi_pdf', [
            'lr'         => $labarugi,
            'items'      => $items,
            'payments'   => $payments,
            'payUnified' => $payUnified,
            'journal'    => $jurnal,
            'ledger'     => $bukuBesar,
            'pembelian'  => $pembelian,
            'meta'       => $meta,
            'sections'   => $bagian,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Laporan-Owner_{$mulai}_sd_{$akhir}.pdf");
    }

    // ===================== LABA RUGI (HPP dari CLOSING_PERIODS) =====================

    private function hitungLabaRugiDariClosing(string $mulai, string $akhir): array
    {
        $mulaiTanggal = substr($mulai, 0, 10);
        $akhirTanggal = substr($akhir, 0, 10);

        // Pendapatan & Beban dari jurnal (akun type revenue/expense)
        $rows = DB::table('journal_line as jl')
            ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
            ->join('journal_entry as je', 'je.id', '=', 'jl.journal_entry_id')
            ->whereBetween('je.date', [$mulai, $akhir])
            ->select(
                'coa.code',
                'coa.name',
                'coa.type',
                DB::raw('SUM(jl.debit) as debit'),
                DB::raw('SUM(jl.credit) as credit')
            )
            ->groupBy('coa.code', 'coa.name', 'coa.type')
            ->get();

        $pendapatan = 0.0;
        $beban      = 0.0;

        foreach ($rows as $row) {
            $debit  = (float) $row->debit;
            $credit = (float) $row->credit;
            $type   = strtolower((string) $row->type);

            if ($type === 'revenue') {
                $pendapatan += ($credit - $debit);
            }

            if ($type === 'expense') {
                // ✅ beban selain HPP (akun 5xxx kamu anggap HPP/COGS, tapi kita ambil dari closing)
                if (substr((string) $row->code, 0, 1) !== '5') {
                    $beban += ($debit - $credit);
                }
            }
        }

        // ✅ Ambil closing kalau periode persis match
        $closing = $this->getClosingForRange($mulaiTanggal, $akhirTanggal);

        $hpp = 0.0;
        $isClosed = false;

        if ($closing) {
            $hpp = (float) ($closing->cogs_value ?? 0);
            $isClosed = true;
        }

        $labaKotor  = $pendapatan - $hpp;
        $labaBersih = $labaKotor - $beban;

        return [
            'revenue'    => $pendapatan,
            'cogs'       => $hpp,
            'gross'      => $labaKotor,
            'expense'    => $beban,
            'net_income' => $labaBersih,
            'rows'       => $rows,

            // flag buat UI (kalau kamu mau tampilin “periode belum ditutup”)
            'is_closed'  => $isClosed,
            'closing'    => $closing ? [
                'period_start' => $closing->period_start?->toDateString(),
                'period_end'   => $closing->period_end?->toDateString(),
                'closed_at'    => optional($closing->closed_at)->toDateTimeString(),
                'cogs_value'   => (float) $closing->cogs_value,
            ] : null,
        ];
    }

    private function getClosingForRange(string $startDate, string $endDate): ?ClosingPeriod
    {
        return ClosingPeriod::query()
            ->where('period_start', $startDate)
            ->where('period_end', $endDate)
            ->where('is_closed', true)
            ->first();
    }

    // ===================== DATA PEMBELIAN =====================

    private function ambilRekapPembelianBahan(string $mulai, string $akhir)
    {
        return DB::table('pembelian_bahan as pb')
            ->join('pembelian_bahan_detail as d', 'd.pembelian_bahan_id', '=', 'pb.id')
            ->whereBetween('pb.tanggal', [$mulai, $akhir])
            ->selectRaw('
                d.nama_bahan,
                SUM(d.qty_beli) AS qty,
                SUM(d.subtotal) AS total
            ')
            ->groupBy('d.nama_bahan')
            ->orderByDesc('total')
            ->get();
    }

    // ===================== PENJUALAN =====================

    private function ambilRekapProduk(string $mulai, string $akhir)
    {
        return DB::table('penjualan as pjl')
            ->join('penjualan_detail as d', 'd.penjualan_id', '=', 'pjl.id')
            ->join('produk as pr', 'pr.id', '=', 'd.produk_id')
            ->join('payment as pay', 'pay.penjualan_id', '=', 'pjl.id')
            ->where('pay.transaction_status', 'settlement')
            ->whereBetween('pjl.created_at', [$mulai, $akhir])
            ->selectRaw('
                pr.id as product_id,
                pr.nama_barang as name,
                SUM(d.qty) AS qty,
                SUM(d.qty * d.harga) AS total
            ')
            ->groupBy('pr.id', 'pr.nama_barang')
            ->orderByDesc('total')
            ->get();
    }

    private function ambilRekapPembayaran(string $mulai, string $akhir)
    {
        return DB::table('payment as pay')
            ->join('penjualan as pjl', 'pjl.id', '=', 'pay.penjualan_id')
            ->where('pay.transaction_status', 'settlement')
            ->whereBetween('pay.paid_at', [$mulai, $akhir])
            ->selectRaw('pay.pg_payment_type, COUNT(*) trx, SUM(pay.gross_amount) total')
            ->groupBy('pay.pg_payment_type')
            ->orderByDesc('total')
            ->get();
    }

    private function ambilRekapTunaiNonTunai(string $mulai, string $akhir)
    {
        return DB::table('payment as pay')
            ->join('penjualan as pjl', 'pjl.id', '=', 'pay.penjualan_id')
            ->where('pay.transaction_status', 'settlement')
            ->whereBetween('pay.paid_at', [$mulai, $akhir])
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
    }

    // ===================== JURNAL & BUKU BESAR =====================

    private function ambilJurnal(string $mulai, string $akhir)
    {
        return DB::table('journal_entry as je')
            ->join('journal_line as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
            ->whereBetween('je.date', [$mulai, $akhir])
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
    }

    private function susunBukuBesar($jurnal): array
    {
        $bb = [];

        foreach ($jurnal as $row) {
            $key    = $row->code . ' - ' . $row->name;
            $normal = strtoupper((string) $row->normal_side);

            if (!isset($bb[$key])) {
                $bb[$key] = [
                    'normal'       => $normal,
                    'rows'         => [],
                    'total_debit'  => 0,
                    'total_credit' => 0,
                    'balance'      => 0,
                ];
            }

            $change = ($normal === 'DEBIT')
                ? ((float)$row->debit - (float)$row->credit)
                : ((float)$row->credit - (float)$row->debit);

            $bb[$key]['total_debit']  += (float)$row->debit;
            $bb[$key]['total_credit'] += (float)$row->credit;
            $bb[$key]['balance']      += $change;

            $bb[$key]['rows'][] = [
                'date'   => $row->date,
                'entry'  => $row->entry_no,
                'ref'    => $row->ref_no,
                'memo'   => $row->line_memo ?: $row->entry_memo,
                'debit'  => (float)$row->debit,
                'credit' => (float)$row->credit,
                'saldo'  => $bb[$key]['balance'],
            ];
        }

        return $bb;
    }

    // ===================== MISC =====================

    private function rentangTanggal(Request $request): array
    {
        $mulai = $request->get('start_date') ?: now()->toDateString();
        $akhir = $request->get('end_date') ?: now()->toDateString();

        return [$mulai . ' 00:00:00', $akhir . ' 23:59:59'];
    }

    private function bagianLaporan(Request $request): array
    {
        $all = ['labarugi', 'items', 'payments', 'unified', 'journal', 'ledger'];
        $pilih = $request->input('sec', $all);

        if (!is_array($pilih)) $pilih = [$pilih];

        return [
            'labarugi' => in_array('labarugi', $pilih),
            'items'    => in_array('items', $pilih),
            'payments' => in_array('payments', $pilih),
            'unified'  => in_array('unified', $pilih),
            'journal'  => in_array('journal', $pilih),
            'ledger'   => in_array('ledger', $pilih),
        ];
    }

    public function menu(Request $request)
    {
        $start = $request->get('start_date') ?: now()->toDateString();
        $end   = $request->get('end_date') ?: now()->toDateString();

        return view('reports.owner_menu', compact('start', 'end'));
    }
}
