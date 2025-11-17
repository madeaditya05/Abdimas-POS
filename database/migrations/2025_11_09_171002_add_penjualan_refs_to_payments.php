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
        Schema::table('payment', function (Blueprint $table) {
            $table->unsignedBigInteger('penjualan_id')->nullable()->after('id');
      $table->string('kode_penjualan', 40)->nullable()->index()->after('penjualan_id');
      // kalau perlu: $t->json('meta')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment', function (Blueprint $table) {
            $t->dropColumn(['penjualan_id','kode_penjualan']);
        });
    }
};
