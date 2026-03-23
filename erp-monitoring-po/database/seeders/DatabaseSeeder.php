<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DocumentTermSeeder::class,
            MasterDataSeeder::class,
            PurchaseOrderDemoSeeder::class,
            ShipmentDemoSeeder::class,
            GoodsReceiptDemoSeeder::class,
        ]);
    }
}
