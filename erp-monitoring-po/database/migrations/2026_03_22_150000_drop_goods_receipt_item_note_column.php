<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('goods_receipt_items') || ! Schema::hasColumn('goods_receipt_items', 'note')) {
            return;
        }

        if (Schema::hasColumn('goods_receipt_items', 'remark')) {
            DB::table('goods_receipt_items')
                ->whereNull('remark')
                ->whereNotNull('note')
                ->update([
                    'remark' => DB::raw('note'),
                    'updated_at' => now(),
                ]);
        }

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('goods_receipt_items') || Schema::hasColumn('goods_receipt_items', 'note')) {
            return;
        }

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->text('note')->nullable()->after('qty_variance');
        });

        if (Schema::hasColumn('goods_receipt_items', 'remark')) {
            DB::table('goods_receipt_items')
                ->whereNull('note')
                ->whereNotNull('remark')
                ->update([
                    'note' => DB::raw('remark'),
                    'updated_at' => now(),
                ]);
        }
    }
};
