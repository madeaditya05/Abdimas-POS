<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('range_start');
            $table->dateTime('range_end');
            $table->decimal('app_cash_total', 12, 2)->default(0);
            $table->decimal('physical_cash', 12, 2)->default(0);
            $table->decimal('difference', 12, 2)->default(0);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'range_start', 'range_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_reconciliations');
    }
};

