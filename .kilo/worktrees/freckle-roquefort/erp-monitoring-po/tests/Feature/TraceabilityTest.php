<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TraceabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedBasic();
    }

    private function seedBasic(): void
    {
        foreach (['administrator', 'staff', 'supervisor'] as $slug) {
            Role::updateOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst(str_replace('_', ' ', $slug))]
            );
        }

        DB::table('suppliers')->insert([
            'supplier_code' => 'SUP001',
            'supplier_name' => 'Supplier A',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('units')->insert([
            'unit_code' => 'PCS',
            'unit_name' => 'Pieces',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unitId = DB::table('units')->value('id');

        DB::table('items')->insert([
            [
                'item_code' => 'ITM001',
                'item_name' => 'Label A',
                'unit_id' => $unitId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_code' => 'ITM002',
                'item_name' => 'Label B',
                'unit_id' => $unitId,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function makeUserWithRole(string $roleSlug): User
    {
        $user = User::factory()->create();
        $roleId = Role::where('slug', $roleSlug)->value('id');
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $roleId]);

        return $user;
    }

    public function test_traceability_lists_each_purchase_order_item_as_a_separate_row(): void
    {
        $user = $this->makeUserWithRole('supervisor');
        $supplierId = DB::table('suppliers')->value('id');
        $itemId = DB::table('items')->where('item_code', 'ITM001')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-TRACE-0001',
            'po_date' => '2026-04-01',
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $firstPoItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poId,
            'item_id' => $itemId,
            'ordered_qty' => 100,
            'received_qty' => 40,
            'outstanding_qty' => 60,
            'etd_date' => '2026-04-03',
            'item_status' => 'Partial',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $secondPoItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poId,
            'item_id' => $itemId,
            'ordered_qty' => 100,
            'received_qty' => 0,
            'outstanding_qty' => 100,
            'etd_date' => '2026-04-05',
            'item_status' => 'Confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $firstGrId = DB::table('goods_receipts')->insertGetId([
            'gr_number' => 'GR-TRACE-0001',
            'receipt_date' => '2026-04-02',
            'purchase_order_id' => $poId,
            'document_number' => 'SJ-TRACE-0001',
            'status' => 'Posted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shipmentId = DB::table('shipments')->insertGetId([
            'purchase_order_id' => $poId,
            'supplier_id' => $supplierId,
            'shipment_number' => 'SHP-TRACE-0001',
            'shipment_date' => '2026-04-01',
            'delivery_note_number' => 'SJ-TRACE-0001',
            'status' => 'Partial Received',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipment_items')->insert([
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $firstPoItemId,
            'shipped_qty' => 40,
            'received_qty' => 40,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('goods_receipt_items')->insert([
            'goods_receipt_id' => $firstGrId,
            'purchase_order_item_id' => $firstPoItemId,
            'received_qty' => 40,
            'accepted_qty' => 40,
            'qty_variance' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/traceability?po_number=PO-TRACE-0001');

        $response->assertOk();
        $response->assertViewHas('rows', function ($rows) use ($firstPoItemId, $secondPoItemId) {
            if ($rows->count() !== 2) {
                return false;
            }

            $firstRow = $rows->firstWhere('purchase_order_item_id', $firstPoItemId);
            $secondRow = $rows->firstWhere('purchase_order_item_id', $secondPoItemId);

            return $firstRow
                && $secondRow
                && (int) $firstRow->shipment_count === 1
                && $firstRow->first_shipment_date === '2026-04-01'
                && str_contains((string) $firstRow->shipment_numbers, 'SHP-TRACE-0001')
                && (int) $firstRow->receipt_count === 1
                && $firstRow->first_receipt_date === '2026-04-02'
                && $secondRow->first_receipt_date === null
                && (int) $secondRow->receipt_count === 0;
        });
    }

    public function test_traceability_supports_supplier_item_and_status_filters(): void
    {
        $user = $this->makeUserWithRole('supervisor');

        DB::table('suppliers')->insert([
            'supplier_code' => 'SUP002',
            'supplier_name' => 'Supplier B',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supplierAId = DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id');
        $supplierBId = DB::table('suppliers')->where('supplier_code', 'SUP002')->value('id');
        $itemAId = DB::table('items')->where('item_code', 'ITM001')->value('id');
        $itemBId = DB::table('items')->where('item_code', 'ITM002')->value('id');

        $poAId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-FILTER-0001',
            'po_date' => '2026-04-01',
            'supplier_id' => $supplierAId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poBId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-FILTER-0002',
            'po_date' => '2026-04-01',
            'supplier_id' => $supplierBId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            'purchase_order_id' => $poAId,
            'item_id' => $itemAId,
            'ordered_qty' => 10,
            'received_qty' => 0,
            'outstanding_qty' => 10,
            'item_status' => 'Confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            'purchase_order_id' => $poBId,
            'item_id' => $itemBId,
            'ordered_qty' => 15,
            'received_qty' => 0,
            'outstanding_qty' => 15,
            'item_status' => 'Waiting',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/traceability?supplier_id=' . $supplierAId . '&item_keyword=ITM001&item_status=Confirmed');

        $response->assertOk();
        $response->assertViewHas('rows', function ($rows) {
            return $rows->count() === 1
                && $rows->first()->po_number === 'PO-FILTER-0001'
                && $rows->first()->item_code === 'ITM001'
                && $rows->first()->item_status === 'Confirmed';
        });
    }
}
