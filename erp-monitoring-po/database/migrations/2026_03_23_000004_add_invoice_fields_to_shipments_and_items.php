<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('shipments', function (Blueprint $table) {
      $table->string('invoice_number', 100)->nullable()->after('delivery_note_number');
      $table->date('invoice_date')->nullable()->after('invoice_number');
      $table->string('invoice_currency', 10)->nullable()->after('invoice_date');
    });

    Schema::table('shipment_items', function (Blueprint $table) {
      $table->decimal('invoice_unit_price', 18, 4)->nullable()->after('received_qty');
      $table->decimal('invoice_line_total', 18, 2)->nullable()->after('invoice_unit_price');
    });
  }

  public function down(): void
  {
    Schema::table('shipment_items', function (Blueprint $table) {
      $table->dropColumn([
        'invoice_unit_price',
        'invoice_line_total',
      ]);
    });

    Schema::table('shipments', function (Blueprint $table) {
      $table->dropColumn([
        'invoice_number',
        'invoice_date',
        'invoice_currency',
      ]);
    });
  }
};
