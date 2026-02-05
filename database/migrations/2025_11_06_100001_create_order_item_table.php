<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('name', 255);
            $table->unsignedInteger('price');
            $table->unsignedInteger('qty');
            $table->unsignedInteger('line_total');

            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');

            $table->foreign('order_id')
                  ->references('id')
                  ->on('order')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item');
    }
};
