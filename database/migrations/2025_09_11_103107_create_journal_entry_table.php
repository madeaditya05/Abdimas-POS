<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // snake_case, tanpa "s"
        Schema::create('journal_entry', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no', 30)->unique();    // JU-YYYYMM-XXXX
            $table->date('date');
            $table->string('ref_no', 50)->nullable();
            $table->string('memo', 255)->nullable();
            $table->string('source_type', 120)->nullable(); // App\Models\Penjualan, dll
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->index(['date']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry');
    }
};
