<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closing_periods', function (Blueprint $table) {
            $table->id();

            // periode closing (tanggal)
            $table->date('period_start');
            $table->date('period_end');

            // status & audit
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();

            // hasil hitungan (disimpan biar bisa ditrace)
            $table->decimal('begin_inventory_value', 18, 2)->default(0);
            $table->decimal('purchases_value', 18, 2)->default(0);
            $table->decimal('end_inventory_value', 18, 2)->default(0);
            $table->decimal('cogs_value', 18, 2)->default(0);

            // link jurnal closing (optional)
            $table->unsignedBigInteger('journal_entry_id')->nullable();

            // simpan detail tambahan (qty per bahan, avg cost, dsb)
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['period_start', 'period_end'], 'closing_periods_unique_period');
            $table->index(['is_closed', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closing_periods');
    }
};
