<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
            SettingsSeeder::class,
            SuppliersSeeder::class,
            UserRolesSeeder::class,
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
