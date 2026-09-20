<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('goods_receipt_items', function (Blueprint $table) {
      if (Schema::hasColumn('goods_receipt_items', 'invoice_line_total')) {
        $table->dropColumn('invoice_line_total');
      }

      if (Schema::hasColumn('goods_receipt_items', 'invoice_unit_price')) {
        $table->dropColumn('invoice_unit_price');
      }
    });

    Schema::table('goods_receipts', function (Blueprint $table) {
      if (Schema::hasColumn('goods_receipts', 'invoice_currency')) {
        $table->dropColumn('invoice_currency');
      }

      if (Schema::hasColumn('goods_receipts', 'invoice_date')) {
        $table->dropColumn('invoice_date');
      }

      if (Schema::hasColumn('goods_receipts', 'invoice_number')) {
        $table->dropColumn('invoice_number');
      }
    });
  }

  public function down(): void
  {
    Schema::table('goods_receipts', function (Blueprint $table) {
      if (! Schema::hasColumn('goods_receipts', 'invoice_number')) {
        $table->string('invoice_number', 100)->nullable()->after('document_number');
      }

      if (! Schema::hasColumn('goods_receipts', 'invoice_date')) {
        $table->date('invoice_date')->nullable()->after('invoice_number');
      }

      if (! Schema::hasColumn('goods_receipts', 'invoice_currency')) {
        $table->string('invoice_currency', 10)->nullable()->after('invoice_date');
      }
    });

    Schema::table('goods_receipt_items', function (Blueprint $table) {
      if (! Schema::hasColumn('goods_receipt_items', 'invoice_unit_price')) {
        $table->decimal('invoice_unit_price', 18, 4)->nullable()->after('received_qty');
      }

      if (! Schema::hasColumn('goods_receipt_items', 'invoice_line_total')) {
        $table->decimal('invoice_line_total', 18, 2)->nullable()->after('invoice_unit_price');
      }
    });
  }
};
