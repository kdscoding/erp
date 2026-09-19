<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'Administrator',
                'slug' => 'administrator',
                'created_at' => '2026-09-18 23:33:25',
                'updated_at' => '2026-09-18 23:33:25',
            ],
            [
                'id' => 2,
                'name' => 'Staff',
                'slug' => 'staff',
                'created_at' => '2026-09-18 23:33:25',
                'updated_at' => '2026-09-18 23:33:25',
            ],
            [
                'id' => 3,
                'name' => 'Supervisor',
                'slug' => 'supervisor',
                'created_at' => '2026-09-18 23:33:25',
                'updated_at' => '2026-09-18 23:33:25',
            ],
        ]);
    }
}
