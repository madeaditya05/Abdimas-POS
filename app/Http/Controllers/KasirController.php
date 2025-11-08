<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Display;
use App\Models\Customer;

// Modul laporan
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;

use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class KasirController extends Controller
{
    public function __construct(private PaymentGateway $gateway) {}

    /** Halaman kasir (katalog dari tabel `produk`) */
    public function index()
    {
        // Auto-clear bila order terakhir sudah selesai
        $lastOrderId = Session::get('last_order_id');
        if ($lastOrderId) {
            $last = Order::find($lastOrderId);
            if ($last && in_array($last->status, ['paid','expired','cancelled'], true)) {
                Session::forget('cart');
                Session::forget('last_order_id');
                Session::forget('pending_customer_name');
            }
        }

        // Katalog dari tabel produk
        $produks = Produk::orderBy('nama_barang')
            ->get(['id','kode_barang','nama_barang','harga','kategori','stok']);

        // daftar kategori (distinct)
        $kategoris = Produk::whereNotNull('kategori')
            ->select('kategori')->distinct()->orderBy('kategori')->pluck('kategori');

        $produks = Produk::select('id','nama_barang','harga','stok','kategori')
        ->whereRaw('COALESCE(stok,0) > 0')   // sembunyikan stok 0 / null
        ->orderBy('nama_barang')
        ->get();

        $pendingName = Session::get('pending_customer_name');
        

        return view('kasir.index', [
            'produks'     => $produks,
            'kategoris'   => $kategoris,
            'pendingName' => $pendingName,
        ]);
    }

    /** Dorong nomor order ke layar customer */
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
                'produk_id'       => $row['id'],
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
        $data = $req->validate(['produk_id' => 'required|integer|exists:produk,id']);
        $p = Produk::select('id','nama_barang','harga')->findOrFail($data['produk_id']);

        $cart = $this->ambilKeranjang();
        $items =& $cart['items'];

        if (!isset($items[$p->id])) {
            $items[$p->id] = ['id'=>$p->id, 'name'=>$p->nama_barang, 'price'=>(int)$p->harga, 'qty'=>1];
        } else {
            $items[$p->id]['qty'] = (int)$items[$p->id]['qty'] + 1;
        }

        return response()->json($this->simpanKeranjang($cart));
    }

    public function kurangKeranjang(Request $req)
    {
        $data = $req->validate(['produk_id' => 'required|integer']);
        $cart = $this->ambilKeranjang();
        $items =& $cart['items'];

        if (isset($items[$data['produk_id']])) {
            $items[$data['produk_id']]['qty'] = max(0, (int)$items[$data['produk_id']]['qty'] - 1);
            if ($items[$data['produk_id']]['qty'] === 0) unset($items[$data['produk_id']]);
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

            /** 1) Buat Order (untuk Midtrans) */
            $order = new Order();
            $order->order_no = $this->buatNomorOrder();
            $order->status   = 'pending';
            $order->discount = 0; $order->tax = 0;
            $order->subtotal = 0; $order->grand_total = 0;

            if ($namaPelanggan !== '') {
                $norm = Str::of($namaPelanggan)->squish()->lower()->value();
                $customer = Customer::whereRaw('LOWER(TRIM(name)) = ?', [$norm])->first();
                if (!$customer) $customer = Customer::create(['name' => Str::of($namaPelanggan)->squish()->value()]);
                $order->customer_id = $customer->id;
                Session::put('pending_customer_name', $customer->name);
            }
            $order->save();

            // Items → order_item (product_id dibiarkan NULL, simpan nama & harga dari tabel `produk`)
            $subtotal = 0;
            foreach ($cart['items'] as $row) {
                $line = (int)$row['price'] * (int)$row['qty'];
                $order->items()->create([
                    'product_id' => null, // kolom ini milik tabel `product`, kita kosongkan
                    'name'       => $row['name'],
                    'price'      => (int)$row['price'],
                    'qty'        => (int)$row['qty'],
                    'line_total' => $line,
                ]);
                $subtotal += $line;
            }
            $order->update(['subtotal'=>$subtotal,'grand_total'=>$subtotal]);

            /** 2) Mirror ke Penjualan + PenjualanDetail */
            $penjualan = Penjualan::updateOrCreate(
                ['kode_penjualan' => $order->order_no],
                [
                    'tanggal'   => now(),
                    'user_id'   => Auth::id() ?: 1,
                    'total'     => $subtotal,
                    'bayar'     => 0,
                    'kembalian' => 0,
                    'metode'    => $metode,
                ]
            );

            $penjualan->details()->delete();
            foreach ($cart['items'] as $row) {
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id'    => (int)$row['id'],   // FK ke tabel `produk`
                    'harga'        => (int)$row['price'],
                    'qty'          => (int)$row['qty'],
                    'subtotal'     => (int)$row['price'] * (int)$row['qty'],
                ]);
            }

            /** 3) Charge Midtrans */
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

            // Dorong ke layar + simpan pointer order
            $this->dorongKeLayar('utama', $order->order_no);
            Session::put('last_order_id', $order->id);

            return redirect()->route('kasir.index')
                ->with('order_id', $order->id)
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
            'status'      => $order->status,
            'grand_total' => (int) $order->grand_total,
        ]);
    }
}
