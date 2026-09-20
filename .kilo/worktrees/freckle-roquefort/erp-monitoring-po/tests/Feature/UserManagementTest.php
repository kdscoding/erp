<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['name' => 'Administrator', 'slug' => 'administrator'],
            ['name' => 'Staff', 'slug' => 'staff'],
            ['name' => 'Supervisor', 'slug' => 'supervisor'],
        ] as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], ['name' => $role['name']]);
        }
    }

    private function makeUserWithRole(string $roleSlug): User
    {
        $user = User::factory()->create();
        $roleId = Role::where('slug', $roleSlug)->value('id');
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $roleId]);

        return $user;
    }

    public function test_administrator_can_create_user_and_assign_role(): void
    {
        $admin = $this->makeUserWithRole('administrator');

        $this->actingAs($admin)->get('/settings/users/create')->assertOk();

        $this->actingAs($admin)->post('/settings/users', [
            'name' => 'Staff Baru',
            'nik' => '20240001',
            'email' => 'staff.baru@example.com',
            'role_slug' => 'staff',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHas('success');

        $userId = DB::table('users')->where('email', 'staff.baru@example.com')->value('id');

        $this->assertNotNull($userId);
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $userId,
            'role_id' => Role::where('slug', 'staff')->value('id'),
        ]);
    }

    public function test_administrator_can_update_user_role_without_changing_password(): void
    {
        $admin = $this->makeUserWithRole('administrator');
        $user = $this->makeUserWithRole('staff');
        $oldPasswordHash = $user->password;

        $this->actingAs($admin)->put("/settings/users/{$user->id}", [
            'name' => 'User Update',
            'nik' => '20240002',
            'email' => 'updated@example.com',
            'role_slug' => 'supervisor',
        ])->assertSessionHas('success');

        $user->refresh();

        $this->assertSame('User Update', $user->name);
        $this->assertSame('20240002', $user->nik);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame($oldPasswordHash, $user->password);
        $this->assertTrue($user->roles()->where('slug', 'supervisor')->exists());
    }

    public function test_administrator_can_only_reset_password_after_user_request_exists(): void
    {
        $admin = $this->makeUserWithRole('administrator');
        $user = $this->makeUserWithRole('staff');

        $this->actingAs($admin)->put("/settings/users/{$user->id}/reset-password", [
            'password' => 'Password456!',
            'password_confirmation' => 'Password456!',
            'admin_note' => 'Reset dicoba tanpa request.',
        ])->assertSessionHas('error');

        DB::table('password_reset_requests')->insert([
            'user_id' => $user->id,
            'request_note' => 'User meminta reset password lewat helpdesk.',
            'status' => 'pending',
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->put("/settings/users/{$user->id}/reset-password", [
            'password' => 'Password456!',
            'password_confirmation' => 'Password456!',
            'admin_note' => 'Identitas user sudah diverifikasi, reset diproses.',
        ])->assertSessionHas('success');

        $user->refresh();

        $this->assertTrue(Hash::check('Password456!', $user->password));
        $this->assertDatabaseHas('password_reset_requests', [
            'user_id' => $user->id,
            'status' => 'processed',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'users',
            'record_id' => $user->id,
            'action' => 'password_reset_by_admin',
        ]);
    }

    public function test_administrator_can_toggle_user_active_status(): void
    {
        $admin = $this->makeUserWithRole('administrator');
        $user = $this->makeUserWithRole('staff');

        $this->actingAs($admin)->patch("/settings/users/{$user->id}/status")->assertSessionHas('success');
        $this->assertFalse((bool) $user->fresh()->is_active);
    }

    public function test_non_administrator_cannot_access_user_management(): void
    {
        $staff = $this->makeUserWithRole('staff');

        $this->actingAs($staff)->get('/settings/users')->assertForbidden();
        $this->actingAs($staff)->get('/settings/users/create')->assertForbidden();
    }

    public function test_administrator_can_update_document_terms_from_settings(): void
    {
        $admin = $this->makeUserWithRole('administrator');

        $termId = DB::table('document_terms')->insertGetId([
            'group_key' => 'po_status',
            'code' => 'PO Issued',
            'label' => 'Released New PO',
            'description' => 'Initial label',
            'is_active' => true,
            'sort_order' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('suppliers')->insert([
            'supplier_code' => 'SUPSET001',
            'supplier_name' => 'Supplier Settings',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supplierId = DB::table('suppliers')->value('id');

        DB::table('purchase_orders')->insert([
            'po_number' => 'PO-SET-0001',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'status' => 'PO Issued',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->post('/settings/document-terms', [
            'document_terms' => [
                $termId => [
                    'label' => 'Released PO Baru',
                    'description' => 'Updated label',
                    'sort_order' => 10,
                    'is_active' => '1',
                ],
            ],
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('document_terms', [
            'id' => $termId,
            'label' => 'Released PO Baru',
        ]);

        $this->actingAs($admin)
            ->get('/po')
            ->assertOk()
            ->assertSee('Released PO Baru');
    }
}
