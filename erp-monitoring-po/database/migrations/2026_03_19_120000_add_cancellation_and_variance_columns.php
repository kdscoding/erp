<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_order_items', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('item_status');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('status');
            }
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            if (! Schema::hasColumn('goods_receipt_items', 'qty_variance')) {
                $table->decimal('qty_variance', 14, 2)->default(0)->after('received_qty');
            }

            if (! Schema::hasColumn('goods_receipt_items', 'note')) {
                $table->text('note')->nullable()->after('qty_variance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            foreach (['note', 'qty_variance'] as $column) {
                if (Schema::hasColumn('goods_receipt_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'cancel_reason')) {
                $table->dropColumn('cancel_reason');
            }
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_items', 'cancel_reason')) {
                $table->dropColumn('cancel_reason');
            }
        });
    }
};
