<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Display;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class KasirController extends Controller
{
    public function __construct(private PaymentGateway $gateway) {}

    /** Halaman Kasir: tampilkan list produk aktif */
    public function index()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id','name','price']);

        return view('kasir.index', compact('products'));
    }

    // ===================== UTIL DISPLAY (LAYAR CUSTOMER) =====================

    /**
     * Dorong order ke layar customer tertentu (default: 'utama')
     * Layar customer /pembayaran akan membaca pointer ini dan menampilkan order terkait.
     */
    private function dorongKeLayar(string $kodeLayar, string $orderNo): void
    {
        Display::updateOrCreate(['code' => $kodeLayar], ['order_no' => $orderNo]);
    }

    // ===================== KERANJANG (SESSION) =====================

    /** Ambil keranjang dari session dalam bentuk standar */
    private function ambilKeranjang(): array
    {
        $cart = Session::get('cart', ['items' => []]); // [ product_id => ['id','name','price','qty'] ]
        if (!isset($cart['items']) || !is_array($cart['items'])) $cart = ['items' => []];
        return $cart;
    }

    /** Simpan keranjang + kembalikan ringkasan (angka & teks rupiah) */
    private function simpanKeranjang(array $cart): array
    {
        $subtotal = 0; $count = 0; $items = [];
        foreach ($cart['items'] as $row) {
            $line = (int)$row['price'] * (int)$row['qty'];
            $subtotal += $line;
            $count += (int)$row['qty'];
            $items[] = [
                'product_id'      => $row['id'],
                'name'            => $row['name'],
                'price'           => (int)$row['price'],
                'qty'             => (int)$row['qty'],
                'line_total'      => $line,
                'price_text'      => $this->rupiah((int)$row['price']),
                'line_total_text' => $this->rupiah($line),
            ];
        }
        $summary = [
            'items'          => $items,
            'subtotal'       => $subtotal,
            'subtotal_text'  => $this->rupiah($subtotal),
            'count'          => $count,
        ];
        Session::put('cart', ['items' => $cart['items']]);
        return $summary;
    }

    /** Format Rupiah */
    private function rupiah(int $n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    /** GET: data keranjang (untuk render di view via fetch) */
    public function dataKeranjang()
    {
        $cart = $this->ambilKeranjang();
        return response()->json($this->simpanKeranjang($cart));
    }

    /** POST: tambah 1 qty (kalau belum ada → buat) */
    public function tambahKeKeranjang(Request $req)
    {
        $data = $req->validate([
            // SESUAIKAN dengan nama tabel kamu: 'product' (singular) atau 'products' (plural)
            'product_id' => 'required|integer|exists:product,id',
        ]);

        $p = Product::select('id','name','price')->findOrFail($data['product_id']);

        $cart = $this->ambilKeranjang();
        $items =& $cart['items'];

        if (!isset($items[$p->id])) {
            $items[$p->id] = ['id'=>$p->id, 'name'=>$p->name, 'price'=>(int)$p->price, 'qty'=>1];
        } else {
            $items[$p->id]['qty'] = (int)$items[$p->id]['qty'] + 1;
        }

        return response()->json($this->simpanKeranjang($cart));
    }

    /** POST: kurangi 1 qty (kalau qty=1 → hapus) */
    public function kurangKeranjang(Request $req)
    {
        $data = $req->validate(['product_id' => 'required|integer']);
        $cart = $this->ambilKeranjang();
        $items =& $cart['items'];

        if (isset($items[$data['product_id']])) {
            $items[$data['product_id']]['qty'] = max(0, (int)$items[$data['product_id']]['qty'] - 1);
            if ($items[$data['product_id']]['qty'] === 0) unset($items[$data['product_id']]);
        }
        return response()->json($this->simpanKeranjang($cart));
    }

    /** DELETE: hapus item dari keranjang */
    public function hapusDariKeranjang(Request $req)
    {
        $data = $req->validate(['product_id' => 'required|integer']);
        $cart = $this->ambilKeranjang();
        unset($cart['items'][$data['product_id']]);
        return response()->json($this->simpanKeranjang($cart));
    }

    /** POST: kosongkan keranjang */
    public function kosongkanKeranjang()
    {
        Session::forget('cart');
        return response()->json($this->simpanKeranjang(['items'=>[]]));
    }

    // ===================== PROSES PEMBAYARAN =====================

    /**
     * Buat order dari keranjang session, inisiasi pembayaran (QRIS/VA/Snap),
     * dorong ke layar customer 'utama', kosongkan keranjang, dan kembali ke /kasir
     * sambil bawa flash order untuk indikator.
     */
    public function prosesForm(Request $req)
{
    try {
        $metode = (string) $req->input('metode');
        if (!in_array($metode, ['qris','va_bca','va_bri','va_bni'], true)) {
            return back()->with('error', 'Metode pembayaran tidak valid.');
        }

        $cart = $this->ambilKeranjang();
        if (empty($cart['items'])) {
            return back()->with('error', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
        }

        // Buat order + items
        $order = new Order();
        $order->order_no = $this->buatNomorOrder();
        $order->status   = 'pending';
        $order->discount = 0; $order->tax = 0;
        $order->subtotal = 0; $order->grand_total = 0;
        $order->save();

        $subtotal = 0;
        foreach ($cart['items'] as $row) {
            $line = (int)$row['price'] * (int)$row['qty'];
            $order->items()->create([
                'product_id'=>$row['id'],
                'name'=>$row['name'],
                'price'=>(int)$row['price'],
                'qty'=>(int)$row['qty'],
                'line_total'=>$line,
            ]);
            $subtotal += $line;
        }
        $order->update(['subtotal'=>$subtotal,'grand_total'=>$subtotal]);

        // Inisiasi pembayaran + Payment
        switch ($metode) {
            case 'qris': {
                $res = $this->gateway->buatTransaksiQris($order);
                PaymentLog::create([
                    'order_id'=>$order->id,'event'=>'charge_qris','payload'=>json_encode($res)
                ]);
                $qrUrl    = collect($res['actions'] ?? [])->firstWhere('name','generate-qr-code')['url'] ?? null;
                $qrString = $res['qr_string'] ?? null;

                Payment::create([
                    'order_id'=>$order->id,'pg'=>'midtrans',
                    'pg_transaction_id'=>$res['transaction_id'] ?? null,
                    'pg_payment_type'=>'qris',
                    'gross_amount'=>$order->grand_total,
                    'transaction_status'=>$res['transaction_status'] ?? 'pending',
                    'meta'=>['qr_url'=>$qrUrl,'qr_string'=>$qrString],
                ]);
                break;
            }
            case 'va_bca':
            case 'va_bri':
            case 'va_bni': {
                $bank = substr($metode, 3);
                $res  = $this->gateway->buatTransaksiVa($order, $bank);
                PaymentLog::create([
                    'order_id'=>$order->id,'event'=>'charge_va_'.$bank,'payload'=>json_encode($res)
                ]);
                $vaNumber = $res['va_numbers'][0]['va_number'] ?? null;

                Payment::create([
                    'order_id'=>$order->id,'pg'=>'midtrans',
                    'pg_transaction_id'=>$res['transaction_id'] ?? null,
                    'pg_payment_type'=>'va_'.$bank,
                    'gross_amount'=>$order->grand_total,
                    'transaction_status'=>$res['transaction_status'] ?? 'pending',
                    'meta'=>['va_number'=>$vaNumber],
                ]);
                break;
            }
        }

        // dorong ke layar customer & kembali ke /kasir (bawa flash untuk indikator)
        $this->dorongKeLayar('utama', $order->order_no);

        return redirect()->route('kasir.index')
            ->with('order_id', $order->id)
            ->with('order_no', $order->order_no)
            ->with('success', 'Transaksi dibuat. Layar customer otomatis menampilkan order.');

    } catch (\Throwable $e) {
        Log::error('Kasir prosesForm error', ['msg'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()]);
        return back()->with('error', 'Gagal memproses pembayaran: '.$e->getMessage());
    }
}


    /** Nomor order unik harian */
    private function buatNomorOrder(): string
    {
        $seq = str_pad((string) ((Order::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);
        return 'ORD-'.now()->format('ymd').'-'.$seq;
    }

    public function show(Order $order)
{
    $order->load('items'); // pastikan relasi items ada
    return response()->json([
        'order_id'    => $order->id,
        'order_no'    => $order->order_no,
        'status'      => $order->status,          // 'pending' | 'paid' | 'expired' | 'cancelled'
        'grand_total' => (int) $order->grand_total,
        'items'       => $order->items->map(fn($i) => [
            'name'       => $i->name,
            'qty'        => (int) $i->qty,
            'line_total' => (int) $i->line_total,
        ]),
    ]);
}
}
