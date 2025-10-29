<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $req)
    {
        $payload = $req->all();

        // simpan log mentah
        PaymentLog::create(['event'=>'notification','payload'=>json_encode($payload)]);

        $orderNo = $payload['order_id'] ?? null;
        $status  = $payload['transaction_status'] ?? null;
        $fraud   = $payload['fraud_status'] ?? null;
        $gross   = (int) ($payload['gross_amount'] ?? 0);

        // Optional: verify signature_key (disarankan)
        // $sig = $payload['signature_key'] ?? '';
        // $calc = hash('sha512', $orderNo.$payload['status_code'].$payload['gross_amount'].config('midtrans.server_key'));
        // if (!hash_equals($calc, $sig)) { abort(403, 'Invalid signature'); }

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

        return response('OK', 200);
    }
}
