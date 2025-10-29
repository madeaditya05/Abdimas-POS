<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\Display;  

class CustomerPembayaranController extends Controller
{
    /** Halaman layar customer: menampilkan ringkasan + QR jika ada */
    public function halaman(string $orderNo)
    {
        $order = Order::with('items')->where('order_no', $orderNo)->firstOrFail();
        return view('pembayaran.show', compact('order'));
    }

    /** Data publik untuk polling layar customer */
    public function dataPublik(string $orderNo)
{
    $order = Order::with('items')->where('order_no', $orderNo)->firstOrFail();
    $pembayaranTerakhir = Payment::where('order_id', $order->id)->latest()->first();

    return response()->json([
        'order_no'    => $order->order_no,
        'status'      => $order->status,
        'grand_total' => (int) $order->grand_total,
        'items'       => $order->items->map(fn($i) => [
            'name'       => $i->name,
            'qty'        => (int) $i->qty,
            'line_total' => (int) $i->line_total,
        ]),
        'qris' => [
            'qr_url'    => data_get($pembayaranTerakhir, 'meta.qr_url'),
            'qr_string' => data_get($pembayaranTerakhir, 'meta.qr_string'),
        ],
        'va' => [
            'bank'      => $pembayaranTerakhir?->pg_payment_type,
            'va_number' => data_get($pembayaranTerakhir, 'meta.va_number'),
        ],
    ]);
}

public function layar(Request $req)
    {
        $kode = $req->query('layar', 'utama'); // ?layar=utama (default)
        // view tanpa order, JS akan polling pointer layar
        return view('pembayaran.show', compact('kode'));
    }

    /** Data gabungan untuk layar: ambil pointer -> ambil order -> kembalikan detail */
    public function dataDisplay(string $code = 'utama')
    {
        $orderNo = Display::where('code', $code)->value('order_no');

        if (!$orderNo) {
            return response()->json([
                'status' => 'idle',               // belum ada order
                'order_no' => null,
                'grand_total' => 0,
                'items' => [],
                'qris' => ['qr_url'=>null,'qr_string'=>null],
                'va'   => ['bank'=>null,'va_number'=>null],
            ]);
        }

        $order = Order::with('items')->where('order_no', $orderNo)->first();
        if (!$order) {
            return response()->json([
                'status' => 'idle',
                'order_no' => $orderNo,
                'grand_total' => 0,
                'items' => [],
                'qris' => ['qr_url'=>null,'qr_string'=>null],
                'va'   => ['bank'=>null,'va_number'=>null],
            ]);
        }

        $payment = Payment::where('order_id',$order->id)->latest()->first();

        return response()->json([
            'status'      => $order->status,
            'order_no'    => $order->order_no,
            'grand_total' => (int) $order->grand_total,
            'items'       => $order->items->map(fn($i)=>[
                'name'=>$i->name, 'qty'=>(int)$i->qty, 'line_total'=>(int)$i->line_total
            ]),
            'qris' => [
                'qr_url'    => data_get($payment, 'meta.qr_url'),
                'qr_string' => data_get($payment, 'meta.qr_string'),
            ],
            'va'   => [
                'bank'      => $payment?->pg_payment_type,
                'va_number' => data_get($payment, 'meta.va_number'),
            ],
        ]);
    }

}
