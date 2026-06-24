<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Penjualan;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class ReceiptPrinter
{
    public function print(Penjualan $penjualan, ?Payment $payment = null): void
    {
        if (! config('receipt_printer.enabled', true)) {
            return;
        }

        $profile = CapabilityProfile::load((string) config('receipt_printer.profile', 'simple'));
        $connector = new WindowsPrintConnector((string) config('receipt_printer.destination', 'POS-Printer'));
        $printer = new Printer($connector, $profile);

        try {
            $this->writeReceipt($printer, $penjualan, $payment);
        } finally {
            $printer->close();
        }
    }

    private function writeReceipt(Printer $printer, Penjualan $penjualan, ?Payment $payment): void
    {
        $brand = strtoupper((string) (config('invoice.brand_name') ?: config('app.name')));
        $date = $penjualan->tanggal
            ? $penjualan->tanggal->format('d/m/Y H:i')
            : now()->format('d/m/Y H:i');
        $kasir = $penjualan->user?->name ?? '-';
        $pelanggan = $penjualan->customer?->name ?: ($penjualan->invoice_to_name ?: '-');
        $metode = strtoupper((string) ($payment?->pg_payment_type ?? $penjualan->metode ?? '-'));

        $printer->initialize();
        $printer->feed(2); // Spasi kosong 2 baris agar nama brand atas tidak terpotong saat kertas disobek
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $this->line($printer, $brand);
        $printer->setEmphasis(false);
        $this->line($printer, 'STRUK PEMBAYARAN');

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $this->divider($printer);
        $this->pair($printer, 'No', (string) $penjualan->kode_penjualan);
        $this->pair($printer, 'Tgl', $date);
        $this->pair($printer, 'Kasir', $kasir);
        $this->pair($printer, 'Pelanggan', $pelanggan);
        $this->pair($printer, 'Metode', $metode);
        $this->divider($printer);

        foreach ($penjualan->details as $detail) {
            $name = $detail->produk?->nama_barang ?? ('Item #' . $detail->produk_id);
            foreach ($this->wrap($name) as $line) {
                $this->line($printer, $line);
            }

            $price = (int) ($detail->harga ?? 0);
            $qty = (int) ($detail->qty ?? 0);
            $this->pair($printer, $qty . ' x ' . $this->money($price), $this->money($price * $qty));
        }

        $subtotal = (int) ($penjualan->subtotal_sebelum_diskon ?? 0);
        if ($subtotal <= 0) {
            $subtotal = (int) ($penjualan->details->sum('subtotal') ?: ($penjualan->total ?? 0));
        }
        $discount = (int) ($penjualan->diskon_nominal ?? 0);
        $discountPercent = (float) ($penjualan->diskon_persen ?? 0);
        $total = (int) ($penjualan->total ?? max(0, $subtotal - $discount));
        $bayar = (int) ($penjualan->bayar ?? 0);
        $kembalian = (int) ($penjualan->kembalian ?? 0);

        $this->divider($printer);
        $this->pair($printer, 'Subtotal', $this->money($subtotal));
        if ($discount > 0) {
            $label = 'Diskon';
            if ($discountPercent > 0) {
                $label .= ' ' . rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.') . '%';
            }
            $this->pair($printer, $label, '-' . $this->money($discount));
        }
        $printer->setEmphasis(true);
        $this->pair($printer, 'Total', $this->money($total));
        $printer->setEmphasis(false);
        $this->pair($printer, 'Bayar', $this->money($bayar));
        $this->pair($printer, 'Kembalian', $this->money($kembalian));
        $this->divider($printer);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->line($printer, 'Terima kasih');
        $this->line($printer, 'atas kunjungan Anda');
        $printer->feed(1);

        if (config('receipt_printer.cash_drawer', false)) {
            $printer->pulse();
        }

        if (config('receipt_printer.cut', false)) {
            $printer->cut();
        }
    }

    private function pair(Printer $printer, string $left, string $right): void
    {
        $columns = $this->columns();
        $left = $this->clean($left);
        $right = $this->clean($right);

        $rightLength = strlen($right);
        $maxLeft = max(1, $columns - $rightLength - 1);
        if (strlen($left) > $maxLeft) {
            $left = substr($left, 0, $maxLeft);
        }

        $space = max(1, $columns - strlen($left) - $rightLength);
        $this->line($printer, $left . str_repeat(' ', $space) . $right);
    }

    private function divider(Printer $printer): void
    {
        $this->line($printer, str_repeat('-', $this->columns()));
    }

    private function line(Printer $printer, string $text = ''): void
    {
        $printer->text($this->clean($text) . "\n");
    }

    private function wrap(string $text): array
    {
        $wrapped = wordwrap($this->clean($text), $this->columns(), "\n", true);

        return array_filter(explode("\n", $wrapped), static fn ($line) => $line !== '');
    }

    private function money(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    private function columns(): int
    {
        return max(24, (int) config('receipt_printer.columns', 32));
    }

    private function clean(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }

        return trim((string) preg_replace('/[^\x20-\x7E]/', '', $text));
    }
}
