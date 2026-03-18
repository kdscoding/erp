<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                if (!Schema::hasColumn('units', 'unit_code')) $table->string('unit_code')->nullable();
                if (!Schema::hasColumn('units', 'unit_name')) $table->string('unit_name')->nullable();
            });
        }

        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (!Schema::hasColumn('suppliers', 'supplier_code')) $table->string('supplier_code')->nullable();
                if (!Schema::hasColumn('suppliers', 'supplier_name')) $table->string('supplier_name')->nullable();
                if (!Schema::hasColumn('suppliers', 'status')) $table->boolean('status')->default(true);
            });
        }

        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouses', 'warehouse_code')) $table->string('warehouse_code')->nullable();
                if (!Schema::hasColumn('warehouses', 'warehouse_name')) $table->string('warehouse_name')->nullable();
            });
        }

        if (Schema::hasTable('plants')) {
            Schema::table('plants', function (Blueprint $table) {
                if (!Schema::hasColumn('plants', 'plant_code')) $table->string('plant_code')->nullable();
                if (!Schema::hasColumn('plants', 'plant_name')) $table->string('plant_name')->nullable();
            });
        }

        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'item_code')) $table->string('item_code')->nullable();
                if (!Schema::hasColumn('items', 'item_name')) $table->string('item_name')->nullable();
                if (!Schema::hasColumn('items', 'active')) $table->boolean('active')->default(true);
            });
        }
    }

    public function down(): void
    {
        // no-op
    }
};
