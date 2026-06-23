<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\KategoriProduk;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Services\ProdukCatalogSyncService;
use App\Services\ReceiptPrinter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    public function __construct(
        private ProdukCatalogSyncService $produkCatalogSync
    ) {}

    public function index()
    {
        $this->produkCatalogSync->syncFromStorage();

        $produks = Produk::select('id', 'nama_barang', 'harga', 'harga_online', 'kategori', 'gambar')
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

        return view('kasir.index', [
            'produks' => $produks,
            'kategoris' => $kategoris,
            'pendingName' => Session::get('pending_customer_name'),
            'activeCode' => null,
        ]);
    }

    private function cleanupSesi(?string $kode = null): void
    {
        Session::forget('cart');
        Session::forget('pending_customer_name');
        Session::forget('last_sales_code');
    }

    private function ambilKeranjang(): array
    {
        $cart = Session::get('cart', ['items' => []]);
        if (! isset($cart['items']) || ! is_array($cart['items'])) {
            $cart = ['items' => []];
        }

        return $cart;
    }

    private function simpanKeranjang(array $cart): array
    {
        $channel = Session::get('pos_channel', 'offline');
        $subtotal = 0;
        $count = 0;
        $items = [];

        foreach ($cart['items'] as $row) {
            $price = (int) ($channel === 'online' ? ($row['price_online'] ?? $row['price']) : $row['price']);
            $line = $price * (int) $row['qty'];
            $subtotal += $line;
            $count += (int) $row['qty'];
            $items[] = [
                'produk_id' => (int) $row['id'],
                'name' => $row['name'],
                'price' => $price,
                'price_offline' => (int) $row['price'],
                'price_online' => (int) ($row['price_online'] ?? $row['price']),
                'qty' => (int) $row['qty'],
                'line_total' => $line,
                'price_text' => $this->rupiah($price),
                'line_total_text' => $this->rupiah($line),
            ];
        }

        Session::put('cart', ['items' => $cart['items']]);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'subtotal_text' => $this->rupiah($subtotal),
            'count' => $count,
        ];
    }

    private function rupiah(int $n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    private function getPenjualanByKode(string $kode): ?Penjualan
    {
        return Penjualan::where('kode_penjualan', $kode)->first();
    }

    private function findCustomerByNormalizedName(string $customerName): ?Customer
    {
        $normalized = Customer::normalizeName($customerName);
        if (! $normalized) {
            return null;
        }

        return Customer::where('normalized_name', $normalized)->first();
    }

    private function customerDiscountSummary(?Customer $customer, int $subtotal, ?int $excludePenjualanId = null): array
    {
        $subtotal = max(0, $subtotal);

        if (! $customer) {
            return [
                'exists' => false,
                'purchase_count' => 0,
                'min_transactions' => 10,
                'configured_percent' => 0,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'total_after_discount' => $subtotal,
                'eligible' => false,
                'remaining_transactions' => 10,
            ];
        }

        $purchaseQuery = $customer->completedPenjualans();
        if ($excludePenjualanId) {
            $purchaseQuery->where('id', '!=', $excludePenjualanId);
        }

        $purchaseCount = (int) $purchaseQuery->count();
        $minTransactions = max(1, (int) (\App\Models\Setting::get('discount_min_transactions', 10)));
        $configuredPercent = max(0, min(99.99, (float) (\App\Models\Setting::get('discount_percent', 0))));
        $discountPercent = $customer->eligibleDiscountPercent($purchaseCount);
        $discountAmount = $discountPercent > 0
            ? (int) floor($subtotal * $discountPercent / 100)
            : 0;
        $discountAmount = min($subtotal, max(0, $discountAmount));

        if ($purchaseCount > 0 && $purchaseCount % $minTransactions === 0) {
            $remainingTransactions = 0;
        } else {
            $nextTarget = (floor($purchaseCount / $minTransactions) + 1) * $minTransactions;
            $remainingTransactions = (int) ($nextTarget - $purchaseCount);
        }

        return [
            'exists' => true,
            'purchase_count' => $purchaseCount,
            'min_transactions' => $minTransactions,
            'configured_percent' => $configuredPercent,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'total_after_discount' => max(0, $subtotal - $discountAmount),
            'eligible' => $discountPercent > 0,
            'remaining_transactions' => $remainingTransactions,
        ];
    }

    public function customerDiscountInfo(Request $req)
    {
        $data = $req->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'subtotal' => ['nullable', 'integer', 'min:0'],
        ]);

        $customerName = Str::of((string) ($data['customer_name'] ?? ''))->squish()->value();
        $subtotal = (int) ($data['subtotal'] ?? 0);
        $customer = $customerName !== '' ? $this->findCustomerByNormalizedName($customerName) : null;

        return response()->json($this->customerDiscountSummary($customer, $subtotal));
    }

    public function dataKeranjang()
    {
        return response()->json($this->simpanKeranjang($this->ambilKeranjang()));
    }

    public function tambahKeKeranjang(Request $req)
    {
        $data = $req->validate(['produk_id' => 'required|integer|exists:produk,id']);
        $p = Produk::select('id', 'nama_barang', 'harga', 'harga_online', 'aktif')->findOrFail($data['produk_id']);

        if (! $p->aktif) {
            return response()->json([
                'message' => 'Produk sedang dinonaktifkan dan belum bisa dipilih kasir.',
            ], 422);
        }

        $cart = $this->ambilKeranjang();
        $items =& $cart['items'];

        if (! isset($items[$p->id])) {
            $items[$p->id] = [
                'id' => $p->id,
                'name' => $p->nama_barang,
                'price' => (int) $p->harga,
                'price_online' => (int) $p->harga_online,
                'qty' => 1,
            ];
        } else {
            $items[$p->id]['qty'] = (int) $items[$p->id]['qty'] + 1;
        }

        return response()->json($this->simpanKeranjang($cart));
    }

    public function kurangKeranjang(Request $req)
    {
        $data = $req->validate(['produk_id' => 'required|integer']);
        $cart = $this->ambilKeranjang();
        $items =& $cart['items'];

        if (isset($items[$data['produk_id']])) {
            $items[$data['produk_id']]['qty'] = max(0, (int) $items[$data['produk_id']]['qty'] - 1);
            if ($items[$data['produk_id']]['qty'] === 0) {
                unset($items[$data['produk_id']]);
            }
        }

        return response()->json($this->simpanKeranjang($cart));
    }

    public function hapusDariKeranjang(Request $req)
    {
        $data = $req->validate(['produk_id' => 'required|integer']);
        $cart = $this->ambilKeranjang();
        unset($cart['items'][$data['produk_id']]);

        return response()->json($this->simpanKeranjang($cart));
    }

    public function kosongkanKeranjang()
    {
        Session::forget('cart');
        Session::forget('pending_customer_name');

        return response()->json($this->simpanKeranjang(['items' => []]));
    }

    public function setChannel(Request $req)
    {
        $channel = (string) $req->input('channel', 'offline');
        if (! in_array($channel, ['online', 'offline'], true)) {
            $channel = 'offline';
        }
        Session::put('pos_channel', $channel);

        return response()->json($this->simpanKeranjang($this->ambilKeranjang()));
    }

    public function prosesForm(Request $req)
    {
        try {
            $metode = (string) $req->input('metode', 'cash');
            if (! in_array($metode, ['cash', 'qris', 'transfer', 'debit', 'kartu', 'lainnya', 'tempo'], true)) {
                return $this->failResponse($req, 'Metode pencatatan tidak valid.');
            }

            $cart = $this->ambilKeranjang();
            if (empty($cart['items'])) {
                return $this->failResponse($req, 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
            }

            $rawCustomerName = Str::of((string) $req->input('customer_name', ''))
                ->squish()
                ->value();

            if ($rawCustomerName === '') {
                Session::forget('pending_customer_name');
                return $this->failResponse($req, 'Nama pelanggan wajib diisi.');
            }

            Session::put('pending_customer_name', $rawCustomerName);

            $customer = $this->findCustomerByNormalizedName($rawCustomerName);
            if (! $customer) {
                $customer = Customer::create([
                    'name' => trim($rawCustomerName),
                ]);
            } elseif ($customer->name !== $rawCustomerName) {
                $customer->name = $rawCustomerName;
                $customer->save();
            }

            $invoiceToName = $metode === 'tempo' ? trim((string) $req->input('invoice_to_name')) : null;
            if ($metode === 'tempo' && empty($invoiceToName)) {
                $invoiceToName = $customer->name;
            }
            $invoiceToCompany = $metode === 'tempo' ? trim((string) $req->input('invoice_to_company')) : null;
            $tempoDueDate = $metode === 'tempo' ? $req->input('tempo_due_date') : null;
            if ($metode === 'tempo' && empty($tempoDueDate)) {
                $tempoDueDate = now()->addDays(7)->toDateString();
            }

            $channel = Session::get('pos_channel', 'offline');
            $penjualan = DB::transaction(function () use ($req, $cart, $customer, $metode, $channel, $invoiceToName, $invoiceToCompany, $tempoDueDate) {
                $penjualan = Penjualan::create([
                    'tanggal' => now(),
                    'user_id' => Auth::id() ?: 1,
                    'customer_id' => $customer->id,
                    'metode' => $metode,
                    'channel' => $channel,
                    'total' => 0,
                    'bayar' => 0,
                    'kembalian' => 0,
                    'invoice_to_name' => $invoiceToName,
                    'invoice_to_company' => $invoiceToCompany,
                    'tempo_due_date' => $tempoDueDate,
                ]);

                $subtotal = 0;
                foreach ($cart['items'] as $row) {
                    $produk = Produk::find((int) $row['id']);
                    $harga = $produk ? (float) ($channel === 'online' ? $produk->harga_online : $produk->harga) : (float) $row['price'];
                    $line = $harga * (int) $row['qty'];
                    
                    PenjualanDetail::create([
                        'penjualan_id' => $penjualan->id,
                        'produk_id' => (int) $row['id'],
                        'harga' => $harga,
                        'qty' => (int) $row['qty'],
                        'subtotal' => $line,
                    ]);
                    $subtotal += $line;
                }

                $discountSummary = $this->customerDiscountSummary($customer, $subtotal, (int) $penjualan->id);
                $penjualan->forceFill([
                    'diskon_persen' => $discountSummary['discount_percent'],
                ])->save();
                $penjualan->refresh();

                $bayar = 0;
                $kembalian = 0;
                if ($metode === 'cash') {
                    $bayarRaw = (int) $req->input('bayar', 0);
                    $bayar = $bayarRaw > 0 ? $bayarRaw : (int) $penjualan->total;
                    $kembalian = max(0, $bayar - (int) $penjualan->total);
                } elseif (in_array($metode, ['qris', 'transfer', 'debit'], true)) {
                    $bayar = 0;
                    $kembalian = 0;
                } elseif ($metode !== 'tempo') {
                    $bayar = (int) $penjualan->total;
                }

                $penjualan->forceFill([
                    'bayar' => $bayar,
                    'kembalian' => $kembalian,
                ])->save();

                if ($metode === 'tempo') {
                    $namaToko = trim((string) ($invoiceToCompany ?: $invoiceToName ?: $customer->name ?: 'Tanpa Nama'));
                    \App\Models\Invoice::create([
                        'penjualan_id'          => $penjualan->id,
                        'nomor_invoice'         => $penjualan->kode_penjualan,
                        'nama_toko'             => $namaToko,
                        'tanggal_invoice'       => now()->toDateString(),
                        'tanggal_jatuh_tempo'   => $tempoDueDate,
                        'total_tagihan'         => (float) $penjualan->total,
                        'status'                => \App\Models\Invoice::STATUS_UNPAID,
                    ]);
                }

                return $penjualan->refresh();
            });

            $message = 'Penjualan berhasil dicatat.';

            if ($req->ajax() || $req->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'sales_code' => $penjualan->kode_penjualan,
                    'metode' => $penjualan->metode,
                    'paid' => true,
                    'total' => (int) $penjualan->total,
                    'diskon' => (int) $penjualan->diskon_nominal,
                    'message' => $message,
                ]);
            }

            return redirect()->route('kasir.index')
                ->with('sales_code', $penjualan->kode_penjualan)
                ->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Kasir prosesForm error', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->failResponse($req, 'Gagal mencatat penjualan: ' . $e->getMessage());
        }
    }

    public function batal(Request $req, string $kode)
    {
        try {
            $penjualan = Penjualan::where('kode_penjualan', $kode)->first();
            if (! $penjualan) {
                return response()->json(['ok' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
            }

            $penjualan->delete();
            $this->cleanupSesi($kode);

            return response()->json(['ok' => true, 'message' => 'Transaksi dibatalkan.']);
        } catch (\Throwable $e) {
            Log::warning('Kasir batal error', ['kode' => $kode, 'msg' => $e->getMessage()]);

            return response()->json(['ok' => false, 'message' => 'Gagal membatalkan.'], 500);
        }
    }

    public function konfirmasiPembayaran(Request $req, string $kode)
    {
        try {
            $penjualan = Penjualan::where('kode_penjualan', $kode)->first();
            if (! $penjualan) {
                return response()->json(['ok' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
            }

            $penjualan->forceFill([
                'bayar' => $penjualan->total,
                'kembalian' => 0,
            ])->save();

            return response()->json(['ok' => true, 'message' => 'Pembayaran berhasil dikonfirmasi.']);
        } catch (\Throwable $e) {
            Log::error('Kasir konfirmasiPembayaran error', ['kode' => $kode, 'msg' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Gagal mengonfirmasi pembayaran.'], 500);
        }
    }

    public function statusPenjualan(string $kode)
    {
        $status = $this->hitungStatus($kode);
        $pj = $this->getPenjualanByKode($kode);

        return response()->json([
            'kode_penjualan' => $kode,
            'status' => $status,
            'metode' => $pj ? $pj->metode : 'cash',
            'grand_total' => (int) ($pj?->total ?? 0),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache');
    }

    private function hitungStatus(string $kode): string
    {
        $pj = $this->getPenjualanByKode($kode);
        if (! $pj) {
            return 'pending';
        }

        if ($pj->metode === 'tempo') {
            return 'paid';
        }

        if ((int) $pj->total > 0 && (int) $pj->bayar >= (int) $pj->total) {
            return 'paid';
        }

        return 'pending';
    }

    private function strukSudahDicetak(string $kode): bool
    {
        return (bool) (Penjualan::where('kode_penjualan', $kode)->value('struk_dicetak') ?? false);
    }

    public function cetakStruk(string $kode)
    {
        $status = $this->hitungStatus($kode);
        if ($status !== 'paid') {
            abort(403, 'Penjualan belum selesai dicatat.');
        }

        $penjualan = Penjualan::with(['details.produk', 'user', 'customer'])
            ->where('kode_penjualan', $kode)
            ->firstOrFail();

        $payment = null;

        return view('kasir.struk', compact('penjualan', 'payment'));
    }

    public function printStruk(string $kode, ReceiptPrinter $receiptPrinter)
    {
        $penjualan = Penjualan::with(['details.produk', 'user', 'customer'])
            ->where('kode_penjualan', $kode)
            ->firstOrFail();

        $status = $this->hitungStatus($kode);
        if ($status !== 'paid') {
            return response()->json(['message' => 'Penjualan belum selesai dicatat.'], 422);
        }

        try {
            $receiptPrinter->print($penjualan, null);
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

        $this->cleanupSesi($kode);

        return response()->json([
            'message' => 'Struk berhasil dicetak ke printer ' . config('receipt_printer.printer_name', 'POS-Printer') . '.',
        ]);
    }

    public function selesaiCetak(string $kode)
    {
        $penjualan = Penjualan::where('kode_penjualan', $kode)->firstOrFail();

        $status = $this->hitungStatus($kode);
        if ($status !== 'paid') {
            return response()->json(['message' => 'Penjualan belum selesai dicatat.'], 422);
        }

        $penjualan->forceFill([
            'struk_dicetak' => true,
            'struk_dicetak_at' => now(),
        ])->save();

        $this->cleanupSesi($kode);

        return response()->json(['message' => 'OK']);
    }

    public function invoice(Request $req, string $kode)
    {
        $penjualan = Penjualan::with(['details.produk', 'user', 'customer'])
            ->where('kode_penjualan', $kode)
            ->firstOrFail();

        // Ensure token is generated if somehow missing
        if (empty($penjualan->invoice_token)) {
            $penjualan->invoice_token = \Illuminate\Support\Str::random(64);
            $penjualan->saveQuietly();
        }

        $status = $this->hitungStatus($kode);
        if ($penjualan->metode !== 'tempo' && $status !== 'paid') {
            abort(403, 'Halaman ini hanya untuk transaksi yang sudah dicatat.');
        }

        $payment = null;
        $printMode = (bool) $req->boolean('print', false);

        return view('kasir.invoice', compact('penjualan', 'payment', 'printMode'));
    }

    public function publicInvoice(Request $req, string $token)
    {
        $penjualan = Penjualan::with(['details.produk', 'user', 'customer'])
            ->where('invoice_token', $token)
            ->firstOrFail();

        $status = $this->hitungStatus($penjualan->kode_penjualan);
        if ($penjualan->metode !== 'tempo' && $status !== 'paid') {
            abort(403, 'Halaman ini hanya untuk transaksi yang sudah dicatat.');
        }

        $payment = null;
        $printMode = (bool) $req->boolean('print', false);

        return view('kasir.invoice', compact('penjualan', 'payment', 'printMode'));
    }

    private function failResponse(Request $req, string $message)
    {
        if ($req->ajax() || $req->wantsJson()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }

        return back()->with('error', $message);
    }
}
