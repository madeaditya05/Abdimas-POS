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
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id(); 
            $table->string('kode_bahan', 16)->unique();
            $table->string('nama_bahan');
            $table->string('kategori')->nullable();
            $table->boolean('aktif')->default(true);
            $table->string('satuan_pakai');
            $table->string('satuan_beli')->nullable();
            $table->decimal('konversi_beli_ke_pakai', 14, 3)->default(1);
            $table->decimal('isi_per_kemasan', 14, 3)->nullable();
            $table->string('penyimpanan')->default('room');
            $table->boolean('is_perishable')->default(false);
            $table->integer('masa_simpan_hari')->nullable();
            $table->boolean('kelola_expired')->default(false);
            $table->string('allergen_flag')->nullable();
            $table->string('status_halal')->nullable();
            $table->string('default_supplier_nama')->nullable();
            $table->string('supplier_kontak')->nullable();
            $table->integer('lead_time_hari')->nullable();
            $table->decimal('min_order_qty', 14, 3)->nullable();
            $table->decimal('yield_persen', 5, 2)->default(100);
            $table->boolean('dipakai_di_resep')->default(true);
            $table->string('foto_path')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_baku');
    }
};
