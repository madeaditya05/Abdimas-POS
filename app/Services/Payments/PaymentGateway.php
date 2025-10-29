<?php

namespace App\Services\Payments;

use App\Models\Order;

class PaymentGateway
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('midtrans.is_production', false);
        \Midtrans\Config::$isSanitized  = (bool) config('midtrans.is_sanitized', true);
        \Midtrans\Config::$is3ds        = (bool) config('midtrans.is_3ds', true);
    }

    /** Utility: ubah respons Midtrans ke array (deep) */
    private function toArray(mixed $res): array
    {
        return json_decode(json_encode($res), true);
    }

    /** Buat transaksi QRIS */
    public function buatTransaksiQris(Order $order): array
    {
        // Ambil kolom lengkap supaya 'id' tidak kosong
        $items = $order->items()->get(['id','product_id','name','price','qty'])
            ->map(function ($i) {
                return [
                    // id WAJIB non-blank kalau dikirim. Pakai SKU dari product_id.
                    'id'       => 'SKU-' . (string) $i->product_id,
                    'price'    => (int) $i->price,
                    'quantity' => (int) $i->qty,
                    'name'     => $i->name,
                ];
            })->toArray();

        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id'     => $order->order_no,
                'gross_amount' => (int) $order->grand_total,
            ],
            'item_details' => $items, // aman: id tidak kosong
        ];

        $res = \Midtrans\CoreApi::charge($params);
        return $this->toArray($res);
    }

    /** Buat transaksi VA */
    public function buatTransaksiVa(Order $order, string $bank): array
    {
        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id'     => $order->order_no,
                'gross_amount' => (int) $order->grand_total,
            ],
            'bank_transfer' => ['bank' => strtolower($bank)],
            // item_details tidak wajib untuk VA
        ];

        $res = \Midtrans\CoreApi::charge($params);
        return $this->toArray($res); // FIX: pastikan array
    }

    

    // Wrapper biar kompatibel dengan kode lama (opsional)
    public function chargeQris(Order $order): array                 { return $this->buatTransaksiQris($order); }
    public function chargeVA(Order $order, string $bank): array     { return $this->buatTransaksiVa($order, $bank); }
}
