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
        Schema::create('pembelian_bahan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pembelian')->unique();   // CFF-YYMMDD-0001
            $table->timestamp('tanggal')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            // Supplier simple (inline, biar cepat jalan)
            $table->string('supplier_nama')->nullable();
            $table->string('supplier_kontak')->nullable();

            // Uang
            $table->decimal('total', 18, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tanggal']);       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_bahan');
    }
};
