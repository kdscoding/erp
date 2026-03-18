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
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Purchasing', 'slug' => 'purchasing'],
            ['name' => 'Purchasing Manager', 'slug' => 'purchasing_manager'],
            ['name' => 'Warehouse', 'slug' => 'warehouse'],
            ['name' => 'BC Compliance', 'slug' => 'compliance'],
            ['name' => 'Viewer', 'slug' => 'viewer'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $admin = User::firstOrCreate(['email' => 'admin@erp.local'], [
            'name' => 'Admin ERP',
            'password' => Hash::make('password'),
        ]);

        $adminRoleId = Role::where('slug', 'admin')->value('id');
        DB::table('user_roles')->updateOrInsert([
            'user_id' => $admin->id,
            'role_id' => $adminRoleId,
        ], []);
    }
}
