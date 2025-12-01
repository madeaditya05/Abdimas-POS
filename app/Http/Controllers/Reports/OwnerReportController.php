<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OwnerReportController extends Controller
{
    public function index(Request $request)
    {
        // Tentukan rentang tanggal
        [$mulai, $akhir] = $this->rentangTanggal($request);
        $bagian = $this->bagianLaporan($request);

        // Data rekap (sama seperti kasir)
        $items      = $this->ambilRekapProduk($mulai, $akhir);
        $payments   = $this->ambilRekapPembayaran($mulai, $akhir);
        $payUnified = $this->ambilRekapTunaiNonTunai($mulai, $akhir);
        $jurnal     = $this->ambilJurnal($mulai, $akhir);
        $bukuBesar  = $this->susunBukuBesar($jurnal);

        // Laba rugi dari jurnal
        $labarugi   = $this->hitungLabaRugi($mulai, $akhir);

        $meta = [
            'start' => $mulai,
            'end'   => $akhir,
        ];

        return view('reports.owner_labarugi', [
            'lr'        => $labarugi,
            'items'     => $items,
            'payments'  => $payments,
            'payUnified'=> $payUnified,
            'journal'   => $jurnal,
            'ledger'    => $bukuBesar,
            'meta'      => $meta,
            'sections'  => $bagian,
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
        $labarugi   = $this->hitungLabaRugi($mulai, $akhir);

        $meta = [
            'start' => $mulai,
            'end'   => $akhir,
        ];

        $pdf = Pdf::loadView('reports.pdf.owner_labarugi_pdf', [
            'lr'        => $labarugi,
            'items'     => $items,
            'payments'  => $payments,
            'payUnified'=> $payUnified,
            'journal'   => $jurnal,
            'ledger'    => $bukuBesar,
            'meta'      => $meta,
            'sections'  => $bagian,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Laporan-Owner_{$mulai}_sd_{$akhir}.pdf");
    }

    // ===================== FUNGSI BANTU (bahasa Indonesia) =====================

    // Hitung laba rugi dari jurnal
    private function hitungLabaRugi(string $mulai, string $akhir): array
    {
        // Karena di rentangTanggal kita tambahkan jam,
        // di sini cukup ambil bagian tanggalnya saja (YYYY-MM-DD)
        $mulaiTanggal = substr($mulai, 0, 10);
        $akhirTanggal = substr($akhir, 0, 10);

        // Ambil total debit/kredit per akun
        $rows = DB::table('journal_line as jl')
            ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
            ->join('journal_entry as je', 'je.id', '=', 'jl.journal_entry_id')
            ->whereBetween('je.date', [$mulaiTanggal, $akhirTanggal])
            ->select(
                'coa.code',
                'coa.name',
                'coa.type',              // asset, liability, equity, revenue, expense
                DB::raw('SUM(jl.debit) as debit'),
                DB::raw('SUM(jl.credit) as credit')
            )
            ->groupBy('coa.code', 'coa.name', 'coa.type')
            ->get();

        $pendapatan = 0; // kelompok akun revenue 4xxx
        $hpp        = 0; // kelompok akun HPP 5xxx
        $beban      = 0; // kelompok akun beban 6xxx dst

        foreach ($rows as $row) {
            $debit  = (float) $row->debit;
            $credit = (float) $row->credit;

            // Pendapatan → type = 'revenue'
            if ($row->type === 'revenue') {
                // normalnya di kredit, retur/potongan di debit akan mengurangi
                $pendapatan += ($credit - $debit);
            }

            // Beban / HPP → type = 'expense'
            if ($row->type === 'expense') {
                // kode 5xxx kita anggap HPP
                if (substr($row->code, 0, 1) === '5') {
                    $hpp += ($debit - $credit);
                } else {
                    // selain 5xxx adalah beban operasional
                    $beban += ($debit - $credit);
                }
            }
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
        ];
    }

    // Rekap penjualan per produk (sama seperti kasir)
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

    // Rekap per metode pembayaran
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

    // Rekap tunai vs non tunai
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

    // Ambil jurnal umum
    private function ambilJurnal(string $mulai, string $akhir)
    {
        // di sini pakai $mulai/$akhir langsung (ada jam-nya tidak masalah)
        return DB::table('journal_entry as je')
            ->join('journal_line as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
            ->whereBetween('je.date', [substr($mulai, 0, 10), substr($akhir, 0, 10)])
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

    // Susun buku besar dari jurnal
    private function susunBukuBesar($jurnal): array
    {
        $bukuBesar = [];

        foreach ($jurnal as $row) {
            $key    = $row->code . ' - ' . $row->name;
            $normal = strtoupper($row->normal_side); // DEBIT / CREDIT

            if (!isset($bukuBesar[$key])) {
                $bukuBesar[$key] = [
                    'normal'        => $normal,
                    'rows'          => [],
                    'total_debit'   => 0,
                    'total_credit'  => 0,
                    'balance'       => 0,
                ];
            }

            // DEBIT normal → saldo naik kalau debit
            // CREDIT normal → saldo naik kalau kredit
            $perubahanSaldo = ($normal === 'DEBIT')
                ? ($row->debit - $row->credit)
                : ($row->credit - $row->debit);

            $bukuBesar[$key]['balance']      += $perubahanSaldo;
            $bukuBesar[$key]['total_debit']  += (float) $row->debit;
            $bukuBesar[$key]['total_credit'] += (float) $row->credit;

            $bukuBesar[$key]['rows'][] = [
                'date'   => $row->date,
                'entry'  => $row->entry_no,
                'ref'    => $row->ref_no,
                'memo'   => $row->line_memo ?: $row->entry_memo,
                'debit'  => (float) $row->debit,
                'credit' => (float) $row->credit,
                'saldo'  => $bukuBesar[$key]['balance'],
            ];
        }

        return $bukuBesar;
    }

    // Rentang tanggal dari form
    private function rentangTanggal(Request $request): array
    {
        $mulai = $request->get('start_date') ?: now()->toDateString();
        $akhir = $request->get('end_date') ?: now()->toDateString();

        return [$mulai . ' 00:00:00', $akhir . ' 23:59:59'];
    }

    // Bagian laporan yang dipilih
    private function bagianLaporan(Request $request): array
    {
        $semua = ['labarugi', 'items', 'payments', 'unified', 'journal', 'ledger'];
        $pilih = $request->input('sec', $semua);

        if (!is_array($pilih)) {
            $pilih = [$pilih];
        }

        return [
            'labarugi' => in_array('labarugi', $pilih),
            'items'    => in_array('items', $pilih),
            'payments' => in_array('payments', $pilih),
            'unified'  => in_array('unified', $pilih),
            'journal'  => in_array('journal', $pilih),
            'ledger'   => in_array('ledger', $pilih),
        ];
    }
}
