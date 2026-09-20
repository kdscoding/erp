<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrackingDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['administrator', 'staff', 'supervisor'] as $slug) {
            Role::updateOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst(str_replace('_', ' ', $slug))]
            );
        }

        DB::table('settings')->insert([
            'key' => 'allow_over_receipt',
            'value' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('suppliers')->insert([
            [
                'supplier_code' => 'SUP001',
                'supplier_name' => 'Supplier A',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_code' => 'SUP002',
                'supplier_name' => 'Supplier B',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('units')->insert([
            ['unit_code' => 'PCS', 'unit_name' => 'Pieces', 'created_at' => now(), 'updated_at' => now()],
            ['unit_code' => 'KG', 'unit_name' => 'Kilogram', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->unitId = DB::table('units')->value('id');

        DB::table('item_categories')->insert([
            [
                'category_code' => 'CAT-PRIM',
                'category_name' => 'Primary',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_code' => 'CAT-CONS',
                'category_name' => 'Consumable',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->categoryId = DB::table('item_categories')->value('id');

        DB::table('items')->insert([
            [
                'item_code' => 'ITM001',
                'item_name' => 'Label A',
                'category_id' => $this->categoryId,
                'unit_id' => $this->unitId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_code' => 'ITM002',
                'item_name' => 'Label B',
                'category_id' => null,
                'unit_id' => $this->unitId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_code' => 'ITM003',
                'item_name' => 'Label C',
                'category_id' => null,
                'unit_id' => $this->unitId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->itemAId = DB::table('items')->where('item_code', 'ITM001')->value('id');
        $this->itemBId = DB::table('items')->where('item_code', 'ITM002')->value('id');
        $this->itemCId = DB::table('items')->where('item_code', 'ITM003')->value('id');
    }

    private function makeUserWithRole(string $roleSlug): User
    {
        $user = User::factory()->create();
        $roleId = Role::where('slug', $roleSlug)->value('id');
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $roleId]);

        return $user;
    }

    public function test_tracking_page_requires_authentication(): void
    {
        $this->get('/tracking')
            ->assertRedirect('login');
    }

    public function test_tracking_page_renders_for_administrator(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-0001',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId,
                'item_id' => $this->itemAId,
                'ordered_qty' => 50,
                'received_qty' => 20,
                'outstanding_qty' => 30,
                'item_status' => 'Partial',
                'etd_date' => now()->addDays(2)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'purchase_order_id' => $poId,
                'item_id' => $this->itemBId,
                'ordered_qty' => 30,
                'received_qty' => 0,
                'outstanding_qty' => 30,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $shipmentId = DB::table('shipments')->insertGetId([
            'purchase_order_id' => $poId,
            'supplier_id' => $supplierId,
            'shipment_number' => 'SHP-TRK-0001',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-TRK-0001',
            'status' => 'Shipped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipment_items')->insert([
            [
                'shipment_id' => $shipmentId,
                'purchase_order_item_id' => $this->itemAId,
                'shipped_qty' => 20,
                'received_qty' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking')
            ->assertOk()
            ->assertSee('Fulfillment Tracking')
            ->assertSee('Unified Tracking Table')
            ->assertSee('PO-TRK-0001')
            ->assertSee('ITM001')
            ->assertSee('ITM002')
            ->assertSee('Supplier A')
            ->assertSee('Partial')
            ->assertSee('Waiting')
            ->assertSee('Open')
            ->assertSee('50')
            ->assertSee('30')
            ->assertSee('Detail');
    }

    public function test_tracking_page_shows_po_level_rows_with_item_codes(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-0002',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId,
                'item_id' => $this->itemAId,
                'ordered_qty' => 100,
                'received_qty' => 100,
                'outstanding_qty' => 0,
                'item_status' => 'Closed',
                'etd_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking')
            ->assertOk()
            ->assertSee('PO-TRK-0002')
            ->assertSee('ITM001')
            ->assertSee('Open');
    }

    public function test_tracking_page_filters_by_supplier(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierAId = DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id');
        $supplierBId = DB::table('suppliers')->where('supplier_code', 'SUP002')->value('id');

        DB::table('purchase_orders')->insert([
            [
                'po_number' => 'PO-TRK-0003',
                'po_date' => now()->toDateString(),
                'supplier_id' => $supplierAId,
                'status' => 'Open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'po_number' => 'PO-TRK-0004',
                'po_date' => now()->toDateString(),
                'supplier_id' => $supplierBId,
                'status' => 'Open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => DB::table('purchase_orders')->where('po_number', 'PO-TRK-0003')->value('id'),
                'item_id' => $this->itemAId,
                'ordered_qty' => 10,
                'received_qty' => 0,
                'outstanding_qty' => 10,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'purchase_order_id' => DB::table('purchase_orders')->where('po_number', 'PO-TRK-0004')->value('id'),
                'item_id' => $this->itemBId,
                'ordered_qty' => 20,
                'received_qty' => 0,
                'outstanding_qty' => 20,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking?supplier_id=' . $supplierAId)
            ->assertOk()
            ->assertSee('PO-TRK-0003')
            ->assertSee('Supplier A');
    }

    public function test_tracking_page_filters_by_type(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-0005',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId,
                'item_id' => $this->itemAId,
                'ordered_qty' => 50,
                'received_qty' => 0,
                'outstanding_qty' => 50,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking?type=po')
            ->assertOk()
            ->assertSee('PO-TRK-0005');
    }

    public function test_tracking_page_filters_by_status(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-0006',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId,
                'item_id' => $this->itemAId,
                'ordered_qty' => 50,
                'received_qty' => 50,
                'outstanding_qty' => 0,
                'item_status' => 'Closed',
                'etd_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'purchase_order_id' => $poId,
                'item_id' => $this->itemBId,
                'ordered_qty' => 30,
                'received_qty' => 0,
                'outstanding_qty' => 30,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking?status=Open')
            ->assertOk()
            ->assertSee('Open');
    }

    public function test_tracking_page_shows_shipment_progress(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-PROG-001',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Item fully received = Fully Shipped
        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId,
                'item_id' => $this->itemAId,
                'ordered_qty' => 100,
                'received_qty' => 100,
                'outstanding_qty' => 0,
                'item_status' => 'Closed',
                'etd_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Item partially received = Partially Shipped
        $poId2 = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-PROG-002',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId2,
                'item_id' => $this->itemBId,
                'ordered_qty' => 50,
                'received_qty' => 20,
                'outstanding_qty' => 30,
                'item_status' => 'Partial',
                'etd_date' => now()->addDays(2)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Item not shipped at all = Not Shipped
        $poId3 = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-PROG-003',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId3,
                'item_id' => $this->itemCId,
                'ordered_qty' => 30,
                'received_qty' => 0,
                'outstanding_qty' => 30,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking')
            ->assertOk()
            ->assertSee('Fully Shipped')
            ->assertSee('Partially Shipped')
            ->assertSee('Not Shipped')
            ->assertSee('PO-TRK-PROG-001')
            ->assertSee('PO-TRK-PROG-002')
            ->assertSee('PO-TRK-PROG-003');
    }

    public function test_tracking_page_shows_shipped_stage(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-0008',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId,
                'item_id' => $this->itemAId,
                'ordered_qty' => 50,
                'received_qty' => 0,
                'outstanding_qty' => 50,
                'item_status' => 'Confirmed',
                'etd_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $shipmentId = DB::table('shipments')->insertGetId([
            'purchase_order_id' => $poId,
            'supplier_id' => $supplierId,
            'shipment_number' => 'SHP-TRK-0008',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-TRK-0008',
            'status' => 'Shipped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipment_items')->insert([
            [
                'shipment_id' => $shipmentId,
                'purchase_order_item_id' => $this->itemAId,
                'shipped_qty' => 30,
                'received_qty' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking')
            ->assertOk()
            ->assertSee('Open')
            ->assertSee('Shipped');
    }

    public function test_tracking_page_drill_down_shows_shipment_history(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-0009',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId,
                'item_id' => $this->itemAId,
                'ordered_qty' => 50,
                'received_qty' => 0,
                'outstanding_qty' => 50,
                'item_status' => 'Confirmed',
                'etd_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $shipmentId = DB::table('shipments')->insertGetId([
            'purchase_order_id' => $poId,
            'supplier_id' => $supplierId,
            'shipment_number' => 'SHP-TRK-0009',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-TRK-0009',
            'status' => 'Shipped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipment_items')->insert([
            [
                'shipment_id' => $shipmentId,
                'purchase_order_item_id' => $this->itemAId,
                'shipped_qty' => 30,
                'received_qty' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking')
            ->assertOk()
            ->assertSee('SHP-TRK-0009')
            ->assertSee('SJ-TRK-0009')
            ->assertSee('Detail');
    }

    public function test_tracking_page_filter_toggle_is_visible(): void
    {
        $user = $this->makeUserWithRole('administrator');

        $this->actingAs($user)
            ->get('/tracking')
            ->assertOk()
            ->assertSee('Tampilkan Filter');
    }

    public function test_tracking_page_accessible_by_staff_and_supervisor(): void
    {
        $staff = $this->makeUserWithRole('staff');
        $supervisor = $this->makeUserWithRole('supervisor');

        $this->actingAs($staff)->get('/tracking')->assertOk();
        $this->actingAs($supervisor)->get('/tracking')->assertOk();
    }

    public function test_tracking_page_shows_category_filter(): void
    {
        $user = $this->makeUserWithRole('administrator');

        $this->actingAs($user)
            ->get('/tracking')
            ->assertOk()
            ->assertSee('Kategori Barang')
            ->assertSee('Semua Kategori')
            ->assertSee('Primary')
            ->assertSee('Consumable');
    }

    public function test_tracking_page_shows_category_column(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $primaryCategoryId = DB::table('item_categories')->where('category_name', 'Primary')->value('id');
        $itemAId = DB::table('items')->where('item_code', 'ITM001')->value('id');
        $itemBId = DB::table('items')->where('item_code', 'ITM002')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-CAT-001',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId,
                'item_id' => $itemAId,
                'ordered_qty' => 50,
                'received_qty' => 0,
                'outstanding_qty' => 50,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'purchase_order_id' => $poId,
                'item_id' => $itemBId,
                'ordered_qty' => 30,
                'received_qty' => 0,
                'outstanding_qty' => 30,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking')
            ->assertOk()
            ->assertSee('Kategori')
            ->assertSee('Primary')
            ->assertSee('Tanpa Kategori');
    }

    public function test_tracking_page_filters_by_category(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $primaryCategoryId = DB::table('item_categories')->where('category_name', 'Primary')->value('id');
        $itemAId = DB::table('items')->where('item_code', 'ITM001')->value('id');
        $itemBId = DB::table('items')->where('item_code', 'ITM002')->value('id');

        $poId1 = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-CAT-002',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId1,
                'item_id' => $itemAId,
                'ordered_qty' => 50,
                'received_qty' => 0,
                'outstanding_qty' => 50,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $poId2 = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRK-CAT-003',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            [
                'purchase_order_id' => $poId2,
                'item_id' => $itemBId,
                'ordered_qty' => 30,
                'received_qty' => 0,
                'outstanding_qty' => 30,
                'item_status' => 'Waiting',
                'etd_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get('/tracking?category_id=' . $primaryCategoryId)
            ->assertOk()
            ->assertSee('PO-TRK-CAT-002')
            ->assertSee('ITM001')
            ->assertSee('Primary');
    }
}
