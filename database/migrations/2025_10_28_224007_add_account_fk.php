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
        Schema::table('journal_lines', function (Blueprint $t) {
      if (!Schema::hasColumn('journal_lines','account_id')) {
        $t->unsignedBigInteger('account_id')->after('journal_entry_id');
      }
      $t->foreign('account_id')->references('id')->on('chart_of_accounts')->restrictOnDelete();
    });

    if (Schema::hasTable('payments')) {
      Schema::table('payments', function (Blueprint $t) {
        if (!Schema::hasColumn('payments','account_id')) {
          $t->unsignedBigInteger('account_id')->nullable()->after('order_id');
          $t->foreign('account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        }
      });
    }

    // opsional: pendapatan per produk
    Schema::table('products', function (Blueprint $t) {
      if (!Schema::hasColumn('products','revenue_account_id')) {
        $t->unsignedBigInteger('revenue_account_id')->nullable()->after('price');
        $t->foreign('revenue_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
      }
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_lines', function (Blueprint $t) {
      $t->dropForeign(['account_id']); $t->dropColumn('account_id');
    });
    Schema::table('payments', function (Blueprint $t) {
      if (Schema::hasColumn('payments','account_id')) { $t->dropForeign(['account_id']); $t->dropColumn('account_id'); }
    });
    Schema::table('products', function (Blueprint $t) {
      if (Schema::hasColumn('products','revenue_account_id')) { $t->dropForeign(['revenue_account_id']); $t->dropColumn('revenue_account_id'); }
    });
    }
};
