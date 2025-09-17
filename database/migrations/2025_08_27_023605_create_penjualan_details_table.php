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
        Schema::create('penjualan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')
                  ->constrained('penjualan')
                  ->cascadeOnDelete();
            $table->foreignId('produk_id')
                  ->constrained('produk')     // tabel produk kamu bernama 'produk'
                  ->restrictOnDelete();
            $table->decimal('harga', 12, 2); // snapshot harga saat transaksi
            $table->unsignedInteger('qty');
            $table->decimal('subtotal', 12, 2); // harga * qty
            $table->index(['penjualan_id', 'produk_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_detail');
    }
};
