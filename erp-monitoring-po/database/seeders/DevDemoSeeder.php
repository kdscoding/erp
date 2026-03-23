<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevDemoSeeder extends Seeder
{
  public function run(): void
  {
    $this->call([
      PurchaseOrderDemoSeeder::class,
      ShipmentDemoSeeder::class,
      GoodsReceiptDemoSeeder::class,
    ]);
  }
}
