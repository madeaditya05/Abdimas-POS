<?php

namespace App\Http\Controllers;

use App\Models\Display;
use App\Models\Penjualan;
use App\Models\Payment;
use Illuminate\Http\Request;

class CustomerPembayaranController extends Controller
{
    /** Halaman layar publik: param ?layar=utama */
    public function layar(Request $req)
    {
        $kode = $req->query('layar', 'utama');
        return view('pembayaran.show', compact('kode'));
    }

    /** Data untuk layar: baca pointer -> ambil penjualan + detail + payment */
    public function dataDisplay(string $code = 'utama')
    {
        $orderNo = Display::where('code', $code)->value('order_no');

        if (!$orderNo) {
            return response()->json([
                'status'      => 'idle',
                'order_no'    => null,
                'grand_total' => 0,
                'items'       => [],
                'qris'        => ['qr_url'=>null,'qr_string'=>null],
                'va'          => ['bank'=>null,'va_number'=>null],
            ]);
        }

        $pj = Penjualan::with(['details.produk'])->where('kode_penjualan',$orderNo)->first();
        if (!$pj) {
            return response()->json([
                'status'      => 'idle',
                'order_no'    => $orderNo,
                'grand_total' => 0,
                'items'       => [],
                'qris'        => ['qr_url'=>null,'qr_string'=>null],
                'va'          => ['bank'=>null,'va_number'=>null],
            ]);
        }

        $payment = Payment::where('kode_penjualan',$orderNo)->latest()->first()
            ?: Payment::where('meta->order_no',$orderNo)->latest()->first();

        $items = $pj->details->map(function ($d) {
            $nama = $d->produk?->nama_barang ?? $d->nama_produk ?? 'Item';
            return ['name'=>$nama, 'qty'=>(int)$d->qty, 'line_total'=>(int)$d->subtotal];
        });

        $discountAmount = (int) ($pj->diskon_nominal ?? 0);
        $discountPercent = (float) ($pj->diskon_persen ?? 0);
        if ($discountAmount > 0) {
            $label = 'Diskon customer';
            if ($discountPercent > 0) {
                $label .= ' ' . rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.') . '%';
            }

            $items->push([
                'name' => $label,
                'qty' => 1,
                'line_total' => -1 * $discountAmount,
            ]);
        }

        // Tentukan status
        $status = 'pending';
        if ((int)$pj->bayar >= (int)$pj->total && (int)$pj->total > 0) {
            $status = 'paid';
        } else {
            $st = strtolower((string)($payment->transaction_status ?? 'pending'));
            $status = match ($st) {
                'settlement','capture' => 'paid',
                'expire'               => 'expired',
                'cancel','deny'        => 'cancelled',
                default                => 'pending',
            };
        }

        return response()->json([
            'status'      => $status,
            'order_no'    => $pj->kode_penjualan,
            'grand_total' => (int) $pj->total,
            'items'       => $items,
            'qris'        => [
                'qr_url'    => data_get($payment, 'meta.qr_url'),
                'qr_string' => data_get($payment, 'meta.qr_string'),
            ],
            'va'          => [
                'bank'      => $payment?->pg_payment_type,
                'va_number' => data_get($payment, 'meta.va_number'),
            ],
        ]);
    }

    /** Akses publik langsung pakai kode (opsional) */
}
