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

        DB::table('goods_receipt_items')->insert([
            'goods_receipt_id' => $firstGrId,
            'purchase_order_item_id' => $firstPoItemId,
            'item_id' => $itemId,
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
                && (int) $firstRow->receipt_count === 1
                && $firstRow->first_receipt_date === '2026-04-02'
                && $secondRow->first_receipt_date === null
                && (int) $secondRow->receipt_count === 0;
        });
    }
}
