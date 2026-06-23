<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            if (! Schema::hasColumn('produk', 'harga_online')) {
                $table->decimal('harga_online', 10, 2)->default(0.00)->after('harga');
            }
        });

        Schema::table('penjualan', function (Blueprint $table) {
            if (! Schema::hasColumn('penjualan', 'channel')) {
                $table->string('channel', 20)->default('offline')->after('metode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            if (Schema::hasColumn('produk', 'harga_online')) {
                $table->dropColumn('harga_online');
            }
        });

        Schema::table('penjualan', function (Blueprint $table) {
            if (Schema::hasColumn('penjualan', 'channel')) {
                $table->dropColumn('channel');
            }
        });
    }
};
