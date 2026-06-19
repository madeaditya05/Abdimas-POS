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
        Schema::table('penjualan', function (Blueprint $table) {
            $table->string('invoice_token', 64)->nullable()->unique()->after('kode_penjualan');
        });

        // Backfill existing records with a random 64-character token
        \Illuminate\Support\Facades\DB::table('penjualan')
            ->whereNull('invoice_token')
            ->orderBy('id')
            ->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    \Illuminate\Support\Facades\DB::table('penjualan')
                        ->where('id', $row->id)
                        ->update(['invoice_token' => \Illuminate\Support\Str::random(64)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn('invoice_token');
        });
    }
};
