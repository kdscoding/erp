<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardMonitoringTest extends TestCase
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

    public function test_dashboard_shows_saved_views_and_action_center(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $itemAId = DB::table('items')->where('item_code', 'ITM001')->value('id');
        $itemBId = DB::table('items')->where('item_code', 'ITM002')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-DASH-0001',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $waitingItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poId,
            'item_id' => $itemAId,
            'ordered_qty' => 25,
            'received_qty' => 0,
            'outstanding_qty' => 25,
            'item_status' => 'Waiting',
            'etd_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $incomingItemId = DB::table('purchase_order_items')->insertGetId([
            'purchase_order_id' => $poId,
            'item_id' => $itemBId,
            'ordered_qty' => 40,
            'received_qty' => 0,
            'outstanding_qty' => 40,
            'item_status' => 'Confirmed',
            'etd_date' => now()->addDays(3)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shipmentId = DB::table('shipments')->insertGetId([
            'purchase_order_id' => $poId,
            'supplier_id' => $supplierId,
            'shipment_number' => 'SHP-DASH-0001',
            'shipment_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-DASH-0001',
            'status' => 'Shipped',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('shipment_items')->insert([
            'shipment_id' => $shipmentId,
            'purchase_order_item_id' => $incomingItemId,
            'shipped_qty' => 20,
            'received_qty' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('At-Risk Hari Ini')
            ->assertSee('Incoming Minggu Ini')
            ->assertSee('Action Center')
            ->assertSee('Items Need ETD Update')
            ->assertSee('Partial Receiving Queue')
            ->assertSee('PO-DASH-0001')
            ->assertSee('ITM001')
            ->assertSee('SHP-DASH-0001');
    }

    public function test_monitoring_page_and_export_show_summary_and_item_detail(): void
    {
        $user = $this->makeUserWithRole('administrator');
        $supplierId = DB::table('suppliers')->value('id');
        $itemId = DB::table('items')->where('item_code', 'ITM001')->value('id');

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-MON-0001',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'item_id' => $itemId,
            'ordered_qty' => 50,
            'received_qty' => 10,
            'outstanding_qty' => 40,
            'item_status' => 'Partial',
            'etd_date' => now()->addDays(2)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/monitoring')
            ->assertOk()
            ->assertSee('Monitoring Summary Per Purchase Order')
            ->assertSee('Monitoring Detail Per Item')
            ->assertSee('PO-MON-0001')
            ->assertSee('ITM001');

        $this->actingAs($user)
            ->get('/monitoring/export-excel')
            ->assertOk()
            ->assertSee('Monitoring Summary Per Purchase Order')
            ->assertSee('Monitoring Detail Per Item')
            ->assertSee('PO-MON-0001')
            ->assertSee('Partial');
    }
}
