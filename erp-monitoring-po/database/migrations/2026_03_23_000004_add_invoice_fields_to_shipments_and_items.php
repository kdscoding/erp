<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('shipments', function (Blueprint $table) {
      if (! Schema::hasColumn('shipments', 'invoice_number')) {
        $table->string('invoice_number', 100)->nullable()->after('delivery_note_number');
      }

      if (! Schema::hasColumn('shipments', 'invoice_date')) {
        $table->date('invoice_date')->nullable()->after('invoice_number');
      }

      if (! Schema::hasColumn('shipments', 'invoice_currency')) {
        $table->string('invoice_currency', 10)->nullable()->after('invoice_date');
      }
    });

    Schema::table('shipment_items', function (Blueprint $table) {
      if (! Schema::hasColumn('shipment_items', 'invoice_unit_price')) {
        $table->decimal('invoice_unit_price', 18, 4)->nullable()->after('received_qty');
      }

      if (! Schema::hasColumn('shipment_items', 'invoice_line_total')) {
        $table->decimal('invoice_line_total', 18, 2)->nullable()->after('invoice_unit_price');
      }
    });
  }

  public function down(): void
  {
    Schema::table('shipment_items', function (Blueprint $table) {
      if (Schema::hasColumn('shipment_items', 'invoice_line_total')) {
        $table->dropColumn('invoice_line_total');
      }

      if (Schema::hasColumn('shipment_items', 'invoice_unit_price')) {
        $table->dropColumn('invoice_unit_price');
      }
    });

    Schema::table('shipments', function (Blueprint $table) {
      if (Schema::hasColumn('shipments', 'invoice_currency')) {
        $table->dropColumn('invoice_currency');
      }

      if (Schema::hasColumn('shipments', 'invoice_date')) {
        $table->dropColumn('invoice_date');
      }

      if (Schema::hasColumn('shipments', 'invoice_number')) {
        $table->dropColumn('invoice_number');
      }
    });
  }
};
