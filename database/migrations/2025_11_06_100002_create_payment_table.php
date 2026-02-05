<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->id();

            // FINAL — tidak perlu alter lagi
            $table->unsignedBigInteger('penjualan_id')->nullable();
            $table->string('kode_penjualan', 40)->nullable();

            $table->string('pg', 255)->default('midtrans');
            $table->string('pg_transaction_id', 255)->nullable();
            $table->string('pg_payment_type', 255)->nullable();

            $table->decimal('gross_amount', 14, 2)->unsigned();
            $table->string('currency', 3)->default('IDR');

            $table->string('transaction_status', 255)->default('pending');
            $table->longText('meta')->nullable();
            $table->string('fraud_status', 255)->nullable();
            $table->text('signature_key')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('penjualan_id');
            $table->index('kode_penjualan');
            $table->index('pg_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
