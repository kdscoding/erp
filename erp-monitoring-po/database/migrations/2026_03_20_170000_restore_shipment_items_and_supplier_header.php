<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('shipments', 'supplier_id')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->after('purchase_order_id')->constrained('suppliers');
            });
        }

        DB::table('shipments')
            ->whereNull('supplier_id')
            ->orderBy('id')
            ->get(['id', 'purchase_order_id'])
            ->each(function ($shipment) {
                $supplierId = DB::table('purchase_orders')
                    ->where('id', $shipment->purchase_order_id)
                    ->value('supplier_id');

                if ($supplierId) {
                    DB::table('shipments')
                        ->where('id', $shipment->id)
                        ->update(['supplier_id' => $supplierId]);
                }
            });

        if (! Schema::hasTable('shipment_items')) {
            Schema::create('shipment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
                $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items');
                $table->decimal('shipped_qty', 14, 2)->default(0);
                $table->decimal('received_qty', 14, 2)->default(0);
                $table->text('note')->nullable();
                $table->timestamps();
                $table->unique(['shipment_id', 'purchase_order_item_id']);
            });
        }

        if (! Schema::hasColumn('goods_receipt_items', 'shipment_item_id')) {
            Schema::table('goods_receipt_items', function (Blueprint $table) {
                $table->foreignId('shipment_item_id')->nullable()->after('goods_receipt_id')->constrained('shipment_items');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('goods_receipt_items', 'shipment_item_id')) {
            Schema::table('goods_receipt_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shipment_item_id');
            });
        }

        Schema::dropIfExists('shipment_items');

        if (Schema::hasColumn('shipments', 'supplier_id')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('supplier_id');
            });
        }
    }
};
