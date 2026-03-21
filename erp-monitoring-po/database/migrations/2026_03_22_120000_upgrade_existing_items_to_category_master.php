<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('item_categories')) {
            Schema::create('item_categories', function (Blueprint $table) {
                $table->id();
                $table->string('category_code')->unique();
                $table->string('category_name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('items') && ! Schema::hasColumn('items', 'category_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('item_name')->constrained('item_categories');
            });
        }

        if (
            Schema::hasTable('items') &&
            Schema::hasColumn('items', 'category') &&
            Schema::hasColumn('items', 'category_id') &&
            Schema::hasTable('item_categories')
        ) {
            DB::table('items')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->orderBy('id')
                ->get(['id', 'category', 'category_id'])
                ->each(function ($item) {
                    if ($item->category_id) {
                        return;
                    }

                    $name = trim((string) $item->category);
                    if ($name === '') {
                        return;
                    }

                    $existingId = DB::table('item_categories')->where('category_name', $name)->value('id');
                    if (! $existingId) {
                        $existingId = DB::table('item_categories')->insertGetId([
                            'category_code' => 'CAT-'.str_pad((string) (DB::table('item_categories')->count() + 1), 3, '0', STR_PAD_LEFT),
                            'category_name' => $name,
                            'description' => 'Migrated from legacy item category column.',
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('items')->where('id', $item->id)->update([
                        'category_id' => $existingId,
                        'updated_at' => now(),
                    ]);
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'category_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('category_id');
            });
        }

        if (Schema::hasTable('item_categories')) {
            Schema::dropIfExists('item_categories');
        }
    }
};
