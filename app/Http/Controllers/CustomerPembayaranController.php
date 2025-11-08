<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Display;
use Illuminate\Http\Request;

class CustomerPembayaranController extends Controller
{
    /** Halaman layar customer berdasarkan NOMOR ORDER (ORD-...) */
    public function halaman(string $orderNo)
    {
        $order = Order::with('items')->where('order_no', $orderNo)->firstOrFail();
        return view('pembayaran.show', compact('order'));
    }

    /** View kosong: JS akan polling pointer layar */
    public function layar(Request $req)
    {
        $kode = $req->query('layar', 'utama'); // ?layar=utama
        return view('pembayaran.show', compact('kode'));
    }

    /** Data publik untuk polling langsung dengan NOMOR ORDER (ORD-...) */
    public function dataPublik(string $orderNo)
    {
        $order   = Order::with('items')->where('order_no', $orderNo)->firstOrFail();
        $payment = Payment::where('order_id', $order->id)->latest('id')->first();

        // Normalisasi meta (cast array atau decode manual)
        $meta = is_array($payment?->meta)
            ? $payment->meta
            : (json_decode($payment?->meta ?? '[]', true) ?: []);

        // "va_bca" -> "bca", "qris" tetap "qris"
        $bankType = $payment?->pg_payment_type;
        $bank = ($bankType && str_starts_with($bankType, 'va_')) ? substr($bankType, 3) : $bankType;

        return response()->json([
            'order_no'    => $order->order_no,
            'status'      => $order->status,                     // pending|paid|expired|cancelled
            'grand_total' => (int) $order->grand_total,
            'items'       => $order->items->map(fn($i) => [
                'name'       => $i->name,
                'qty'        => (int) $i->qty,
                'line_total' => (int) $i->line_total,
            ]),
            'qris' => [
                'qr_url'    => $meta['qr_url']    ?? null,
                'qr_string' => $meta['qr_string'] ?? null,
            ],
            'va' => [
                'bank'      => $bank,
                'va_number' => $meta['va_number'] ?? null,
            ],
        ]);
    }

    /** Data gabungan untuk layar publik:
     *  pointer (display.code) -> order_no (ORD-...) -> order + payment
     */
    public function dataDisplay(string $code = 'utama')
    {
        $orderNo = Display::where('code', $code)->value('order_no'); // berisi NOMOR ORDER sekarang
        if (!$orderNo) {
            return response()->json([
                'status' => 'idle',
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

        $payment = Payment::where('order_id', $order->id)->latest('id')->first();
        $meta = is_array($payment?->meta)
            ? $payment->meta
            : (json_decode($payment?->meta ?? '[]', true) ?: []);

        $bankType = $payment?->pg_payment_type;
        $bank = ($bankType && str_starts_with($bankType, 'va_')) ? substr($bankType, 3) : $bankType;

        return response()->json([
            'status'      => $order->status,
            'order_no'    => $order->order_no,
            'grand_total' => (int) $order->grand_total,
            'items'       => $order->items->map(fn($i)=>[
                'name'=>$i->name, 'qty'=>(int)$i->qty, 'line_total'=>(int)$i->line_total
            ]),
            'qris' => [
                'qr_url'    => $meta['qr_url']    ?? null,
                'qr_string' => $meta['qr_string'] ?? null,
            ],
            'va'   => [
                'bank'      => $bank,
                'va_number' => $meta['va_number'] ?? null,
            ],
        ]);
    }
}
