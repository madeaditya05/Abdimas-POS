<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            // Some environments may already have these columns manually / via older SQL.
            if (!Schema::hasColumn('penjualan', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('user_id');
                $table->index('customer_id');
            }

            if (!Schema::hasColumn('penjualan', 'invoice_to_name')) {
                $table->string('invoice_to_name', 255)->nullable()->after('metode');
            }

            if (!Schema::hasColumn('penjualan', 'invoice_to_company')) {
                $table->string('invoice_to_company', 255)->nullable()->after('invoice_to_name');
            }

            if (!Schema::hasColumn('penjualan', 'tempo_due_date')) {
                $table->date('tempo_due_date')->nullable()->after('invoice_to_company');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            if (Schema::hasColumn('penjualan', 'tempo_due_date')) {
                $table->dropColumn('tempo_due_date');
            }
            if (Schema::hasColumn('penjualan', 'invoice_to_company')) {
                $table->dropColumn('invoice_to_company');
            }
            if (Schema::hasColumn('penjualan', 'invoice_to_name')) {
                $table->dropColumn('invoice_to_name');
            }
            if (Schema::hasColumn('penjualan', 'customer_id')) {
                // keep index drop safe
                try {
                    $table->dropIndex(['customer_id']);
                } catch (\Throwable $e) {
                    // ignore
                }
                $table->dropColumn('customer_id');
            }
        });
    }
};

