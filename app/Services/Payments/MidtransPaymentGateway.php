<?php

namespace App\Services\Payments;

use App\Models\Order;
use Midtrans\Transaction;
use Midtrans\CoreApi;
use Midtrans\Snap; 

class MidtransPaymentGateway implements PaymentGateway
{
    public function chargeQris(Order $order): array { /* (biarin, gak dipakai kalau Snap saja) */ return []; }
    public function chargeEwallet(Order $o, string $e): array { return []; }
    public function chargeVA(Order $o, string $b): array { return []; }
    public function chargeCard(Order $o, array $c): array { return []; }
}
