<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionBaselineSeeder extends Seeder
{
  public function run(): void
  {
    $this->call([
      RolePermissionSeeder::class,
      DocumentTermSeeder::class,
      MasterDataSeeder::class,
    ]);
  }
}
