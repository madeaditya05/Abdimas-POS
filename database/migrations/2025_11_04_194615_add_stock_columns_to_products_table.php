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
        Schema::table('product', function (Blueprint $table) {
            $table->unsignedInteger('stock')->default(0)->after('price');
            $table->unsignedInteger('min_stock')->default(0)->after('stock');
            $table->string('unit', 32)->nullable()->after('min_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn(['stock','min_stock','unit']);
        });
    }
};
