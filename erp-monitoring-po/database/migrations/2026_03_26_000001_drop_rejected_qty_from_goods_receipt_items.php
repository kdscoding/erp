<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('goods_receipt_items') && Schema::hasColumn('goods_receipt_items', 'rejected_qty')) {
            Schema::table('goods_receipt_items', function (Blueprint $table) {
                $table->dropColumn('rejected_qty');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('goods_receipt_items') && ! Schema::hasColumn('goods_receipt_items', 'rejected_qty')) {
            Schema::table('goods_receipt_items', function (Blueprint $table) {
                $table->decimal('rejected_qty', 14, 2)->default(0)->after('accepted_qty');
            });
        }
    }
};
