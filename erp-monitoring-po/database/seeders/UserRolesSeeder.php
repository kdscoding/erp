<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_roles')->insert([
            [
                'id' => 1,
                'user_id' => 6,
                'role_id' => 1,
            ],
            [
                'id' => 2,
                'user_id' => 7,
                'role_id' => 2,
            ],
            [
                'id' => 3,
                'user_id' => 8,
                'role_id' => 3,
            ],
        ]);
    }
}
