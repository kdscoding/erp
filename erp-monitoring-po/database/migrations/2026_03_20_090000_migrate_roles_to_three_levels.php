<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $targetRoles = [
            'administrator' => 'Administrator',
            'staff' => 'Staff',
            'supervisor' => 'Supervisor',
        ];

        foreach ($targetRoles as $slug => $name) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $slug],
                ['name' => $name, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $roleIds = DB::table('roles')->pluck('id', 'slug');
        $roleMappings = [
            'admin' => 'administrator',
            'administrator' => 'administrator',
            'purchasing' => 'staff',
            'warehouse' => 'staff',
            'staff' => 'staff',
            'purchasing_manager' => 'supervisor',
            'compliance' => 'supervisor',
            'viewer' => 'supervisor',
            'supervisor' => 'supervisor',
        ];

        $userRoleRows = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->select('user_roles.user_id', 'roles.slug')
            ->get();

        $userAssignments = [];
        $priority = ['administrator' => 3, 'staff' => 2, 'supervisor' => 1];

        foreach ($userRoleRows as $row) {
            $mappedSlug = $roleMappings[$row->slug] ?? null;
            if (!$mappedSlug) {
                continue;
            }

            $current = $userAssignments[$row->user_id] ?? null;
            if (!$current || $priority[$mappedSlug] > $priority[$current]) {
                $userAssignments[$row->user_id] = $mappedSlug;
            }
        }

        DB::table('user_roles')->truncate();

        foreach ($userAssignments as $userId => $slug) {
            if (!isset($roleIds[$slug])) {
                continue;
            }

            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleIds[$slug],
            ]);
        }

        DB::table('roles')->whereNotIn('slug', array_keys($targetRoles))->delete();
    }

    public function down(): void
    {
        // Tidak mengembalikan mapping role lama secara otomatis.
    }
};
