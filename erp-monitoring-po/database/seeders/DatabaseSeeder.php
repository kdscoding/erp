<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DocumentTermSeeder::class,
            MasterDataSeeder::class,
        ]);

        if (! app()->environment('production')) {
            $this->call([
                PurchaseOrderDemoSeeder::class,
                ShipmentDemoSeeder::class,
            ]);
        }
    }
}
