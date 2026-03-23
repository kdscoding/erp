<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProductionBaselineSeeder::class);

        if (! app()->environment('production')) {
            $this->call(DevDemoSeeder::class);
        }
    }
}
