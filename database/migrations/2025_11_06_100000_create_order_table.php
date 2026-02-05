<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id();

            $table->string('order_no', 255)->unique();

            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('tax')->default(0);
            $table->unsignedInteger('grand_total')->default(0);

            $table->enum('status', [
                'pending',
                'paid',
                'expired',
                'cancelled',
                'refunded'
            ])->default('pending');

            // sudah FINAL, tidak perlu alter
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->string('payment_method', 255)->nullable();

            $table->timestamps();

            $table->index('customer_id');
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customer')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};
