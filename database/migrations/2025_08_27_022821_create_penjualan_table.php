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
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penjualan')->unique();  // nomor struk
            $table->dateTime('tanggal');                 // waktu transaksi
            $table->foreignId('user_id')                 // kasir/pegawai
                  ->constrained('users')
                  ->cascadeOnUpdate();
            $table->decimal('total', 12, 2)->default(0); // total dari detail
            $table->decimal('bayar', 12, 2)->default(0); // uang diterima
            $table->decimal('kembalian', 12, 2)->default(0);
            $table->string('metode', 20);                // cash/qris/dll
            $table->index(['tanggal', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
