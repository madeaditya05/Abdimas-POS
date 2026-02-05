<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->id(); // bigint unsigned AI

            $table->string('name', 255)->nullable();

            // GENERATED ALWAYS AS (lcase(trim(name))) STORED
            $table->string('normalized_name', 191)
                ->storedAs('lcase(trim(name))');

            // created_at & updated_at timestamp NULL
            $table->timestamps();

            $table->index('normalized_name', 'customer_normalized_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
