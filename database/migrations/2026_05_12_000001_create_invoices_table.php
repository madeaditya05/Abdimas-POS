<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('penjualan_id')->nullable()->constrained('penjualan')->nullOnDelete();
                $table->string('nomor_invoice')->unique();
                $table->string('nama_toko');
                $table->date('tanggal_invoice');
                $table->date('tanggal_jatuh_tempo');
                $table->decimal('total_tagihan', 14, 2)->default(0);
                $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
                $table->timestamps();

                $table->unique('penjualan_id');
                $table->index(['status', 'tanggal_jatuh_tempo']);
                $table->index('nama_toko');
            });
        }

        if (Schema::hasTable('penjualan')) {
            $rows = DB::table('penjualan as p')
                ->leftJoin('customer as c', 'c.id', '=', 'p.customer_id')
                ->where('p.metode', 'tempo')
                ->whereNotNull('p.tempo_due_date')
                ->select([
                    'p.id',
                    'p.kode_penjualan',
                    'p.tanggal',
                    'p.invoice_to_name',
                    'p.invoice_to_company',
                    'p.tempo_due_date',
                    'p.total',
                    'p.bayar',
                    'c.name as customer_name',
                ])
                ->get();

            $now = now();

            foreach ($rows as $row) {
                $namaToko = trim((string) ($row->invoice_to_company ?: $row->invoice_to_name ?: $row->customer_name ?: 'Tanpa Nama'));

                DB::table('invoices')->updateOrInsert(
                    ['nomor_invoice' => $row->kode_penjualan],
                    [
                        'penjualan_id'          => $row->id,
                        'nama_toko'             => $namaToko,
                        'tanggal_invoice'       => substr((string) $row->tanggal, 0, 10) ?: $now->toDateString(),
                        'tanggal_jatuh_tempo'   => $row->tempo_due_date,
                        'total_tagihan'         => (float) ($row->total ?? 0),
                        'status'                => ((float) ($row->bayar ?? 0) >= (float) ($row->total ?? 0) && (float) ($row->total ?? 0) > 0) ? 'paid' : 'unpaid',
                        'created_at'            => $now,
                        'updated_at'            => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
