<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Administrator', 'slug' => 'administrator'],
            ['name' => 'Supervisor', 'slug' => 'supervisor'],
            ['name' => 'Receiver', 'slug' => 'receiver'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $admin = User::firstOrCreate(['email' => 'admin@erp.local'], [
            'name' => 'Admin ERP',
            'password' => Hash::make('password'),
        ]);

        $adminRoleId = Role::where('slug', 'administrator')->value('id');
        DB::table('user_roles')->updateOrInsert([
            'user_id' => $admin->id,
            'role_id' => $adminRoleId,
        ], []);

        DB::table('settings')->updateOrInsert(
            ['key' => 'allow_over_receipt'],
            ['value' => '0', 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
