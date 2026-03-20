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
            ['name' => 'Staff', 'slug' => 'staff'],
            ['name' => 'Supervisor', 'slug' => 'supervisor'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $admin = User::updateOrCreate(['email' => 'admin@erp.local'], [
            'name' => 'Administrator ERP',
            'nik' => '10000001',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $adminRoleId = Role::where('slug', 'administrator')->value('id');
        DB::table('user_roles')->updateOrInsert([
            'user_id' => $admin->id,
            'role_id' => $adminRoleId,
        ], []);

        $staff = User::updateOrCreate(['email' => 'staff@erp.local'], [
            'name' => 'Staff ERP',
            'nik' => '10000002',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $staffRoleId = Role::where('slug', 'staff')->value('id');
        DB::table('user_roles')->updateOrInsert([
            'user_id' => $staff->id,
            'role_id' => $staffRoleId,
        ], []);

        $supervisor = User::updateOrCreate(['email' => 'supervisor@erp.local'], [
            'name' => 'Supervisor ERP',
            'nik' => '10000003',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $supervisorRoleId = Role::where('slug', 'supervisor')->value('id');
        DB::table('user_roles')->updateOrInsert([
            'user_id' => $supervisor->id,
            'role_id' => $supervisorRoleId,
        ], []);

        DB::table('settings')->updateOrInsert(
            ['key' => 'allow_over_receipt'],
            ['value' => '0', 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
