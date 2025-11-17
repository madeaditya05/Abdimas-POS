<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Penjualan;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $req)
    {
        $p = $req->all();
        PaymentLog::create(['event'=>'notification','payload'=>json_encode($p)]);

        $kode   = $p['order_id'] ?? null;                     // = PJL-*
        $status = $p['transaction_status'] ?? null;           // settlement|capture|pending|expire|cancel|deny
        $fraud  = $p['fraud_status'] ?? null;
        $gross  = (int)($p['gross_amount'] ?? 0);
        $ptype  = $p['payment_type'] ?? null;

        if (!$kode) return response('OK',200);

        // update payment (cari by kode_penjualan atau meta->order_no)
        $payment = Payment::where('kode_penjualan',$kode)
                    ->orWhere('meta->order_no',$kode)
                    ->latest()->first();

        if ($payment) {
            $payment->update([
                'transaction_status' => $status,
                'fraud_status'       => $fraud,
                'paid_at'            => in_array($status,['capture','settlement']) ? now() : $payment->paid_at,
                'signature_key'      => $p['signature_key'] ?? $payment->signature_key,
            ]);
        }

        // sinkronkan nilai di penjualan
        if ($pj = Penjualan::where('kode_penjualan',$kode)->first()) {
            if (in_array($status,['capture','settlement'])) {
                $pj->update([
                    'bayar'     => $gross,
                    'kembalian' => max(0, $gross - (int)$pj->total),
                    'metode'    => $payment?->pg_payment_type ?: $ptype ?: $pj->metode,
                ]);
            }
        }

        return response('OK',200);
    }
}
