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
        Schema::table('pembelian_bahan', function (Blueprint $table) {
            $table->string('bukti_file')->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_bahan', function (Blueprint $table) {
            $table->dropColumn('bukti_file');
        });
    }
};
