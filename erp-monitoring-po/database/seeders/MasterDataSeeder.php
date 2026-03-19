<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('units')->updateOrInsert(['unit_code' => 'PCS'], ['unit_name' => 'Pieces', 'updated_at' => $now, 'created_at' => $now]);
        DB::table('units')->updateOrInsert(['unit_code' => 'ROLL'], ['unit_name' => 'Roll', 'updated_at' => $now, 'created_at' => $now]);

        DB::table('warehouses')->updateOrInsert(['warehouse_code' => 'WH-01'], ['warehouse_name' => 'Main Warehouse', 'location' => 'Plant A', 'updated_at' => $now, 'created_at' => $now]);
        DB::table('warehouses')->updateOrInsert(['warehouse_code' => 'WH-02'], ['warehouse_name' => 'Secondary Warehouse', 'location' => 'Plant B', 'updated_at' => $now, 'created_at' => $now]);
        DB::table('plants')->updateOrInsert(['plant_code' => 'PLT-A'], ['plant_name' => 'Plant A', 'updated_at' => $now, 'created_at' => $now]);
        DB::table('plants')->updateOrInsert(['plant_code' => 'PLT-B'], ['plant_name' => 'Plant B', 'updated_at' => $now, 'created_at' => $now]);

        for ($i = 1; $i <= 10; $i++) {
            DB::table('suppliers')->updateOrInsert(
                ['supplier_code' => sprintf('SUP%03d', $i)],
                [
                    'supplier_name' => 'Supplier '.$i,
                    'address' => 'Kawasan Industri Blok '.$i,
                    'phone' => '021-555'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'email' => 'supplier'.$i.'@example.com',
                    'contact_person' => 'PIC Supplier '.$i,
                    'status' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $unitId = DB::table('units')->where('unit_code', 'PCS')->value('id');
        for ($i = 1; $i <= 50; $i++) {
            DB::table('items')->updateOrInsert(
                ['item_code' => sprintf('LBL-%04d', $i)],
                [
                    'item_name' => 'Material Label '.$i,
                    'category' => $i % 2 === 0 ? 'Thermal' : 'Sticker',
                    'specification' => 'Spec label ukuran '.$i.'x'.$i.' mm',
                    'unit_id' => $unitId,
                    'active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
