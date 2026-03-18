<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('unit_code')->unique();
                $table->string('unit_name');
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
                $table->foreignId('unit_id')->nullable()->constrained('units');
                $table->string('category')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // no-op
    }
};
