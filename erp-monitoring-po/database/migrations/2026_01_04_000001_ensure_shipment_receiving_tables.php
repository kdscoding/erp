<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->string('shipment_number')->unique();
                $table->date('shipment_date')->nullable();
                $table->date('eta_date')->nullable();
                $table->string('delivery_note_number')->nullable();
                $table->text('supplier_remark')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('goods_receipts')) {
            Schema::create('goods_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('gr_number')->unique();
                $table->date('receipt_date');
                $table->foreignId('purchase_order_id')->constrained('purchase_orders');
                $table->string('document_number')->nullable();
                $table->text('remark')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('goods_receipt_items')) {
            Schema::create('goods_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
                $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items');
                $table->decimal('received_qty', 14, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // no-op
    }
};
