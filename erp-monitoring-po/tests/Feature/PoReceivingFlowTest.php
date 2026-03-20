<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PoReceivingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('settings')->insert([
            'key' => 'allow_over_receipt',
            'value' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seedBasic();
    }

    private function seedBasic(): void
    {
        $roleIds = [];
        foreach (['administrator', 'staff', 'supervisor'] as $slug) {
            $roleIds[$slug] = Role::updateOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst(str_replace('_', ' ', $slug))]
            )->id;
        }

        DB::table('suppliers')->insert(['supplier_code' => 'SUP001', 'supplier_name' => 'Supplier A', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('units')->insert(['unit_code' => 'PCS', 'unit_name' => 'Pieces', 'created_at' => now(), 'updated_at' => now()]);
        $unitId = DB::table('units')->value('id');
        DB::table('items')->insert(['item_code' => 'ITM001', 'item_name' => 'Label A', 'unit_id' => $unitId, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function makeUserWithRole(string $roleSlug): User
    {
        $user = User::factory()->create();
        $roleId = Role::where('slug', $roleSlug)->value('id');
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $roleId]);

        return $user;
    }

    public function test_po_creation_sets_initial_status_to_po_issued(): void
    {
        $user = $this->makeUserWithRole('staff');
        $supplierId = DB::table('suppliers')->value('id');
        $itemId = DB::table('items')->value('id');

        $resp = $this->actingAs($user)->post('/po', [
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'items' => [
                ['item_id' => $itemId, 'ordered_qty' => 100],
            ],
        ]);

        $resp->assertSessionHasNoErrors();
        $poId = DB::table('purchase_orders')->value('id');
        $this->assertNotNull($poId);
        $this->assertDatabaseHas('purchase_orders', ['id' => $poId, 'status' => 'PO Issued']);
        $this->assertDatabaseHas('po_status_histories', [
            'purchase_order_id' => $poId,
            'from_status' => null,
            'to_status' => 'PO Issued',
        ]);
    }

    public function test_shipment_partial_and_full_receipt_auto_close(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $itemId = DB::table('items')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TEST-0001',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poId,
            'item_id' => $itemId,
            'ordered_qty' => 100,
            'received_qty' => 0,
            'outstanding_qty' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->post('/shipments', [
            'purchase_order_id' => $poId,
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-0001',
        ])->assertSessionHas('success');

        $shipmentId = DB::table('shipments')->value('id');
        $this->assertDatabaseHas('shipments', ['id' => $shipmentId, 'status' => 'Draft']);

        $this->actingAs($user)->patch("/shipments/{$shipmentId}/mark-shipped")->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_orders', ['id' => $poId, 'status' => 'Shipped']);
        $this->assertDatabaseHas('shipments', ['id' => $shipmentId, 'status' => 'Shipped']);

        $this->actingAs($user)->post('/receiving', [
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $poItemId,
            'receipt_date' => now()->toDateString(),
            'received_qty' => 40,
            'document_number' => 'SJ-0001',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_order_items', ['id' => $poItemId, 'outstanding_qty' => 60]);
        $this->assertDatabaseHas('purchase_orders', ['id' => $poId, 'status' => 'Partial']);
        $this->assertDatabaseHas('shipments', ['id' => $shipmentId, 'status' => 'Partial Received']);

        $this->actingAs($user)->post('/receiving', [
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $poItemId,
            'receipt_date' => now()->toDateString(),
            'received_qty' => 60,
            'document_number' => 'SJ-0001',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_order_items', ['id' => $poItemId, 'outstanding_qty' => 0]);
        $this->assertDatabaseHas('purchase_orders', ['id' => $poId, 'status' => 'Closed']);
        $this->assertDatabaseHas('shipments', ['id' => $shipmentId, 'status' => 'Received']);
    }

    public function test_over_receipt_is_blocked_by_default(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $itemId = DB::table('items')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TEST-0002',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Shipped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poId,
            'item_id' => $itemId,
            'ordered_qty' => 10,
            'received_qty' => 0,
            'outstanding_qty' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shipmentId = DB::table('shipments')->insertGetId([
            'purchase_order_id' => $poId,
            'shipment_number' => 'SHP-TEST-0002',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-0002',
            'status' => 'Shipped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->from('/receiving')->post('/receiving', [
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $poItemId,
            'receipt_date' => now()->toDateString(),
            'received_qty' => 11,
            'document_number' => 'SJ-0002',
        ])->assertRedirect('/receiving')->assertSessionHas('error');
    }

    public function test_role_restriction_for_receiving_page(): void
    {
        $viewer = $this->makeUserWithRole('supervisor');

        $this->actingAs($viewer)->get('/receiving')->assertForbidden();
    }
}
