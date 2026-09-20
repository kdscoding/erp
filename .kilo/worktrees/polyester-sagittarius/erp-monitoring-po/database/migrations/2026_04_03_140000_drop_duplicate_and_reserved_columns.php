<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('goods_receipt_items') && Schema::hasColumn('goods_receipt_items', 'item_id')) {
            Schema::table('goods_receipt_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('item_id');
            });
        }

        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_orders', 'approved_by')) {
                    $table->dropConstrainedForeignId('approved_by');
                }

                foreach ([
                    'sent_to_supplier_at',
                    'approved_at',
                    'bc_reference_no',
                    'bc_reference_date',
                ] as $column) {
                    if (Schema::hasColumn('purchase_orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('purchase_orders', 'sent_to_supplier_at')) {
                    $table->timestamp('sent_to_supplier_at')->nullable()->after('status_code');
                }

                if (! Schema::hasColumn('purchase_orders', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('sent_to_supplier_at')->constrained('users');
                }

                if (! Schema::hasColumn('purchase_orders', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }

                if (! Schema::hasColumn('purchase_orders', 'bc_reference_no')) {
                    $table->string('bc_reference_no')->nullable()->after('eta_date');
                }

                if (! Schema::hasColumn('purchase_orders', 'bc_reference_date')) {
                    $table->date('bc_reference_date')->nullable()->after('bc_reference_no');
                }
            });
        }

        if (Schema::hasTable('goods_receipt_items') && ! Schema::hasColumn('goods_receipt_items', 'item_id')) {
            Schema::table('goods_receipt_items', function (Blueprint $table) {
                $table->foreignId('item_id')->nullable()->after('purchase_order_item_id')->constrained('items');
            });

            DB::table('goods_receipt_items as gri')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'gri.purchase_order_item_id')
                ->update(['gri.item_id' => DB::raw('poi.item_id')]);
        }
    }
};
