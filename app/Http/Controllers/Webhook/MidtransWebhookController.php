<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;

// penjualan
use App\Models\Penjualan;

use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $req)
    {
        $payload = $req->all();

        PaymentLog::create(['event'=>'notification','payload'=>json_encode($payload)]);

        $orderNo = $payload['order_id'] ?? null;             // contoh: ORD-241029-0001 (== kode_penjualan)
        $status  = $payload['transaction_status'] ?? null;   // capture|settlement|expire|cancel|deny
        $fraud   = $payload['fraud_status'] ?? null;
        $gross   = (int) ($payload['gross_amount'] ?? 0);

        $order = Order::where('order_no', $orderNo)->first();
        if (!$order) return response('OK', 200);

        // update payment row
        $payment = Payment::where('order_id',$order->id)->latest()->first();
        if ($payment) {
            $payment->update([
                'transaction_status' => $status,
                'fraud_status' => $fraud,
                'paid_at' => in_array($status, ['capture','settlement']) ? now() : $payment->paid_at,
                'signature_key' => $payload['signature_key'] ?? $payment->signature_key,
            ]);
        }

        // sinkronkan status order
        if (in_array($status, ['capture','settlement'])) {
            $order->update(['status' => 'paid']);
        } elseif ($status === 'expire') {
            $order->update(['status' => 'expired']);
        } elseif (in_array($status, ['cancel','deny'])) {
            $order->update(['status' => 'cancelled']);
        }

        // === Sinkron ke PENJUALAN ===
        if ($pj = Penjualan::where('kode_penjualan', $orderNo)->first()) {
            if (in_array($status, ['capture','settlement'])) {
                $pj->update([
                    'bayar'     => $gross,
                    'kembalian' => 0,
                    'metode'    => $payment?->pg_payment_type ?: ($payload['payment_type'] ?? $pj->metode),
                ]);
            }
        }

        return response('OK', 200);
    }
}
