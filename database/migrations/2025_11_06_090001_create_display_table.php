<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('display', function (Blueprint $table) {
            // Di DB kamu: id bukan auto increment
            $table->unsignedBigInteger('id');
            $table->string('code', 255);
            $table->string('order_no', 255)->nullable();
            $table->timestamps();

            $table->primary('id');
            $table->unique('code', 'display_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('display');
    }
};
