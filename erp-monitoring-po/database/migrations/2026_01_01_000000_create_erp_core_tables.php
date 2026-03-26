<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->unique(['user_id', 'role_id']);
            });
        }

        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('supplier_code')->unique();
                $table->string('supplier_name');
                $table->text('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('contact_person')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('unit_code')->unique();
                $table->string('unit_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('item_categories')) {
            Schema::create('item_categories', function (Blueprint $table) {
                $table->id();
                $table->string('category_code')->unique();
                $table->string('category_name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('warehouse_code')->unique();
                $table->string('warehouse_name');
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('plants')) {
            Schema::create('plants', function (Blueprint $table) {
                $table->id();
                $table->string('plant_code')->unique();
                $table->string('plant_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->string('item_code')->unique();
                $table->string('item_name');
                $table->foreignId('category_id')->nullable()->constrained('item_categories');
                $table->text('specification')->nullable();
                $table->foreignId('unit_id')->nullable()->constrained('units');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique();
                $table->date('po_date');
                $table->foreignId('supplier_id')->constrained('suppliers');
                $table->foreignId('plant_id')->nullable()->constrained('plants');
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
                $table->string('currency', 10)->default('IDR');
                $table->text('notes')->nullable();
                $table->string('status')->default('Draft');
                $table->timestamp('sent_to_supplier_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamp('approved_at')->nullable();
                $table->date('eta_date')->nullable();
                $table->string('bc_reference_no')->nullable();
                $table->date('bc_reference_date')->nullable();
                $table->text('cancel_reason')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items');
                $table->decimal('ordered_qty', 14, 2);
                $table->decimal('received_qty', 14, 2)->default(0);
                $table->decimal('outstanding_qty', 14, 2)->default(0);
                $table->decimal('unit_price', 14, 2)->nullable();
                $table->date('etd_date')->nullable();
                $table->date('eta_date')->nullable();
                $table->string('item_status', 50)->default('Waiting');
                $table->text('cancel_reason')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
                $table->string('shipment_number')->unique();
                $table->date('shipment_date')->nullable();
                $table->date('eta_date')->nullable();
                $table->string('delivery_note_number')->nullable();
                $table->text('supplier_remark')->nullable();
                $table->string('status')->default('Shipped');
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shipment_items')) {
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

        if (!Schema::hasTable('goods_receipts')) {
            Schema::create('goods_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('gr_number')->unique();
                $table->date('receipt_date');
                $table->foreignId('purchase_order_id')->constrained('purchase_orders');
                $table->foreignId('shipment_id')->nullable()->constrained('shipments');
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
                $table->foreignId('received_by')->nullable()->constrained('users');
                $table->string('document_number')->nullable();
                $table->text('remark')->nullable();
                $table->string('status')->default('Posted');
                $table->text('cancel_reason')->nullable();
                $table->foreignId('cancelled_by')->nullable()->constrained('users');
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('goods_receipt_items')) {
            Schema::create('goods_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
                $table->foreignId('shipment_item_id')->nullable()->constrained('shipment_items');
                $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items');
                $table->foreignId('item_id')->constrained('items');
                $table->decimal('received_qty', 14, 2)->default(0);
                $table->decimal('qty_variance', 14, 2)->default(0);
                $table->decimal('accepted_qty', 14, 2)->default(0);
                $table->text('remark')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('po_status_histories')) {
            Schema::create('po_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->foreignId('changed_by')->nullable()->constrained('users');
                $table->timestamp('changed_at')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attachments')) {
            Schema::create('attachments', function (Blueprint $table) {
                $table->id();
                $table->string('module');
                $table->unsignedBigInteger('record_id');
                $table->string('file_path');
                $table->string('file_name');
                $table->foreignId('uploaded_by')->nullable()->constrained('users');
                $table->timestamps();
                $table->index(['module', 'record_id']);
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('module');
                $table->unsignedBigInteger('record_id')->nullable();
                $table->string('action');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('document_terms')) {
            Schema::create('document_terms', function (Blueprint $table) {
                $table->id();
                $table->string('group_key', 100);
                $table->string('code', 100);
                $table->string('label', 150);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['group_key', 'code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('po_status_histories');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('items');
        Schema::dropIfExists('item_categories');
        Schema::dropIfExists('plants');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('units');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('document_terms');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};
