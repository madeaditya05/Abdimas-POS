<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Display;
use App\Models\Customer;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    public function __construct(private PaymentGateway $gateway) {}

    /** Halaman kasir */
    public function index()
    {
        // Auto-clear: kalau order terakhir sudah selesai, kosongkan keranjang & nama pending
        $lastOrderId = Session::get('last_order_id');
        if ($lastOrderId) {
            $last = Order::find($lastOrderId);
            if ($last && in_array($last->status, ['paid','expired','cancelled'])) {
                Session::forget('cart');
                Session::forget('last_order_id');
                Session::forget('pending_customer_name');
            }
        }

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id','name','price']);

        // kirim nama pelanggan yg sedang pending (untuk isi ulang input)
        $pendingName = Session::get('pending_customer_name');

        return view('kasir.index', compact('products', 'pendingName'));
    }

    /** ===== UTIL DISPLAY ===== */
    private function dorongKeLayar(string $kodeLayar, string $orderNo): void
    {
        Display::updateOrCreate(['code' => $kodeLayar], ['order_no' => $orderNo]);
    }

    /** ===== KERANJANG (SESSION) ===== */
    private function ambilKeranjang(): array
    {
        $cart = Session::get('cart', ['items' => []]);
        if (!isset($cart['items']) || !is_array($cart['items'])) $cart = ['items' => []];
        return $cart;
    }

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
        Session::put('cart', ['items' => $cart['items']]);
        return [
            'items'         => $items,
            'subtotal'      => $subtotal,
            'subtotal_text' => $this->rupiah($subtotal),
            'count'         => $count,
        ];
    }

    private function rupiah(int $n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    public function dataKeranjang()
    {
        return response()->json($this->simpanKeranjang($this->ambilKeranjang()));
    }

    public function tambahKeKeranjang(Request $req)
    {
        $data = $req->validate(['product_id' => 'required|integer|exists:product,id']);
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

    public function hapusDariKeranjang(Request $req)
    {
        $data = $req->validate(['product_id' => 'required|integer']);
        $cart = $this->ambilKeranjang();
        unset($cart['items'][$data['product_id']]);
        return response()->json($this->simpanKeranjang($cart));
    }

    public function kosongkanKeranjang()
    {
        // saat paid/expired/cancelled via polling -> kosong + hapus nama pending
        Session::forget('cart');
        Session::forget('pending_customer_name');
        return response()->json($this->simpanKeranjang(['items'=>[]]));
    }

    /** ===== PROSES PEMBAYARAN ===== */
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

            $namaPelanggan = trim((string) $req->input('customer_name', ''));

            // Order
            $order = new Order();
            $order->order_no = $this->buatNomorOrder();
            $order->status   = 'pending';
            $order->discount = 0; $order->tax = 0;
            $order->subtotal = 0; $order->grand_total = 0;

            // Link pelanggan (case-insensitive)
            if ($namaPelanggan !== '') {
                $norm = Str::of($namaPelanggan)->squish()->lower()->value();
                $customer = Customer::whereRaw('LOWER(TRIM(name)) = ?', [$norm])->first();
                if (!$customer) $customer = Customer::create(['name' => Str::of($namaPelanggan)->squish()->value()]);
                $order->customer_id = $customer->id;

                // simpan nama supaya tetap tampil di kasir saat pending
                Session::put('pending_customer_name', $customer->name);
            }

            $order->save();

            // Items
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

            // Charge Midtrans
            switch ($metode) {
                case 'qris': {
                    $res = $this->gateway->buatTransaksiQris($order);
                    PaymentLog::create(['order_id'=>$order->id,'event'=>'charge_qris','payload'=>json_encode($res)]);
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
                    PaymentLog::create(['order_id'=>$order->id,'event'=>'charge_va_'.$bank,'payload'=>json_encode($res)]);
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

            // Tampilkan di layar customer + simpan penanda order
            $this->dorongKeLayar('utama', $order->order_no);
            Session::put('last_order_id', $order->id);

            return redirect()->route('kasir.index')
                ->with('order_id', $order->id)        // untuk polling pertama
                ->with('order_no', $order->order_no)
                ->with('success', 'Transaksi dibuat. Layar customer otomatis menampilkan order.');

        } catch (\Throwable $e) {
            Log::error('Kasir prosesForm error', ['msg'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()]);
            return back()->with('error', 'Gagal memproses pembayaran: '.$e->getMessage());
        }
    }

    private function buatNomorOrder(): string
    {
        $seq = str_pad((string) ((Order::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);
        return 'ORD-'.now()->format('ymd').'-'.$seq;
    }

    /** JSON untuk polling status */
    public function show(Order $order)
    {
        return response()->json([
            'order_id'    => $order->id,
            'order_no'    => $order->order_no,
            'status'      => $order->status,   // pending | paid | expired | cancelled
            'grand_total' => (int) $order->grand_total,
        ]);
    }
}
