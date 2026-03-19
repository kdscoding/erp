<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('supplier_confirmations');
        Schema::dropIfExists('po_approvals');
    }

    public function down(): void
    {
        if (! Schema::hasTable('po_approvals')) {
            Schema::create('po_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('approver_id')->constrained('users');
                $table->string('status');
                $table->text('note')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('supplier_confirmations')) {
            Schema::create('supplier_confirmations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->date('confirmation_date')->nullable();
                $table->date('eta_date')->nullable();
                $table->text('remark')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('shipment_items')) {
            Schema::create('shipment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
                $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items');
                $table->decimal('shipped_qty', 14, 2)->default(0);
                $table->timestamps();
            });
        }
    }
};
