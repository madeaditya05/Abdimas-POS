<?php

namespace App\Observers;

use App\Models\PenjualanDetail;

class PenjualanDetailObserver
{
    public function created(PenjualanDetail $detail): void
    {
        // Soft detach BOM: penjualan tidak lagi memicu pemakaian bahan berbasis resep.
    }
}
