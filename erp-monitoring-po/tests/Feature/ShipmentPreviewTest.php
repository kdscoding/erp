<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\DocumentTermCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShipmentPreviewTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        $user = User::factory()->create();
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $role->id]);

        return $user;
    }

    private function seedBasic(): void
    {
        DB::table('settings')->insert([
            'key' => 'allow_over_receipt',
            'value' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('suppliers')->insert(['supplier_code' => 'SUP001', 'supplier_name' => 'Supplier A', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);

        $unitId = DB::table('units')->insertGetId(['unit_code' => 'PCS', 'unit_name' => 'Pieces', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('items')->insert(['item_code' => 'ITM001', 'item_name' => 'Item Alpha', 'unit_id' => $unitId, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createPo(string $poNumber, string $supplierCode): int
    {
        $supplierId = DB::table('suppliers')->where('supplier_code', $supplierCode)->value('id');
        $itemId = DB::table('items')->where('item_code', 'ITM001')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => $poNumber,
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'currency' => 'IDR',
            'status' => DocumentTermCodes::PO_ISSUED,
            'eta_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'item_id' => $itemId,
            'ordered_qty' => 100,
            'received_qty' => 0,
            'outstanding_qty' => 100,
            'item_status' => DocumentTermCodes::ITEM_WAITING,
            'unit_price' => 50000,
            'etd_date' => null,
            'eta_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $poId;
    }

    public function test_preview_page_loads_for_draft_shipment(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();
        $poId = $this->createPo('PO-0001', 'SUP001');
        $poItemId = DB::table('purchase_order_items')->value('id');

        DB::table('shipments')->insert([
            'purchase_order_id' => $poId,
            'supplier_id' => DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id'),
            'shipment_number' => 'SHP-'.date('Ymd').'-0001',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'DN-001',
            'invoice_number' => 'INV-001',
            'invoice_date' => now()->toDateString(),
            'invoice_currency' => 'IDR',
            'supplier_remark' => 'Test remark',
            'status' => DocumentTermCodes::SHIPMENT_DRAFT,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shipmentId = DB::table('shipments')->value('id');

        DB::table('shipment_items')->insert([
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $poItemId,
            'shipped_qty' => 50,
            'received_qty' => 0,
            'invoice_unit_price' => 55000,
            'invoice_line_total' => 2750000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/shipments/'.$shipmentId.'/preview')
            ->assertOk()
            ->assertSee('Preview Draft Shipment')
            ->assertSee('SHP-'.date('Ymd').'-0001')
            ->assertSee('Supplier A')
            ->assertSee('Draft')
            ->assertSee('ITM001')
            ->assertSee('PO-0001')
            ->assertSee('DN-001')
            ->assertSee('INV-001')
            ->assertSee('Test remark')
            ->assertSee('50')
            ->assertSee('55.000')
            ->assertSee('2.750.000');
    }

    public function test_preview_returns_404_for_non_draft_shipment(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();
        $poId = $this->createPo('PO-0001', 'SUP001');

        DB::table('shipments')->insert([
            'purchase_order_id' => $poId,
            'supplier_id' => DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id'),
            'shipment_number' => 'SHP-'.date('Ymd').'-0001',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'DN-001',
            'status' => DocumentTermCodes::SHIPMENT_SHIPPED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shipmentId = DB::table('shipments')->value('id');

        $this->actingAs($user)
            ->get('/shipments/'.$shipmentId.'/preview')
            ->assertStatus(404);
    }

    public function test_preview_returns_404_for_nonexistent_shipment(): void
    {
        $this->seedBasic();
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get('/shipments/99999/preview')
            ->assertStatus(404);
    }

    public function test_redirect_unauthenticated_user_from_preview(): void
    {
        $this->seedBasic();

        $this->get('/shipments/1/preview')
            ->assertRedirect('/login');
    }
}
