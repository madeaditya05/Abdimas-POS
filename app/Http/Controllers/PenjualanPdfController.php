<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;

class PenjualanPdfController extends Controller
{
    public function show(Penjualan $penjualan)
    {
        // Load relasi tanpa pilih kolom spesifik (biar aman di semua skema)
        $penjualan->loadMissing(['details.produk', 'user']);

        // Pastikan total/kembalian up-to-date sebelum cetak
        $penjualan->recalcTotal();

        $pdf = Pdf::loadView('pdf.penjualan_ticket', [
                'penjualan' => $penjualan,
            ])
            // Thermal 80mm (≈ 226.77pt); tinggi dibuat panjang supaya muat
            ->setPaper([0, 0, 226.77, 1000], 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        return $pdf->stream('ticket-' . $penjualan->kode_penjualan . '.pdf');
    }
}
