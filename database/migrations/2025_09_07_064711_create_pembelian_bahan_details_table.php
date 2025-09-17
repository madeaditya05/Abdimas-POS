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
        Schema::create('pembelian_bahan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_bahan_id')->constrained('pembelian_bahan')->cascadeOnDelete();

            // Relasi ke master (tabel kamu: 'bahan_baku' singular). FK opsional → aman dulu pakai index.
            $table->unsignedBigInteger('bahan_baku_id')->index();

            // Snapshot dari master Bahan Baku (biar konsisten walau master diubah)
            $table->string('nama_bahan');                  // copy dari bahan_baku.nama_bahan
            $table->string('satuan_beli')->nullable();     // copy dari bahan_baku.satuan_beli
            $table->decimal('isi_per_kemasan', 14, 3)->nullable(); // copy dari bahan_baku.isi_per_kemasan
            $table->decimal('konversi_ke_pakai', 14, 3)->default(1); // copy dari bahan_baku.konversi_beli_ke_pakai
            $table->date('expired_date')->nullable();      // diisi jika perishable

            // Nilai & qty transaksi
            $table->decimal('qty_beli', 18, 4)->default(0);      // dalam satuan_beli
            $table->decimal('harga_satuan', 18, 2)->default(0);  // per satuan_beli
            $table->decimal('subtotal', 18, 2)->default(0);      // qty * harga

            $table->string('catatan')->nullable();

            $table->timestamps();

            $table->index(['pembelian_bahan_id']);        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_bahan_detail');
    }
};
