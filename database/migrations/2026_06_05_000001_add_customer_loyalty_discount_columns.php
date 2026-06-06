<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            if (! Schema::hasColumn('customer', 'discount_min_transactions')) {
                $table->unsignedInteger('discount_min_transactions')->default(10)->after('normalized_name');
            }

            if (! Schema::hasColumn('customer', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('discount_min_transactions');
            }
        });

        Schema::table('penjualan', function (Blueprint $table) {
            if (! Schema::hasColumn('penjualan', 'subtotal_sebelum_diskon')) {
                $table->decimal('subtotal_sebelum_diskon', 12, 2)->default(0)->after('total');
            }

            if (! Schema::hasColumn('penjualan', 'diskon_persen')) {
                $table->decimal('diskon_persen', 5, 2)->default(0)->after('subtotal_sebelum_diskon');
            }

            if (! Schema::hasColumn('penjualan', 'diskon_nominal')) {
                $table->decimal('diskon_nominal', 12, 2)->default(0)->after('diskon_persen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            if (Schema::hasColumn('penjualan', 'diskon_nominal')) {
                $table->dropColumn('diskon_nominal');
            }

            if (Schema::hasColumn('penjualan', 'diskon_persen')) {
                $table->dropColumn('diskon_persen');
            }

            if (Schema::hasColumn('penjualan', 'subtotal_sebelum_diskon')) {
                $table->dropColumn('subtotal_sebelum_diskon');
            }
        });

        Schema::table('customer', function (Blueprint $table) {
            if (Schema::hasColumn('customer', 'discount_percent')) {
                $table->dropColumn('discount_percent');
            }

            if (Schema::hasColumn('customer', 'discount_min_transactions')) {
                $table->dropColumn('discount_min_transactions');
            }
        });
    }
};
