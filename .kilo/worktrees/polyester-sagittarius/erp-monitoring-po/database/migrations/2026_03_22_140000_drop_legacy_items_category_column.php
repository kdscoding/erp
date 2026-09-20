<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('items') || ! Schema::hasColumn('items', 'category')) {
            return;
        }

        if (! Schema::hasTable('item_categories') || ! Schema::hasColumn('items', 'category_id')) {
            return;
        }

        DB::table('items')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('id')
            ->get(['id', 'category', 'category_id'])
            ->each(function ($item) {
                $categoryName = trim((string) $item->category);
                if ($categoryName === '') {
                    return;
                }

                $categoryId = $item->category_id ?: DB::table('item_categories')
                    ->where('category_name', $categoryName)
                    ->value('id');

                if (! $categoryId) {
                    $categoryId = DB::table('item_categories')->insertGetId([
                        'category_code' => 'CAT-'.str_pad((string) (DB::table('item_categories')->count() + 1), 3, '0', STR_PAD_LEFT),
                        'category_name' => $categoryName,
                        'description' => 'Migrated before dropping legacy category column.',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('items')->where('id', $item->id)->update([
                    'category_id' => $categoryId,
                    'updated_at' => now(),
                ]);
            });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('items') || Schema::hasColumn('items', 'category')) {
            return;
        }

        Schema::table('items', function (Blueprint $table) {
            $table->string('category')->nullable()->after('item_name');
        });

        if (! Schema::hasTable('item_categories') || ! Schema::hasColumn('items', 'category_id')) {
            return;
        }

        DB::table('items')
            ->leftJoin('item_categories', 'item_categories.id', '=', 'items.category_id')
            ->whereNotNull('items.category_id')
            ->get(['items.id', 'item_categories.category_name'])
            ->each(function ($item) {
                DB::table('items')->where('id', $item->id)->update([
                    'category' => $item->category_name,
                    'updated_at' => now(),
                ]);
            });
    }
};
