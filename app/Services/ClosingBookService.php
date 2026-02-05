<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\ClosingPeriod;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClosingBookService
{
    public function preview(string $periodStart, string $periodEnd): array
    {
        // normalize date (YYYY-MM-DD)
        $periodStart = substr($periodStart, 0, 10);
        $periodEnd   = substr($periodEnd, 0, 10);

        $from = $periodStart . ' 00:00:00';
        $to   = $periodEnd   . ' 23:59:59';

        // purchases selama periode (periodik)
        // NOTE: ini ambil dari pembelian_bahan.total (bukan akun 5100),
        // jadi preview tetap jalan walau jurnal pembelian kamu postingnya ke 1201 (persediaan).
        $purchases = (float) (DB::table('pembelian_bahan')
            ->whereBetween('tanggal', [$from, $to])
            ->sum('total') ?? 0);

        // beginning inventory: ambil dari closing terakhir sebelum periodStart
        $prev = ClosingPeriod::query()
            ->where('is_closed', true)
            ->whereDate('period_end', '<', $periodStart)
            ->orderByDesc('period_end')
            ->first();

        $beginInv = (float) ($prev->end_inventory_value ?? 0);

        // ending inventory value (average cost) + detail per bahan
        [$endInv, $detail] = $this->computeEndingInventoryValue($to);

        $cogs = ($beginInv + $purchases - $endInv);

        // kalau mau strict, mending jangan “dipaksa 0”
        // tapi kalau kamu mau safety net, keep ini:
        if ($cogs < 0) $cogs = 0;

        return [
            'period_start' => $periodStart,
            'period_end'   => $periodEnd,
            'begin_inv'    => $beginInv,
            'purchases'    => $purchases,
            'end_inv'      => $endInv,
            'cogs'         => $cogs,
            'detail'       => $detail,
            'prev_period'  => $prev
                ? [$prev->period_start?->toDateString(), $prev->period_end?->toDateString()]
                : null,
        ];
    }

    public function isClosed(string $periodStart, string $periodEnd): ?ClosingPeriod
    {
        $periodStart = substr($periodStart, 0, 10);
        $periodEnd   = substr($periodEnd, 0, 10);

        return ClosingPeriod::query()
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->where('is_closed', true)
            ->first();
    }

    public function close(string $periodStart, string $periodEnd): ClosingPeriod
    {
        $periodStart = substr($periodStart, 0, 10);
        $periodEnd   = substr($periodEnd, 0, 10);

        return DB::transaction(function () use ($periodStart, $periodEnd) {

            // 1) GUARD: kalau sudah closed → stop (anti double posting)
            $already = $this->isClosed($periodStart, $periodEnd);
            if ($already) {
                throw new \RuntimeException("Periode {$periodStart} s/d {$periodEnd} sudah ditutup.");
            }

            // 2) kalau ada record existing tapi belum closed (misal draft), boleh lanjut update
            $existing = ClosingPeriod::query()
                ->whereDate('period_start', $periodStart)
                ->whereDate('period_end', $periodEnd)
                ->first();

            $data = $this->preview($periodStart, $periodEnd);

            // 3) buat / update record closing
            $cp = $existing ?: new ClosingPeriod();
            $cp->period_start = $periodStart;
            $cp->period_end   = $periodEnd;

            $cp->begin_inventory_value = (float) $data['begin_inv'];
            $cp->purchases_value       = (float) $data['purchases'];
            $cp->end_inventory_value   = (float) $data['end_inv'];
            $cp->cogs_value            = (float) $data['cogs'];

            $cp->is_closed  = true;
            $cp->closed_at  = now();
            $cp->closed_by  = Auth::id();
            $cp->meta       = [
                'detail' => $data['detail'],
                'note'   => 'Closing periodik: BI + Purchases - EI (average cost)',
                'prev_period' => $data['prev_period'],
            ];

            $cp->save();

            // 4) bikin jurnal closing (source_id pakai cp->id biar traceable)
            $entryId = $this->postClosingJournal(
                closingPeriodId: (int) $cp->id,
                periodStart: $periodStart,
                periodEnd: $periodEnd,
                beginInv: (float) $data['begin_inv'],
                purchases: (float) $data['purchases'],
                endInv: (float) $data['end_inv']
            );

            $cp->journal_entry_id = $entryId;
            $cp->save();

            return $cp;
        });
    }

    private function postClosingJournal(
        int $closingPeriodId,
        string $periodStart,
        string $periodEnd,
        float $beginInv,
        float $purchases,
        float $endInv
    ): int {
        $date = $periodEnd; // jurnal closing pakai tanggal akhir periode

        $hppCode        = config('account_map.hpp_bahan');        // 5001
        $pembelianCode  = config('account_map.pembelian_bahan');  // 5100
        $persediaanCode = config('account_map.persediaan_bahan'); // 1201

        $hppId        = $this->accountIdByCode($hppCode);
        $pembelianId  = $this->accountIdByCode($pembelianCode);
        $persediaanId = $this->accountIdByCode($persediaanCode);

        // create entry
        $entry = new JournalEntry();
        $entry->date = $date;
        $entry->memo = "Tutup Buku Periodik {$periodStart} s/d {$periodEnd}";
        $entry->ref_no = "CLOSE-{$periodEnd}";
        $entry->source_type = ClosingPeriod::class;
        $entry->source_id = $closingPeriodId;
        $entry->save();

        // reset lines just in case
        $entry->lines()->delete();

        $lines = [];
        $lineNo = 1;

        // 1) Dr HPP / Cr Pembelian sebesar purchases
        if ($purchases > 0) {
            $lines[] = [
                'journal_entry_id' => $entry->id,
                'account_id'       => $hppId,
                'debit'            => $purchases,
                'credit'           => 0,
                'memo'             => "Closing: pindahkan Pembelian ke HPP",
                'line_no'          => $lineNo++,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
            $lines[] = [
                'journal_entry_id' => $entry->id,
                'account_id'       => $pembelianId,
                'debit'            => 0,
                'credit'           => $purchases,
                'memo'             => "Closing: tutup akun Pembelian",
                'line_no'          => $lineNo++,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        // 2) Dr HPP / Cr Persediaan sebesar beginning inventory
        if ($beginInv > 0) {
            $lines[] = [
                'journal_entry_id' => $entry->id,
                'account_id'       => $hppId,
                'debit'            => $beginInv,
                'credit'           => 0,
                'memo'             => "Closing: akui Persediaan Awal ke HPP",
                'line_no'          => $lineNo++,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
            $lines[] = [
                'journal_entry_id' => $entry->id,
                'account_id'       => $persediaanId,
                'debit'            => 0,
                'credit'           => $beginInv,
                'memo'             => "Closing: keluarkan Persediaan Awal",
                'line_no'          => $lineNo++,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        // 3) Dr Persediaan / Cr HPP sebesar ending inventory
        if ($endInv > 0) {
            $lines[] = [
                'journal_entry_id' => $entry->id,
                'account_id'       => $persediaanId,
                'debit'            => $endInv,
                'credit'           => 0,
                'memo'             => "Closing: akui Persediaan Akhir",
                'line_no'          => $lineNo++,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
            $lines[] = [
                'journal_entry_id' => $entry->id,
                'account_id'       => $hppId,
                'debit'            => 0,
                'credit'           => $endInv,
                'memo'             => "Closing: kurangi HPP dengan Persediaan Akhir",
                'line_no'          => $lineNo++,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        if (!empty($lines)) {
            DB::table('journal_line')->insert($lines);
        }

        return (int) $entry->id;
    }

    private function computeEndingInventoryValue(string $to): array
    {
        // qty on hand per bahan
        $qtyRows = DB::table('stok_mutasi')
            ->where('tanggal', '<=', $to)
            ->selectRaw("
                bahan_baku_id,
                SUM(CASE WHEN tipe='IN'  THEN qty ELSE 0 END) as qty_in,
                SUM(CASE WHEN tipe='OUT' THEN qty ELSE 0 END) as qty_out
            ")
            ->groupBy('bahan_baku_id')
            ->get();

        if ($qtyRows->isEmpty()) {
            return [0.0, []];
        }

        // avg cost per bahan dari pembelian sampai $to
        $avgRows = DB::table('pembelian_bahan_detail as d')
            ->join('pembelian_bahan as pb', 'pb.id', '=', 'd.pembelian_bahan_id')
            ->where('pb.tanggal', '<=', $to)
            ->selectRaw('
                d.bahan_baku_id,
                SUM(d.subtotal) AS total_value,
                SUM(d.qty_beli * COALESCE(d.isi_per_kemasan, 0) * COALESCE(d.konversi_ke_pakai, 1)) AS total_qty_pakai
            ')
            ->groupBy('d.bahan_baku_id')
            ->get()
            ->keyBy('bahan_baku_id');

        $total = 0.0;
        $detail = [];

        foreach ($qtyRows as $q) {
            $bahanId = (int) $q->bahan_baku_id;
            $onHand  = (float) ($q->qty_in ?? 0) - (float) ($q->qty_out ?? 0);
            if ($onHand <= 0) continue;

            $avg = $avgRows->get($bahanId);
            if (!$avg) continue;

            $totalValue = (float) ($avg->total_value ?? 0);
            $totalQty   = (float) ($avg->total_qty_pakai ?? 0);
            if ($totalValue <= 0 || $totalQty <= 0) continue;

            $avgCost = $totalValue / $totalQty;
            $value   = $onHand * $avgCost;

            $total += $value;

            $detail[] = [
                'bahan_baku_id' => $bahanId,
                'qty_on_hand'   => $onHand,
                'avg_cost'      => $avgCost,
                'value'         => $value,
            ];
        }

        return [(float) $total, $detail];
    }

    private function accountIdByCode(string $code): int
    {
        $id = ChartOfAccount::query()->where('code', $code)->value('id');

        if (!$id) {
            throw new \RuntimeException("Kode COA {$code} tidak ditemukan. Cek config/account_map.php & master COA.");
        }

        return (int) $id;
    }
}
