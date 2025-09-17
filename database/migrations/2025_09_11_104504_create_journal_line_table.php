<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // snake_case, single (tanpa "s")
        Schema::create('journal_line', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('journal_entry_id'); // FK -> journal_entry.id
            $table->unsignedBigInteger('account_id');       // FK -> chart_of_account'.id (atau chart_of_account'jika kamu pakai single)

            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->string('memo', 255)->nullable();
            $table->unsignedInteger('line_no')->default(1);
            $table->timestamps();

            $table->index('journal_entry_id');
            $table->index('account_id');

            // FK ke journal_entry (hapus detail kalau header dihapus)
            $table->foreign('journal_entry_id')
                ->references('id')
                ->on('journal_entry')
                ->cascadeOnDelete();

            // FK ke COA — default aman: tidak cascade delete
            // NOTE: kalau COA kamu pakai single: ganti 'chart_of_account'' -> 'chart_of_account'
            $table->foreign('account_id')
                ->references('id')
                ->on('chart_of_account');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_line');
    }
};
