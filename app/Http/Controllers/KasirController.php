<?php

namespace App\Http\Controllers;

use App\Models\Display;
use App\Models\Invoice;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Customer;
use App\Models\StokMutasi;
use App\Services\Payments\PaymentGateway;
use App\Services\JournalPoster;
use App\Services\ReceiptPrinter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    public function __construct(
        private PaymentGateway $gateway,
        private JournalPoster $poster // dipakai untuk delete jurnal HPP realtime (seperti penyesuaian manual)
    ) {}

    public function index()
    {
        // Bereskan sisa transaksi final
        if ($last = Session::get('last_sales_code')) {
            $status = $this->hitungStatus($last);

            // expired/cancelled boleh langsung bersih
            if (in_array($status, ['expired','cancelled'], true)) {
                $this->cleanupSesiDanDisplay($last);
            }

            // paid JANGAN bersih kalau struk belum dicetak
            if ($status === 'paid' && $this->strukSudahDicetak($last)) {
                $this->cleanupSesiDanDisplay($last);
            }
        }

        // Hanya tampilkan produk yang aktif di menu kasir
        $produks = Produk::select('id','nama_barang','harga','kategori','gambar')
            ->where('aktif', true)
            ->orderBy('nama_barang')
            ->get();

        $kategoris = KategoriProduk::query()
            ->join('produk', 'produk.kategori', '=', 'kategori_produk.slug')
            ->where('produk.aktif', true)
            ->select(
                'kategori_produk.slug',
                'kategori_produk.nama',
                DB::raw('MIN(kategori_produk.urutan) as urutan')
            )
            ->groupBy('kategori_produk.slug', 'kategori_produk.nama')
            ->orderBy('urutan')
            ->orderBy('kategori_produk.nama')
            ->get();

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

    private function getPenjualanByKode(string $kode): ?Penjualan
    {
        return Penjualan::where('kode_penjualan', $kode)->first();
    }

    private function getLatestPaymentByPenjualanId(int $penjualanId): ?Payment
    {
        return Payment::where('penjualan_id', $penjualanId)->latest()->first();
    }

    public function dataKeranjang()
    {
        return response()->json($this->simpanKeranjang($this->ambilKeranjang()));
    }

    public function tambahKeKeranjang(Request $req)
    {
        $data = $req->validate(['produk_id'=>'required|integer|exists:produk,id']);
        $p = Produk::select('id','nama_barang','harga','aktif')->findOrFail($data['produk_id']);

        if (! $p->aktif) {
            return response()->json([
                'message' => 'Produk sedang dinonaktifkan dan belum bisa dipilih kasir.',
            ], 422);
        }

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

    /**
     * POTONG BAHAN BAKU OTOMATIS (pakai resep aktif) -> stok_mutasi OUT
     * Pakai StokMutasi::adjustStock biar konsisten dengan penyesuaian manual.
     *
     * Idempotent: kalau sudah ada OUT untuk penjualan ini, tidak diproses lagi.
     */
    private function applyBomStokOut(Penjualan $penjualan): void
    {
        DB::transaction(function () use ($penjualan) {

            $already = StokMutasi::query()
                ->where('sumber_type', Penjualan::class)
                ->where('sumber_id', $penjualan->id)
                ->where('tipe', 'OUT')
                ->exists();

            if ($already) return;

            $details = PenjualanDetail::where('penjualan_id', $penjualan->id)
                ->get(['produk_id','qty']);

            if ($details->isEmpty()) return;

            $tanggal  = Carbon::parse($penjualan->tanggal ?? now());
            $noteBase = "Auto OUT dari penjualan {$penjualan->kode_penjualan}";

            foreach ($details as $d) {
                $produkId = (int) $d->produk_id;
                $qtyMenu  = (int) $d->qty;

                // ambil resep aktif + detail bahan (pakai relasi yang sama seperti penyesuaian manual)
                $produk = Produk::with('resepAktif.details.bahanBaku')->find($produkId);
                $resep  = $produk?->resepAktif;

                if (!$resep || $resep->details->isEmpty()) {
                    // produk belum punya resep aktif -> skip
                    continue;
                }

                foreach ($resep->details as $detail) {
                    $qtyPerPorsi = (float) $detail->qty_per_porsi;
                    if ($qtyPerPorsi <= 0) continue;

                    // yield opsional (kalau null, dianggap 100)
                    $yield = (float)($detail->bahanBaku->yield_persen ?? 100);
                    if ($yield <= 0) $yield = 100;

                    $delta = -1 * ($qtyMenu * $qtyPerPorsi * (100.0 / $yield));
                    if ($delta == 0.0) continue;

                    $mutasi = StokMutasi::adjustStock(
                        bahanBakuId: (int) $detail->bahan_baku_id,
                        deltaQty:    $delta,
                        note:        $noteBase . " | " . ($produk->nama_barang ?? 'Produk'),
                        tanggal:     $tanggal
                    );

                    // tandai sumbernya dari penjualan (supaya bisa di-trace)
                    if ($mutasi) {
                        $mutasi->update([
                            'sumber_type' => Penjualan::class,
                            'sumber_id'   => $penjualan->id,
                        ]);

                        // PERIODIK: tidak posting jurnal HPP realtime
                        $this->poster->deleteFor(StokMutasi::class, $mutasi->id);
                    }
                }
            }
        });
    }

    /**
     * Kalau sudah paid, pastikan jurnal + potong stok sudah dijalankan
     * (aman dipanggil berulang karena idempotent)
     */
    private function ensurePaidSideEffects(string $kode): void
    {
        $pj = $this->getPenjualanByKode($kode);
        if (!$pj) return;

        $pay = $this->getLatestPaymentByPenjualanId($pj->id);
        $paymentType = $pay?->pg_payment_type ?? $pj->metode;

        $this->postJournalPenjualan($pj, $paymentType);
        $this->applyBomStokOut($pj);
    }

    /**
     * PROSES PEMBAYARAN
     */
    public function prosesForm(Request $req)
    {
        try {
            $metode = (string) $req->input('metode');
            if (!in_array($metode, ['cash','qris','va_bca','va_bri','va_bni','tempo'], true)) {
                return $this->failResponse($req, 'Metode pembayaran tidak valid.');
            }

            $cart = $this->ambilKeranjang();
            if (empty($cart['items'])) {
                return $this->failResponse($req, 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
            }

            // Handle customer
            $customerId = null;
            $rawCustomerName = Str::of((string) $req->input('customer_name',''))
                ->squish()
                ->value();

            if ($rawCustomerName !== '') {
                Session::put('pending_customer_name', $rawCustomerName);

                $normalized = Str::of($rawCustomerName)->trim()->lower();
                $customer = Customer::whereRaw('normalized_name = ?', [$normalized])->first();

                if (!$customer) {
                    $customer = Customer::create(['name' => trim($rawCustomerName)]);
                } else {
                    if ($customer->name !== $rawCustomerName) {
                        $customer->name = $rawCustomerName;
                        $customer->save();
                    }
                }

                $customerId = $customer->id;
            } else {
                Session::forget('pending_customer_name');
            }

            // Simpan penjualan
            $dataPenjualan = [
                'tanggal'   => now(),
                'user_id'   => Auth::id() ?: 1,
                'metode'    => $metode,
                'total'     => 0,
                'bayar'     => 0,
                'kembalian' => 0,
            ];
            if (!is_null($customerId)) $dataPenjualan['customer_id'] = $customerId;

            $penjualan = Penjualan::create($dataPenjualan);

            // Simpan detail + hitung subtotal
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
            $penjualan->update(['total' => $subtotal]);

            $kode = $penjualan->kode_penjualan;

            // ===== TEMPO (Bayar Nanti) =====
            if ($metode === 'tempo') {
                $data = $req->validate([
                    'invoice_to_name'    => 'nullable|string|max:255',
                    'invoice_to_company' => 'nullable|string|max:255',
                    'tempo_due_date'     => 'required|date',
                ]);

                $invoiceToName = trim((string) ($data['invoice_to_name'] ?? ''));
                if ($invoiceToName === '') {
                    $invoiceToName = $rawCustomerName;
                }

                $invoiceToCompany = trim((string) ($data['invoice_to_company'] ?? ''));
                $invoiceToCompany = $invoiceToCompany !== '' ? $invoiceToCompany : null;

                if ($invoiceToName === '') {
                    return $this->failResponse($req, 'Untuk Bayar Nanti (Tempo), isi minimal "Nama Pelanggan" atau "Nama Perusahaan".');
                }

                $penjualan->update([
                    'metode'            => 'tempo',
                    'invoice_to_name'   => $invoiceToName,
                    'invoice_to_company'=> $invoiceToCompany,
                    'tempo_due_date'    => $data['tempo_due_date'],
                    'bayar'             => 0,
                    'kembalian'         => 0,
                ]);

                Payment::create([
                    'penjualan_id'       => $penjualan->id,
                    'pg'                 => 'tempo',
                    'pg_transaction_id'  => null,
                    'pg_payment_type'    => 'tempo',
                    'gross_amount'       => $subtotal,
                    'transaction_status' => 'pending',
                    'meta'               => [
                        'order_no'   => $kode,
                        'due_date'   => $data['tempo_due_date'],
                        'invoice_to' => $invoiceToName,
                        'company'    => $invoiceToCompany,
                    ],
                ]);

                Invoice::updateOrCreate(
                    ['penjualan_id' => $penjualan->id],
                    [
                        'nomor_invoice'        => $kode,
                        'nama_toko'            => $invoiceToCompany ?: $invoiceToName,
                        'tanggal_invoice'      => Carbon::parse($penjualan->tanggal)->toDateString(),
                        'tanggal_jatuh_tempo'  => $data['tempo_due_date'],
                        'total_tagihan'        => $subtotal,
                        'status'               => Invoice::STATUS_UNPAID,
                    ]
                );

                // stok tetap dipotong saat transaksi dibuat (idempotent)
                $this->applyBomStokOut($penjualan);

                // transaksi tempo langsung diarahkan ke invoice (tanpa polling status pembayaran)
                Session::forget('cart');
                Session::forget('last_sales_code');

                if ($req->ajax() || $req->wantsJson()) {
                    return response()->json([
                        'ok'          => true,
                        'sales_code'  => $kode,
                        'metode'      => 'tempo',
                        'paid'        => false,
                        'invoice_url' => route('kasir.invoice', ['kode' => $kode]),
                        'message'     => 'Transaksi TEMPO dibuat. Silakan cetak/kirim invoice.',
                    ]);
                }

                return redirect()->route('kasir.invoice', ['kode' => $kode])
                    ->with('success', 'Transaksi TEMPO dibuat. Silakan cetak/kirim invoice.');
            }

            // ===== CASH =====
            if ($metode === 'cash') {
                $req->validate(['cash_tendered' => 'required|integer|min:0']);
                $bayar = (int) $req->input('cash_tendered', 0);
                if ($bayar < $subtotal) {
                    return $this->failResponse($req, 'Uang cash kurang dari total.');
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

                    // paid side effects
                    $this->postJournalPenjualan($penjualan, 'cash');
                    $this->applyBomStokOut($penjualan);
                });

                Display::updateOrCreate(['code'=>'utama'], ['order_no'=>$kode]);
                Session::put('last_sales_code', $kode);

                if ($req->ajax() || $req->wantsJson()) {
                    return response()->json([
                        'ok'         => true,
                        'sales_code' => $kode,
                        'metode'     => 'cash',
                        'paid'       => true,
                        'kembalian'  => $kembalian,
                        'message'    => 'Transaksi CASH berhasil.',
                    ]);
                }

                return redirect()->route('kasir.index')
                    ->with('sales_code', $kode)
                    ->with('success', 'Transaksi CASH berhasil. Kembalian: '.$this->rupiah($kembalian).'. Silakan cetak struk lalu klik "Selesaikan Pembayaran".');
            }

            // ===== NON CASH (Midtrans) =====
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

            Display::updateOrCreate(['code'=>'utama'], ['order_no'=>$kode]);
            Session::put('last_sales_code', $kode);

            if ($req->ajax() || $req->wantsJson()) {
                return response()->json([
                    'ok'         => true,
                    'sales_code' => $kode,
                    'metode'     => $metode,
                    'paid'       => false,
                    'message'    => 'Transaksi dibuat.',
                ]);
            }

            return redirect()->route('kasir.index')
                ->with('sales_code', $kode)
                ->with('success', 'Transaksi dibuat. Layar customer otomatis menampilkan order.');

        } catch (\Throwable $e) {
            Log::error('Kasir prosesForm error', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->failResponse($req, 'Gagal memproses pembayaran: '.$e->getMessage());
        }
    }

    public function batal(Request $req, string $kode)
    {
        try {
            $pj = Penjualan::where('kode_penjualan', $kode)->first();
            if (!$pj) {
                return response()->json(['ok'=>false,'message'=>'Transaksi tidak ditemukan.'], 404);
            }

            $pay = Payment::where('penjualan_id', $pj->id)->latest()->first();
            if ($pay && !in_array(strtolower((string)$pay->transaction_status), ['settlement','capture'], true)) {
                $pay->update(['transaction_status' => 'cancel']);
            }

            Display::where('code','utama')->where('order_no',$kode)->update(['order_no' => null]);

            Session::forget('cart');
            Session::forget('pending_customer_name');
            Session::forget('last_sales_code');

            return response()->json(['ok'=>true,'message'=>'Transaksi dibatalkan.']);

        } catch (\Throwable $e) {
            Log::warning('Kasir batal error', ['kode'=>$kode,'msg'=>$e->getMessage()]);
            return response()->json(['ok'=>false,'message'=>'Gagal membatalkan.'], 500);
        }
    }

    public function statusPenjualan(string $kode)
    {
        $status = $this->hitungStatus($kode);
        $pj = $this->getPenjualanByKode($kode);

        if ($status === 'pending') {
            try {
                if (method_exists($this->gateway, 'status')) {
                    $res = $this->gateway->status($kode);

                    PaymentLog::create([
                        'event'   => 'status_poll',
                        'payload' => json_encode($res),
                    ]);

                    $pgStatus = strtolower((string)($res['transaction_status'] ?? 'pending'));

                    $pj = $this->getPenjualanByKode($kode);
                    if ($pj) {
                        $pay = $this->getLatestPaymentByPenjualanId($pj->id);

                        if ($pay && $pgStatus && $pgStatus !== $pay->transaction_status) {
                            $updateData = ['transaction_status' => $pgStatus];
                            if (in_array($pgStatus, ['settlement','capture'], true)) {
                                $updateData['paid_at'] = now();
                            }
                            $pay->update($updateData);
                        }

                        if (in_array($pgStatus, ['settlement','capture'], true)) {
                            $pj->update([
                                'bayar'     => (int)$pj->total,
                                'kembalian' => 0,
                            ]);
                            $status = 'paid';
                        } elseif ($pgStatus === 'expire') {
                            $status = 'expired';
                        } elseif (in_array($pgStatus, ['cancel','deny'], true)) {
                            $status = 'cancelled';
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('statusPenjualan fallback error', [
                    'kode' => $kode,
                    'msg'  => $e->getMessage(),
                ]);
            }
        }

        // SAFEGUARD: kalau sudah paid, pastikan jurnal + potong stok sudah jalan
        if ($status === 'paid') {
            try {
                $this->ensurePaidSideEffects($kode);
            } catch (\Throwable $ex) {
                Log::warning('ensurePaidSideEffects gagal', [
                    'kode' => $kode,
                    'msg'  => $ex->getMessage(),
                ]);
            }
        }

        $total = (int) (Penjualan::where('kode_penjualan',$kode)->value('total') ?? 0);

        if (in_array($status, ['expired','cancelled'], true)) {
            $this->cleanupSesiDanDisplay($kode);
        }

        if ($status === 'paid' && $this->strukSudahDicetak($kode)) {
            $this->cleanupSesiDanDisplay($kode);
        }

        return response()->json([
            'kode_penjualan' => $kode,
            'status'         => $status,
            'metode'         => $pj ? $pj->metode : 'cash',
            'grand_total'    => $total,
        ])->header('Cache-Control','no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma','no-cache');
    }

    private function hitungStatus(string $kode): string
    {
        $pj = $this->getPenjualanByKode($kode);
        if (!$pj) return 'pending';

        if ((int)$pj->bayar >= (int)$pj->total && (int)$pj->total > 0) {
            return 'paid';
        }

        $pay = $this->getLatestPaymentByPenjualanId($pj->id);
        if (!$pay) return 'pending';

        $st = strtolower((string)($pay->transaction_status ?? 'pending'));
        return match ($st) {
            'settlement','capture' => 'paid',
            'expire'               => 'expired',
            'cancel','deny'        => 'cancelled',
            default                => 'pending',
        };
    }

    // ===== jurnal (punyamu) =====

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
        if ($amount <= 0) return;

        $already = DB::table('journal_entry')
            ->where('source_type', Penjualan::class)
            ->where('source_id', $penjualan->id)
            ->exists();

        if ($already) return;

        $debitAccountId = match ($paymentType) {
            'cash'                       => 1,
            'qris'                       => 2,
            'va_bca', 'va_bri', 'va_bni' => 3,
            default                      => 1,
        };

        $creditAccountId = 19;

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

    private function strukSudahDicetak(string $kode): bool
    {
        return (bool) (Penjualan::where('kode_penjualan', $kode)->value('struk_dicetak') ?? false);
    }

    public function cetakStruk(string $kode)
    {
        $status = $this->hitungStatus($kode);
        if ($status !== 'paid') abort(403, 'Pembayaran belum lunas.');

        $penjualan = Penjualan::with(['details.produk', 'user', 'customer'])
            ->where('kode_penjualan', $kode)
            ->firstOrFail();

        $payment = Payment::where('penjualan_id', $penjualan->id)->latest()->first();

        // Diarahkan ke file struk.blade.php
        return view('kasir.struk', compact('penjualan', 'payment'));
    }

    public function printStruk(string $kode, ReceiptPrinter $receiptPrinter)
    {
        $penjualan = Penjualan::with(['details.produk', 'user', 'customer'])
            ->where('kode_penjualan', $kode)
            ->firstOrFail();

        $status = $this->hitungStatus($kode);
        if ($status !== 'paid') {
            return response()->json(['message' => 'Pembayaran belum lunas.'], 422);
        }

        $payment = Payment::where('penjualan_id', $penjualan->id)->latest()->first();

        try {
            $receiptPrinter->print($penjualan, $payment);
        } catch (\Throwable $e) {
            Log::error('Gagal mencetak struk ke printer thermal', [
                'kode' => $kode,
                'printer' => config('receipt_printer.destination', 'POS-Printer'),
                'msg' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal mencetak struk ke printer ' . config('receipt_printer.printer_name', 'POS-Printer') . '.',
                'detail' => $e->getMessage(),
            ], 500);
        }

        $penjualan->forceFill([
            'struk_dicetak' => true,
            'struk_dicetak_at' => now(),
        ])->save();

        $this->cleanupSesiDanDisplay($kode);

        return response()->json([
            'message' => 'Struk berhasil dicetak ke printer ' . config('receipt_printer.printer_name', 'POS-Printer') . '.',
        ]);
    }

    public function selesaiCetak(string $kode)
    {
        $penjualan = Penjualan::where('kode_penjualan', $kode)->firstOrFail();

        $status = $this->hitungStatus($kode);
        if ($status !== 'paid') {
            return response()->json(['message' => 'Pembayaran belum lunas.'], 422);
        }

        $penjualan->forceFill([
            'struk_dicetak'    => true,
            'struk_dicetak_at' => now(),
        ])->save();

        $this->cleanupSesiDanDisplay($kode);

        return response()->json(['message' => 'OK']);
    }

    public function invoice(Request $req, string $kode)
{
    $penjualan = Penjualan::with(['details.produk', 'user', 'customer'])
        ->where('kode_penjualan', $kode)
        ->firstOrFail();

    // Invoice boleh untuk TEMPO (belum lunas) atau transaksi yang sudah paid
    $status = $this->hitungStatus($kode);
    if ($penjualan->metode !== 'tempo' && $status !== 'paid') {
        abort(403, 'Halaman ini hanya untuk transaksi Tempo atau yang sudah lunas.');
    }

    $payment = Payment::where('penjualan_id', $penjualan->id)->latest()->first();
    $printMode = (bool) $req->boolean('print', false);

    // Diarahkan ke file invoice.blade.php
    return view('kasir.invoice', compact('penjualan', 'payment', 'printMode'));
}

    private function failResponse(Request $req, string $message)
    {
        if ($req->ajax() || $req->wantsJson()) {
            return response()->json(['ok'=>false,'message'=>$message], 422);
        }
        return back()->with('error', $message);
    }
}
