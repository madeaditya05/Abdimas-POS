<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            if (! Schema::hasColumn('penjualan', 'struk_dicetak')) {
                $after = Schema::hasColumn('penjualan', 'tempo_due_date') ? 'tempo_due_date' : 'metode';
                $table->boolean('struk_dicetak')->default(false)->after($after);
            }

            if (! Schema::hasColumn('penjualan', 'struk_dicetak_at')) {
                $table->timestamp('struk_dicetak_at')->nullable()->after('struk_dicetak');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            if (Schema::hasColumn('penjualan', 'struk_dicetak_at')) {
                $table->dropColumn('struk_dicetak_at');
            }

            if (Schema::hasColumn('penjualan', 'struk_dicetak')) {
                $table->dropColumn('struk_dicetak');
            }
        });
    }
};
