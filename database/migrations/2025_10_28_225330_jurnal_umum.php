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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');                           // tanggal jurnal
            $table->string('reference_type')->nullable();         // "Order", "Payment", dll
            $table->unsignedBigInteger('reference_id')->nullable(); // id dari reference_type
            $table->string('reference_no')->nullable();           // contoh: ORD-251024-0102
            $table->text('memo')->nullable();
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reference_type','reference_id']);
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')
                  ->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')
                  ->constrained('chart_of_accounts');            // <- relasi COA
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['journal_entry_id','account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
    }
};
