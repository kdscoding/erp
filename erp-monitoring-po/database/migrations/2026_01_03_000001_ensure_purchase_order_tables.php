<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique(); // manual input
                $table->date('po_date');
                $table->foreignId('supplier_id')->constrained('suppliers');
                $table->string('status')->default('Draft');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items');
                $table->decimal('ordered_qty', 14, 2)->default(0);
                $table->decimal('received_qty', 14, 2)->default(0);
                $table->decimal('outstanding_qty', 14, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // no-op
    }
};
