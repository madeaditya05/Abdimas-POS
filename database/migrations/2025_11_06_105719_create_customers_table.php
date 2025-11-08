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
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            // kolom generated untuk dedup nama (lower+trim); bisa diindex unik kalau mau
            $table->string('normalized_name', 191)->storedAs('LOWER(TRIM(name))')->nullable();
            $table->timestamps();
            $table->index('normalized_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
