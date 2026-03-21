<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterDataValidationTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        $user = User::factory()->create();
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $role->id]);

        return $user;
    }

    public function test_supplier_code_is_normalized_and_unique(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)->post('/suppliers', [
            'supplier_code' => ' sup-01 ',
            'supplier_name' => 'Supplier Satu',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', ['supplier_code' => 'SUP-01']);

        $this->actingAs($user)->from('/suppliers')->post('/suppliers', [
            'supplier_code' => 'SUP-01',
            'supplier_name' => 'Duplikat Supplier',
        ])->assertRedirect('/suppliers')->assertSessionHasErrors('supplier_code');
    }

    public function test_item_code_is_normalized_and_unique(): void
    {
        $user = $this->adminUser();
        DB::table('units')->insert(['unit_code' => 'PCS', 'unit_name' => 'Pieces', 'created_at' => now(), 'updated_at' => now()]);
        $categoryId = DB::table('item_categories')->insertGetId([
            'category_code' => 'CAT-LBL',
            'category_name' => 'Label',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->post('/masters/items', [
            'item_code' => ' lbl-001 ',
            'item_name' => 'Label A',
            'category_id' => $categoryId,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('items', ['item_code' => 'LBL-001']);

        $this->actingAs($user)->from('/masters/items')->post('/masters/items', [
            'item_code' => 'LBL-001',
            'item_name' => 'Label Duplicate',
        ])->assertRedirect('/masters/items')->assertSessionHasErrors('item_code');
    }

    public function test_supplier_can_be_edited(): void
    {
        $user = $this->adminUser();
        $supplierId = DB::table('suppliers')->insertGetId([
            'supplier_code' => 'SUP-A',
            'supplier_name' => 'Supplier Lama',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->put("/suppliers/{$supplierId}", [
            'supplier_code' => ' sup-b ',
            'supplier_name' => 'Supplier Baru',
            'email' => 'baru@example.com',
        ])->assertRedirect('/suppliers');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplierId,
            'supplier_code' => 'SUP-B',
            'supplier_name' => 'Supplier Baru',
            'email' => 'baru@example.com',
        ]);
    }

    public function test_item_can_be_edited(): void
    {
        $user = $this->adminUser();
        DB::table('units')->insert(['unit_code' => 'PCS', 'unit_name' => 'Pieces', 'created_at' => now(), 'updated_at' => now()]);
        $oldCategoryId = DB::table('item_categories')->insertGetId([
            'category_code' => 'CAT-OLD',
            'category_name' => 'Kategori Lama',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $newCategoryId = DB::table('item_categories')->insertGetId([
            'category_code' => 'CAT-NEW',
            'category_name' => 'Kategori Baru',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $itemId = DB::table('items')->insertGetId([
            'item_code' => 'ITM-01',
            'item_name' => 'Item Lama',
            'category_id' => $oldCategoryId,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->put("/masters/items/{$itemId}", [
            'item_code' => ' itm-02 ',
            'item_name' => 'Item Baru',
            'category_id' => $newCategoryId,
            'specification' => 'Ukuran 50 x 25 mm',
        ])->assertRedirect('/masters/items');

        $this->assertDatabaseHas('items', [
            'id' => $itemId,
            'item_code' => 'ITM-02',
            'item_name' => 'Item Baru',
            'category_id' => $newCategoryId,
            'specification' => 'Ukuran 50 x 25 mm',
        ]);
    }

    public function test_item_list_can_be_filtered_by_category(): void
    {
        $user = $this->adminUser();
        $categoryA = DB::table('item_categories')->insertGetId([
            'category_code' => 'CAT-A',
            'category_name' => 'Kategori A',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $categoryB = DB::table('item_categories')->insertGetId([
            'category_code' => 'CAT-B',
            'category_name' => 'Kategori B',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('items')->insert([
            [
                'item_code' => 'ITM-A',
                'item_name' => 'Item A',
                'category_id' => $categoryA,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_code' => 'ITM-B',
                'item_name' => 'Item B',
                'category_id' => $categoryB,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/masters/items?category_id='.$categoryA)
            ->assertOk()
            ->assertSee('Item A')
            ->assertDontSee('ITM-B');
    }

}
