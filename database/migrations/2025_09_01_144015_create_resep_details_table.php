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
        Schema::create('resep_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resep_id')->constrained('resep')->cascadeOnDelete();   // <-- penting!
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku');           // <-- penting!
            $table->decimal('qty_per_porsi', 14, 3);
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['resep_id', 'bahan_baku_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep_detail');
    }

};
