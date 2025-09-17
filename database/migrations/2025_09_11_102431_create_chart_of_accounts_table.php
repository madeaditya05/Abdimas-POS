<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chart_of_account', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();   // contoh: 1001, 4001
            $table->string('name', 100);            // contoh: Kas, Penjualan
            $table->string('type', 30);             // asset | liability | equity | revenue | expense
            $table->string('normal_side', 6);       // debit | credit
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'normal_side']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_account');
    }
};
