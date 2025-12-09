<?php

namespace App\Services\Payments;

class PaymentGateway
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('midtrans.is_production', false);
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;
    }

    /** Charge QRIS pakai kode penjualan & total */
    public function chargeQris(string $kodePenjualan, int $total, array $itemDetails = []): array
    {
        // itemDetails format: [['id'=>'SKU-1','price'=>1000,'quantity'=>1,'name'=>'Latte'], ...]
        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id'     => $kodePenjualan,
                'gross_amount' => $total,
            ],
            // item_details opsional tapi baiknya diisi
            'item_details' => array_values($itemDetails),
        ];

        $res = \Midtrans\CoreApi::charge($params);
        return json_decode(json_encode($res), true);
    }

    /** Charge VA (BCA/BRI/BNI) */
    public function chargeVa(string $kodePenjualan, int $total, string $bank): array
    {
        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id'     => $kodePenjualan,
                'gross_amount' => $total,
            ],
            'bank_transfer' => ['bank' => strtolower($bank)],
        ];

        $res = \Midtrans\CoreApi::charge($params);
        return json_decode(json_encode($res), true);
    }

    /** Cek status transaksi ke Midtrans */
    public function status(string $kodePenjualan): array
    {
        // Midtrans PHP SDK biasa pakainya Transaction::status($orderId)
        $res = \Midtrans\Transaction::status($kodePenjualan);

        // samain gaya return dengan chargeQris / chargeVa (array, bukan object)
        return json_decode(json_encode($res), true);
    }
}
