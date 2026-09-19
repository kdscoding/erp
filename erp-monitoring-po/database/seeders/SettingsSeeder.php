<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([
            [
                'id' => 1,
                'key' => 'allow_over_receipt',
                'value' => '0',
                'created_at' => '2026-09-18 23:33:26',
                'updated_at' => '2026-09-18 23:33:26',
            ],
        ]);
    }
}
