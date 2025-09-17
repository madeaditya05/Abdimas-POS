<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Penjualan;
use App\Models\PembelianBahan;
use Illuminate\Support\Facades\DB;

class JournalPoster
{
    /** Hapus jurnal untuk sumber tertentu */
    public function deleteFor(string $sourceType, int $sourceId): void
    {
        JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->each(function (JournalEntry $e) {
                $e->lines()->delete();
                $e->delete();
            });
    }

    /** Post jurnal untuk PENJUALAN */
    public function postForPenjualan(Penjualan $sale): void
    {
        $amount = (float) ($sale->total ?? 0);
        if ($amount <= 0) {
            $this->deleteFor(Penjualan::class, $sale->id);
            return;
        }

        // Tentukan akun kas/bank/piutang berdasarkan metode
        $metode = strtolower((string) $sale->metode);
        if (in_array($metode, ['cash', 'tunai'])) {
            $debitAccountCode = config('account_map.kas');
        } elseif (in_array($metode, ['qris', 'transfer', 'debit', 'kartu'])) {
            $debitAccountCode = config('account_map.bank');
        } else {
            // anggap sebagai kredit (piutang) jika tak dikenal
            $debitAccountCode = config('account_map.piutang_usaha');
        }

        $lines = [
            // Debit: kas/bank/piutang
            [
                'account_code' => $debitAccountCode,
                'debit'  => $amount,
                'credit' => 0,
                'memo'   => 'Penjualan '.$sale->kode_penjualan,
            ],
            // Kredit: pendapatan penjualan
            [
                'account_code' => config('account_map.pendapatan_penjualan'),
                'debit'  => 0,
                'credit' => $amount,
                'memo'   => 'Penjualan '.$sale->kode_penjualan,
            ],
        ];

        $this->upsertEntry(
            sourceType: Penjualan::class,
            sourceId:   $sale->id,
            date:       $sale->tanggal?->toDateString() ?? now()->toDateString(),
            ref:        $sale->kode_penjualan,
            memo:       'Auto-post dari Penjualan',
            lines:      $lines,
        );
    }

    /** Post jurnal untuk PEMBELIAN BAHAN (tanpa hutang kalau default = false) */
    public function postForPembelianBahan(PembelianBahan $buy): void
    {
        $amount = (float) ($buy->total ?? 0);
        if ($amount <= 0) {
            $this->deleteFor(PembelianBahan::class, $buy->id);
            return;
        }

        $keUtang = (bool) config('account_map.pembelian_ke_hutang_default');

        $creditAccount = $keUtang
            ? config('account_map.utang_usaha')
            : config('account_map.kas'); // asumsi bayar cash kalau bukan ke utang

        $lines = [
            // Debit: persediaan bahan
            [
                'account_code' => config('account_map.persediaan_bahan'),
                'debit'  => $amount,
                'credit' => 0,
                'memo'   => 'Pembelian '.$buy->kode_pembelian,
            ],
            // Kredit: kas / utang
            [
                'account_code' => $creditAccount,
                'debit'  => 0,
                'credit' => $amount,
                'memo'   => 'Pembelian '.$buy->kode_pembelian,
            ],
        ];

        $this->upsertEntry(
            sourceType: PembelianBahan::class,
            sourceId:   $buy->id,
            date:       $buy->tanggal?->toDateString() ?? now()->toDateString(),
            ref:        $buy->kode_pembelian,
            memo:       'Auto-post dari Pembelian Bahan',
            lines:      $lines,
        );
    }

    /** Helper: buat/replace 1 JournalEntry + lines */
    private function upsertEntry(string $sourceType, int $sourceId, string $date, ?string $ref, ?string $memo, array $lines): void
    {
        DB::transaction(function () use ($sourceType, $sourceId, $date, $ref, $memo, $lines) {
            $entry = JournalEntry::firstOrNew([
                'source_type' => $sourceType,
                'source_id'   => $sourceId,
            ]);

            $entry->date  = $date;
            $entry->memo  = $memo;
            $entry->ref_no ??= $ref; // biar generator di model jalan saat create
            $entry->save();          // kalau baru → generator entry_no/ref_no akan mengisi

            // reset lines
            $entry->lines()->delete();

            $payload = [];
            foreach ($lines as $i => $l) {
                $payload[] = [
                    'account_id' => $this->accountIdByCode($l['account_code']),
                    'debit'      => (float) $l['debit'],
                    'credit'     => (float) $l['credit'],
                    'memo'       => $l['memo'] ?? null,
                    'line_no'    => $i + 1,
                ];
            }

            if (! empty($payload)) {
                $entry->lines()->createMany($payload);
            }
        });
    }

    /** Helper: ambil ID akun dari kode COA */
    private function accountIdByCode(string $code): int
    {
        $id = ChartOfAccount::query()->where('code', $code)->value('id');
        if (! $id) {
            throw new \RuntimeException("Kode COA {$code} tidak ditemukan. Cek config/account_map.php & master COA.");
        }
        return (int) $id;
    }
}
