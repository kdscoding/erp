<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_order_items', 'etd_date')) {
                $table->date('etd_date')->nullable()->after('outstanding_qty');
            }
            if (! Schema::hasColumn('purchase_order_items', 'eta_date')) {
                $table->date('eta_date')->nullable()->after('etd_date');
            }
            if (! Schema::hasColumn('purchase_order_items', 'item_status')) {
                $table->string('item_status', 50)->default('Waiting')->after('eta_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            foreach (['item_status', 'eta_date', 'etd_date'] as $column) {
                if (Schema::hasColumn('purchase_order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
