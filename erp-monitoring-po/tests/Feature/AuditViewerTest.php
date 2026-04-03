<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditViewerTest extends TestCase
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
    }

    private function makeUserWithRole(string $roleSlug, string $name = 'User'): User
    {
        $user = User::factory()->create(['name' => $name]);
        $roleId = Role::where('slug', $roleSlug)->value('id');
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $roleId]);

        return $user;
    }

    public function test_administrator_can_filter_audit_logs_by_module_actor_and_date(): void
    {
        $admin = $this->makeUserWithRole('administrator', 'Admin Audit');
        $staff = $this->makeUserWithRole('staff', 'Staff Ops');

        DB::table('audit_logs')->insert([
            [
                'module' => 'shipments',
                'record_id' => 10,
                'action' => 'create',
                'old_values' => null,
                'new_values' => json_encode(['shipment_number' => 'SHP-AUD-01', 'status' => 'Draft']),
                'user_id' => $staff->id,
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'module' => 'purchase_orders',
                'record_id' => 44,
                'action' => 'schedule_update',
                'old_values' => json_encode(['etd_date' => '2026-04-01']),
                'new_values' => json_encode(['etd_date' => '2026-04-03']),
                'user_id' => $admin->id,
                'ip_address' => '127.0.0.2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($admin)
            ->get('/audit?module=purchase_orders&actor_id=' . $admin->id . '&date_from=' . now()->format('Y-m-d'))
            ->assertOk()
            ->assertSee('Audit Log List')
            ->assertSee('Admin Audit')
            ->assertSee('Administrator')
            ->assertSee('purchase_orders')
            ->assertSee('schedule_update')
            ->assertSee('#44')
            ->assertSee('Changed Fields')
            ->assertSee('View Detail')
            ->assertSee('etd_date: 2026-04-01')
            ->assertSee('etd_date: 2026-04-03')
            ->assertDontSee('#10');
    }

    public function test_audit_viewer_can_filter_by_record_id_and_show_pretty_payload_detail(): void
    {
        $admin = $this->makeUserWithRole('administrator', 'Admin Audit');

        DB::table('audit_logs')->insert([
            'module' => 'goods_receipts',
            'record_id' => 99,
            'action' => 'cancel',
            'old_values' => json_encode(['status' => 'Posted', 'accepted_qty' => 10]),
            'new_values' => json_encode(['status' => 'Cancelled', 'accepted_qty' => 0, 'cancel_reason' => 'Wrong document']),
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.9',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/audit?record_id=99')
            ->assertOk()
            ->assertSee('goods_receipts')
            ->assertSee('#99')
            ->assertSee('cancel_reason')
            ->assertSee('Wrong document')
            ->assertSee('Before Payload')
            ->assertSee('After Payload');
    }

    public function test_staff_cannot_access_audit_viewer(): void
    {
        $staff = $this->makeUserWithRole('staff', 'Staff Ops');

        $this->actingAs($staff)
            ->get('/audit')
            ->assertForbidden();
    }
}
