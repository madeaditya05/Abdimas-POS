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
        Schema::create('stok_mutasi', function (Blueprint $table) {
        $table->id();
        $table->foreignId('bahan_baku_id')->constrained('bahan_baku'); // relasi ke tabel bahan
        $table->enum('tipe', ['IN', 'OUT', 'ADJ']); // jenis mutasi
        $table->decimal('qty', 18, 4);              // jumlah
        $table->timestamp('tanggal')->nullable();   // tanggal mutasi
        $table->string('sumber_type');              // model sumber (misal DetailPenjualan)
        $table->unsignedBigInteger('sumber_id');    // id sumber
        $table->string('note')->nullable();         // catatan tambahan
        $table->timestamps();

        $table->index(['sumber_type','sumber_id']);
        $table->index(['bahan_baku_id','tanggal']);        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_mutasi');
    }
};
