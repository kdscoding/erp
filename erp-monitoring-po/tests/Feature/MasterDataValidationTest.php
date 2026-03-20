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

        $this->actingAs($user)->post('/masters/items', [
            'item_code' => ' lbl-001 ',
            'item_name' => 'Label A',
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
        $itemId = DB::table('items')->insertGetId([
            'item_code' => 'ITM-01',
            'item_name' => 'Item Lama',
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->put("/masters/items/{$itemId}", [
            'item_code' => ' itm-02 ',
            'item_name' => 'Item Baru',
        ])->assertRedirect('/masters/items');

        $this->assertDatabaseHas('items', [
            'id' => $itemId,
            'item_code' => 'ITM-02',
            'item_name' => 'Item Baru',
        ]);
    }

}
