<?php

namespace App\Http\Controllers;

use App\Models\Display;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Customer; // penting: pakai model Customer
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class KasirController extends Controller
{
    public function __construct(private PaymentGateway $gateway) {}

    public function index()
    {
        // Bereskan sisa transaksi final
        if ($last = Session::get('last_sales_code')) {
            $status = $this->hitungStatus($last);
            if (in_array($status, ['paid','expired','cancelled'], true)) {
                $this->cleanupSesiDanDisplay($last);
            }
        }

        $produks = Produk::select('id','nama_barang','harga','stok','kategori')
            ->whereRaw('COALESCE(stok,0) > 0')
            ->orderBy('nama_barang')->get();

        $kategoris = Produk::whereNotNull('kategori')
            ->select('kategori')->distinct()->orderBy('kategori')->pluck('kategori');

        // Kode aktif untuk memicu polling di view
        $activeCode = session('sales_code') ?? Session::get('last_sales_code');

        return view('kasir.index', [
            'produks'     => $produks,
            'kategoris'   => $kategoris,
            'pendingName' => Session::get('pending_customer_name'),
            'activeCode'  => $activeCode,
        ]);
    }

    private function cleanupSesiDanDisplay(string $kode): void
    {
        Session::forget('cart');
        Session::forget('pending_customer_name');
        Session::forget('last_sales_code');

        Display::where('code','utama')->where('order_no',$kode)->update(['order_no' => null]);
    }

    private function ambilKeranjang(): array
    {
        $cart = Session::get('cart', ['items'=>[]]);
        if (!isset($cart['items']) || !is_array($cart['items'])) $cart = ['items'=>[]];
        return $cart;
    }

    private function simpanKeranjang(array $cart): array
    {
        $subtotal = 0; $count = 0; $items = [];
        foreach ($cart['items'] as $row) {
            $line = (int)$row['price'] * (int)$row['qty'];
            $subtotal += $line; $count += (int)$row['qty'];
            $items[] = [
                'produk_id'       => (int)$row['id'],
                'name'            => $row['name'],
                'price'           => (int)$row['price'],
                'qty'             => (int)$row['qty'],
                'line_total'      => $line,
                'price_text'      => $this->rupiah((int)$row['price']),
                'line_total_text' => $this->rupiah($line),
            ];
        }
        Session::put('cart', ['items'=>$cart['items']]);

        return [
            'items'         => $items,
            'subtotal'      => $subtotal,
            'subtotal_text' => $this->rupiah($subtotal),
            'count'         => $count,
        ];
    }

    private function rupiah(int $n): string
    {
        return 'Rp ' . number_format($n,0,',','.');
    }

    public function dataKeranjang()
    {
        return response()->json($this->simpanKeranjang($this->ambilKeranjang()));
    }

    public function tambahKeKeranjang(Request $req)
    {
        $data = $req->validate(['produk_id'=>'required|integer|exists:produk,id']);
        $p = Produk::select('id','nama_barang','harga')->findOrFail($data['produk_id']);

        $cart = $this->ambilKeranjang();
        $items =& $cart['items'];
        if (!isset($items[$p->id])) {
            $items[$p->id] = ['id'=>$p->id,'name'=>$p->nama_barang,'price'=>(int)$p->harga,'qty'=>1];
        } else {
            $items[$p->id]['qty'] = (int)$items[$p->id]['qty'] + 1;
        }
        return response()->json($this->simpanKeranjang($cart));
    }

    public function kurangKeranjang(Request $req)
    {
        $data = $req->validate(['produk_id'=>'required|integer']);
        $cart = $this->ambilKeranjang();
        $items =& $cart['items'];
        if (isset($items[$data['produk_id']])) {
            $items[$data['produk_id']]['qty'] = max(0,(int)$items[$data['produk_id']]['qty'] - 1);
            if ($items[$data['produk_id']]['qty'] === 0) unset($items[$data['produk_id']]);
        }
        return response()->json($this->simpanKeranjang($cart));
    }

    public function hapusDariKeranjang(Request $req)
    {
        $data = $req->validate(['produk_id'=>'required|integer']);
        $cart = $this->ambilKeranjang();
        unset($cart['items'][$data['produk_id']]);
        return response()->json($this->simpanKeranjang($cart));
    }

    public function kosongkanKeranjang()
    {
        Session::forget('cart');
        Session::forget('pending_customer_name');
        return response()->json($this->simpanKeranjang(['items'=>[]]));
    }

    public function prosesForm(Request $req)
    {
        try {
            $metode = (string) $req->input('metode');
            if (!in_array($metode, ['cash','qris','va_bca','va_bri','va_bni'], true)) {
                return back()->with('error', 'Metode pembayaran tidak valid.');
            }

            $cart = $this->ambilKeranjang();
            if (empty($cart['items'])) {
                return back()->with('error', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
            }

            // Handle customer (tabel customer, kolom name, normalized_name)
            $customerId = null;
            $rawCustomerName = Str::of((string) $req->input('customer_name',''))
                ->squish()
                ->value();

            if ($rawCustomerName !== '') {
                // simpan di session supaya balik ke form masih keisi
                Session::put('pending_customer_name', $rawCustomerName);

                // normalisasi sama dengan generated column normalized_name
                $normalized = Str::of($rawCustomerName)->trim()->lower();

                // cari berdasarkan normalized_name (supaya "Manusia" dan "manusia" ketemu yang sama)
                $customer = Customer::whereRaw('normalized_name = ?', [$normalized])->first();

                if (! $customer) {
                    // kalau belum ada, buat baru
                    $customer = Customer::create([
                        'name' => trim($rawCustomerName),
                    ]);
                } else {
                    // opsional: kalau nama aslinya beda kapitalisasi, update biar rapi
                    if ($customer->name !== $rawCustomerName) {
                        $customer->name = $rawCustomerName;
                        $customer->save();
                    }
                }

                $customerId = $customer->id;
            } else {
                // kalau input kosong, hapus pending name
                Session::forget('pending_customer_name');
            }

            // Simpan penjualan dan detail
            $dataPenjualan = [
                'tanggal'   => now(),
                'user_id'   => Auth::id() ?: 1,
                'metode'    => $metode,
                'total'     => 0,
                'bayar'     => 0,
                'kembalian' => 0,
            ];

            if (!is_null($customerId)) {
                $dataPenjualan['customer_id'] = $customerId;
            }

            $penjualan = Penjualan::create($dataPenjualan);

            $subtotal = 0;
            foreach ($cart['items'] as $row) {
                $line = (int)$row['price'] * (int)$row['qty'];
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id'    => (int)$row['id'],
                    'harga'        => (int)$row['price'],
                    'qty'          => (int)$row['qty'],
                    'subtotal'     => $line,
                ]);
                $subtotal += $line;
            }
            $penjualan->update(['total'=>$subtotal]);

            $kode = $penjualan->kode_penjualan;

            // Pembayaran CASH
            if ($metode === 'cash') {
                $req->validate(['cash_tendered' => 'required|integer|min:0']);
                $bayar = (int) $req->input('cash_tendered', 0);
                if ($bayar < $subtotal) {
                    return back()->with('error', 'Uang cash kurang dari total.');
                }
                $kembalian = $bayar - $subtotal;

                DB::transaction(function () use ($penjualan, $kode, $subtotal, $bayar, $kembalian) {
                    $penjualan->update([
                        'bayar'     => $bayar,
                        'kembalian' => $kembalian,
                        'metode'    => 'cash',
                    ]);

                    Payment::create([
                        'penjualan_id'       => $penjualan->id,
                        'kode_penjualan'     => $kode,
                        'pg'                 => 'cash',
                        'pg_transaction_id'  => null,
                        'pg_payment_type'    => 'cash',
                        'gross_amount'       => $subtotal,
                        'transaction_status' => 'settlement',
                        'meta'               => [
                            'order_no'      => $kode,
                            'cash_tendered' => $bayar,
                            'change'        => $kembalian,
                        ],
                        'paid_at'            => now(),
                    ]);

                    // Jurnal otomatis cash
                    $this->postJournalPenjualan($penjualan, 'cash');
                });

                $this->cleanupSesiDanDisplay($kode);

                return redirect()->route('kasir.index')
                    ->with('success', 'Transaksi CASH berhasil. Kembalian: '.$this->rupiah($kembalian));
            }

            // Pembayaran NON CASH → Midtrans (QRIS / VA)
            $itemDetails = $penjualan->details()
                ->with('produk:id,nama_barang')
                ->get()
                ->map(function($d){
                    return [
                        'id'       => 'SKU-'.$d->produk_id,
                        'price'    => (int)$d->harga,
                        'quantity' => (int)$d->qty,
                        'name'     => $d->produk?->nama_barang ?? 'Item',
                    ];
                })->toArray();

            if ($metode === 'qris') {
                $res = $this->gateway->chargeQris($kode, (int)$penjualan->total, $itemDetails);
                PaymentLog::create([
                    'event'   => 'charge_qris',
                    'payload' => json_encode($res),
                ]);

                $qrUrl    = collect($res['actions'] ?? [])->firstWhere('name','generate-qr-code')['url'] ?? null;
                $qrString = $res['qr_string'] ?? null;

                Payment::create([
                    'penjualan_id'       => $penjualan->id,
                    'kode_penjualan'     => $kode,
                    'pg'                 => 'midtrans',
                    'pg_transaction_id'  => $res['transaction_id'] ?? null,
                    'pg_payment_type'    => 'qris',
                    'gross_amount'       => $penjualan->total,
                    'transaction_status' => $res['transaction_status'] ?? 'pending',
                    'meta'               => [
                        'order_no'  => $kode,
                        'qr_url'    => $qrUrl,
                        'qr_string' => $qrString,
                    ],
                ]);
            } else {
                $bank = substr($metode, 3); // bca/bri/bni
                $res  = $this->gateway->chargeVa($kode, (int)$penjualan->total, $bank);
                PaymentLog::create([
                    'event'   => 'charge_va_'.$bank,
                    'payload' => json_encode($res),
                ]);

                $vaNumber = $res['va_numbers'][0]['va_number'] ?? null;

                Payment::create([
                    'penjualan_id'       => $penjualan->id,
                    'kode_penjualan'     => $kode,
                    'pg'                 => 'midtrans',
                    'pg_transaction_id'  => $res['transaction_id'] ?? null,
                    'pg_payment_type'    => 'va_'.$bank,
                    'gross_amount'       => $penjualan->total,
                    'transaction_status' => $res['transaction_status'] ?? 'pending',
                    'meta'               => [
                        'order_no'  => $kode,
                        'va_number' => $vaNumber,
                    ],
                ]);
            }

            // tampilkan di layar customer
            Display::updateOrCreate(['code'=>'utama'], ['order_no'=>$kode]);
            Session::put('last_sales_code', $kode);

            return redirect()->route('kasir.index')
                ->with('sales_code', $kode)
                ->with('success', 'Transaksi dibuat. Layar customer otomatis menampilkan order.');

        } catch (\Throwable $e) {
            Log::error('Kasir prosesForm error', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', 'Gagal memproses pembayaran: '.$e->getMessage());
        }
    }

    public function statusPenjualan(string $kode)
    {
        $status = $this->hitungStatus($kode);

        if ($status === 'pending') {
            try {
                if (method_exists($this->gateway, 'status')) {
                    $res = $this->gateway->status($kode);
                    PaymentLog::create([
                        'event'   => 'status_poll',
                        'payload' => json_encode($res),
                    ]);

                    $pgStatus = strtolower((string)($res['transaction_status'] ?? 'pending'));

                    $pay = Payment::where('kode_penjualan',$kode)->latest()->first();
                    if ($pay && $pgStatus && $pgStatus !== $pay->transaction_status) {
                        $updateData = ['transaction_status'=>$pgStatus];
                        if (in_array($pgStatus, ['settlement','capture'], true)) {
                            $updateData['paid_at'] = now();
                        }
                        $pay->update($updateData);
                    }

                    if (in_array($pgStatus, ['settlement','capture'], true)) {
                        $pj = Penjualan::where('kode_penjualan',$kode)->first();
                        if ($pj) {
                            $pj->update([
                                'bayar'     => (int)$pj->total,
                                'kembalian' => 0,
                            ]);
                        }
                        $status = 'paid';
                    } elseif ($pgStatus === 'expire') {
                        $status = 'expired';
                    } elseif (in_array($pgStatus, ['cancel','deny'], true)) {
                        $status = 'cancelled';
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('statusPenjualan fallback error', [
                    'kode' => $kode,
                    'msg'  => $e->getMessage(),
                ]);
            }
        }

        $total = (int) (Penjualan::where('kode_penjualan',$kode)->value('total') ?? 0);

        // Jurnal otomatis untuk non-cash saat status sudah paid
        if ($status === 'paid') {
            try {
                $pj = Penjualan::where('kode_penjualan',$kode)->first();
                if ($pj) {
                    $pay = Payment::where('kode_penjualan',$kode)->latest()->first();
                    $paymentType = $pay?->pg_payment_type ?? $pj->metode;

                    $this->postJournalPenjualan($pj, $paymentType);
                }
            } catch (\Throwable $ex) {
                Log::warning('Gagal auto jurnal penjualan', [
                    'kode' => $kode,
                    'msg'  => $ex->getMessage(),
                ]);
            }
        }

        if (in_array($status, ['paid','expired','cancelled'], true)) {
            $this->cleanupSesiDanDisplay($kode);
        }

        return response()->json([
            'kode_penjualan' => $kode,
            'status'         => $status,
            'grand_total'    => $total,
        ])->header('Cache-Control','no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma','no-cache');
    }

    private function hitungStatus(string $kode): string
    {
        $pj = Penjualan::where('kode_penjualan',$kode)->first();
        if (!$pj) return 'pending';

        if ((int)$pj->bayar >= (int)$pj->total && (int)$pj->total > 0) {
            return 'paid';
        }

        $pay = Payment::where('kode_penjualan',$kode)->latest()->first()
             ?: Payment::where('meta->order_no',$kode)->latest()->first();

        if (!$pay) {
            return 'pending';
        }

        $st = strtolower((string)($pay->transaction_status ?? 'pending'));
        return match ($st) {
            'settlement','capture' => 'paid',
            'expire'               => 'expired',
            'cancel','deny'        => 'cancelled',
            default                => 'pending',
        };
    }

    // Fungsi bantuan auto jurnal penjualan

    private function generateJournalEntryNo(string $date): string
    {
        $prefix = 'JU-' . date('Ym', strtotime($date)) . '-';

        $lastNo = DB::table('journal_entry')
            ->where('entry_no', 'like', $prefix.'%')
            ->orderByDesc('entry_no')
            ->value('entry_no');

        $next = 1;
        if ($lastNo) {
            $next = (int) substr($lastNo, -4) + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function postJournalPenjualan(Penjualan $penjualan, string $paymentType): void
    {
        $amount = (int) $penjualan->total;
        if ($amount <= 0) {
            return;
        }

        // Cegah jurnal dobel untuk penjualan yang sama
        $already = DB::table('journal_entry')
            ->where('source_type', Penjualan::class)
            ->where('source_id', $penjualan->id)
            ->exists();

        if ($already) {
            return;
        }

        // Mapping akun
        $debitAccountId = match ($paymentType) {
            'cash'                       => 1, // 1001 Kas
            'qris'                       => 2, // 1002 Bank
            'va_bca', 'va_bri', 'va_bni' => 3, // 1101 Piutang Usaha
            default                      => 1,
        };

        $creditAccountId = 19; // 4001 Penjualan Minuman

        $date = $penjualan->tanggal
            ? date('Y-m-d', strtotime($penjualan->tanggal))
            : now()->toDateString();

        $entryNo    = $this->generateJournalEntryNo($date);
        $headerMemo = 'Penjualan '.$penjualan->kode_penjualan;

        $debitAcc  = DB::table('chart_of_account')->where('id', $debitAccountId)->first();
        $creditAcc = DB::table('chart_of_account')->where('id', $creditAccountId)->first();

        $debitName  = $debitAcc?->name  ?? 'Debit';
        $creditName = $creditAcc?->name ?? 'Kredit';

        $entryId = DB::table('journal_entry')->insertGetId([
            'entry_no'    => $entryNo,
            'date'        => $date,
            'ref_no'      => $penjualan->kode_penjualan,
            'memo'        => $headerMemo,
            'source_type' => Penjualan::class,
            'source_id'   => $penjualan->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('journal_line')->insert([
            [
                'journal_entry_id' => $entryId,
                'account_id'       => $debitAccountId,
                'debit'            => $amount,
                'credit'           => 0,
                'memo'             => "Penjualan {$penjualan->kode_penjualan} (Lawan: {$creditName})",
                'line_no'          => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'journal_entry_id' => $entryId,
                'account_id'       => $creditAccountId,
                'debit'            => 0,
                'credit'           => $amount,
                'memo'             => "Penjualan {$penjualan->kode_penjualan} (Lawan: {$debitName})",
                'line_no'          => 2,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }
}
