<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('units')->updateOrInsert(['unit_code' => 'PCS'], ['unit_name' => 'Pieces', 'updated_at' => now(), 'created_at' => now()]);
        DB::table('warehouses')->updateOrInsert(['warehouse_code' => 'WH-01'], ['warehouse_name' => 'Main Warehouse', 'location' => 'Plant A', 'updated_at' => now(), 'created_at' => now()]);
        DB::table('plants')->updateOrInsert(['plant_code' => 'PLT-A'], ['plant_name' => 'Plant A', 'updated_at' => now(), 'created_at' => now()]);

        for ($i=1; $i<=10; $i++) {
            DB::table('suppliers')->updateOrInsert(
                ['supplier_code' => sprintf('SUP%03d', $i)],
                ['supplier_name' => 'Supplier '.$i, 'status' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
