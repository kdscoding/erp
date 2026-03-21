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
        DB::table('items')->insert(['item_code' => 'ITM002', 'item_name' => 'Label B', 'unit_id' => $unitId, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
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
        $itemId = DB::table('items')->where('item_code', 'ITM001')->value('id');

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
        $itemId = DB::table('items')->where('item_code', 'ITM001')->value('id');

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
            'supplier_id' => $supplierId,
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-0001',
            'selected_items' => [$poItemId],
            'shipped_qty' => [
                $poItemId => 100,
            ],
        ])->assertSessionHas('success');

        $shipmentId = DB::table('shipments')->value('id');
        $shipmentItemId = DB::table('shipment_items')->value('id');

        $this->assertDatabaseHas('shipments', ['id' => $shipmentId, 'status' => 'Draft']);
        $this->assertDatabaseHas('shipment_items', ['id' => $shipmentItemId, 'shipment_id' => $shipmentId, 'purchase_order_item_id' => $poItemId, 'shipped_qty' => 100]);

        $this->actingAs($user)->patch("/shipments/{$shipmentId}/mark-shipped")
            ->assertRedirect('/receiving?supplier_id='.$supplierId.'&shipment_id='.$shipmentId.'&document_number=SJ-0001')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_orders', ['id' => $poId, 'status' => 'Shipped']);
        $this->assertDatabaseHas('shipments', ['id' => $shipmentId, 'status' => 'Shipped']);

        $this->actingAs($user)->post('/receiving', [
            'shipment_item_id' => $shipmentItemId,
            'receipt_date' => now()->toDateString(),
            'received_qty' => 40,
            'document_number' => 'SJ-0001',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_order_items', ['id' => $poItemId, 'outstanding_qty' => 60]);
        $this->assertDatabaseHas('purchase_orders', ['id' => $poId, 'status' => 'Partial']);
        $this->assertDatabaseHas('shipment_items', ['id' => $shipmentItemId, 'received_qty' => 40]);
        $this->assertDatabaseHas('shipments', ['id' => $shipmentId, 'status' => 'Partial Received']);

        $this->actingAs($user)->post('/receiving', [
            'shipment_item_id' => $shipmentItemId,
            'receipt_date' => now()->toDateString(),
            'received_qty' => 60,
            'document_number' => 'SJ-0001',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_order_items', ['id' => $poItemId, 'outstanding_qty' => 0]);
        $this->assertDatabaseHas('purchase_orders', ['id' => $poId, 'status' => 'Closed']);
        $this->assertDatabaseHas('shipment_items', ['id' => $shipmentItemId, 'received_qty' => 100]);
        $this->assertDatabaseHas('shipments', ['id' => $shipmentId, 'status' => 'Received']);
    }

    public function test_one_supplier_document_can_cover_multiple_purchase_orders(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $itemAId = DB::table('items')->where('item_code', 'ITM001')->value('id');
        $itemBId = DB::table('items')->where('item_code', 'ITM002')->value('id');

        $poOneId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TEST-1001',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poTwoId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TEST-1002',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poOneItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poOneId,
            'item_id' => $itemAId,
            'ordered_qty' => 30,
            'received_qty' => 0,
            'outstanding_qty' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poTwoItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poTwoId,
            'item_id' => $itemBId,
            'ordered_qty' => 20,
            'received_qty' => 0,
            'outstanding_qty' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->post('/shipments', [
            'supplier_id' => $supplierId,
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-MULTI-01',
            'selected_items' => [$poOneItemId, $poTwoItemId],
            'shipped_qty' => [
                $poOneItemId => 10,
                $poTwoItemId => 20,
            ],
        ])->assertSessionHas('success');

        $shipmentId = DB::table('shipments')->value('id');

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $poOneItemId,
            'shipped_qty' => 10,
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $poTwoItemId,
            'shipped_qty' => 20,
        ]);

        $this->actingAs($user)->patch("/shipments/{$shipmentId}/mark-shipped")
            ->assertRedirect('/receiving?supplier_id='.$supplierId.'&shipment_id='.$shipmentId.'&document_number=SJ-MULTI-01')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_orders', ['id' => $poOneId, 'status' => 'Shipped']);
        $this->assertDatabaseHas('purchase_orders', ['id' => $poTwoId, 'status' => 'Shipped']);
    }

    public function test_draft_shipment_can_be_cancelled_without_being_deleted(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $itemId = DB::table('items')->where('item_code', 'ITM001')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TEST-CANCEL',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poId,
            'item_id' => $itemId,
            'ordered_qty' => 15,
            'received_qty' => 0,
            'outstanding_qty' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->post('/shipments', [
            'supplier_id' => $supplierId,
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-CANCEL-01',
            'selected_items' => [$poItemId],
            'shipped_qty' => [
                $poItemId => 15,
            ],
        ])->assertSessionHas('success');

        $shipmentId = DB::table('shipments')->value('id');

        $this->actingAs($user)->patch("/shipments/{$shipmentId}/cancel-draft")->assertSessionHas('success');

        $this->assertDatabaseHas('shipments', ['id' => $shipmentId, 'status' => 'Cancelled']);
    }

    public function test_same_delivery_note_for_same_supplier_cannot_be_processed_twice(): void
    {
        $firstUser = $this->makeUserWithRole('administrator');
        $secondUser = $this->makeUserWithRole('staff');
        $supplierId = DB::table('suppliers')->value('id');
        $itemId = DB::table('items')->where('item_code', 'ITM001')->value('id');

        $poOneId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TEST-DN-01',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poTwoId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TEST-DN-02',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poOneItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poOneId,
            'item_id' => $itemId,
            'ordered_qty' => 25,
            'received_qty' => 0,
            'outstanding_qty' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poTwoItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poTwoId,
            'item_id' => $itemId,
            'ordered_qty' => 10,
            'received_qty' => 0,
            'outstanding_qty' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($firstUser)->post('/shipments', [
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-DUP-01',
            'selected_items' => [$poOneItemId],
            'shipped_qty' => [
                $poOneItemId => 25,
            ],
        ])->assertSessionHas('success');

        $this->actingAs($secondUser)->post('/shipments', [
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-DUP-01',
            'selected_items' => [$poTwoItemId],
            'shipped_qty' => [
                $poTwoItemId => 10,
            ],
        ])->assertSessionHasErrors('delivery_note_number');

        $this->assertDatabaseCount('shipments', 1);
    }

    public function test_over_receipt_is_blocked_by_default(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $itemId = DB::table('items')->where('item_code', 'ITM001')->value('id');

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
            'supplier_id' => $supplierId,
            'shipment_number' => 'SHP-TEST-0002',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-0002',
            'status' => 'Shipped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shipmentItemId = DB::table('shipment_items')->insertGetId([
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $poItemId,
            'shipped_qty' => 10,
            'received_qty' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->from('/receiving')->post('/receiving', [
            'shipment_item_id' => $shipmentItemId,
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

    public function test_receiving_selection_can_be_cleared_explicitly(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $itemId = DB::table('items')->where('item_code', 'ITM001')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TEST-CLEAR-01',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Shipped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poId,
            'item_id' => $itemId,
            'ordered_qty' => 50,
            'received_qty' => 0,
            'outstanding_qty' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shipmentId = DB::table('shipments')->insertGetId([
            'purchase_order_id' => $poId,
            'supplier_id' => $supplierId,
            'shipment_number' => 'SHP-TEST-CLEAR-01',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-CLEAR-01',
            'status' => 'Shipped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipment_items')->insert([
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $poItemId,
            'shipped_qty' => 50,
            'received_qty' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/receiving?shipment_id='.$shipmentId)
            ->assertOk()
            ->assertSee('Warehouse akan memproses dokumen')
            ->assertSee('Batalkan Pilihan Dokumen');

        $this->actingAs($user)
            ->get('/receiving?clear_selection=1')
            ->assertOk()
            ->assertDontSee('Warehouse akan memproses dokumen')
            ->assertSee('Pilih dulu satu dokumen shipment di tabel atas');
    }
}
