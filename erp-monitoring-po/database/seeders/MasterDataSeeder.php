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
        DB::table('units')->updateOrInsert(['unit_code' => 'SET'], ['unit_name' => 'Set', 'updated_at' => $now, 'created_at' => $now]);

        DB::table('item_categories')->updateOrInsert(
            ['category_code' => 'CAT-PRIM'],
            ['category_name' => 'Primary Label', 'description' => 'Label untuk kemasan primer.', 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );
        DB::table('item_categories')->updateOrInsert(
            ['category_code' => 'CAT-SECO'],
            ['category_name' => 'Secondary Label', 'description' => 'Label untuk karton / outer box.', 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );
        DB::table('item_categories')->updateOrInsert(
            ['category_code' => 'CAT-CONS'],
            ['category_name' => 'Consumable Printing', 'description' => 'Ribbon dan consumable printer.', 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('warehouses')->updateOrInsert(['warehouse_code' => 'WH-RM'], ['warehouse_name' => 'Raw Material Warehouse', 'location' => 'Plant A', 'updated_at' => $now, 'created_at' => $now]);
        DB::table('warehouses')->updateOrInsert(['warehouse_code' => 'WH-PKG'], ['warehouse_name' => 'Packaging Warehouse', 'location' => 'Plant B', 'updated_at' => $now, 'created_at' => $now]);

        DB::table('plants')->updateOrInsert(['plant_code' => 'PLT-JKT'], ['plant_name' => 'Plant Jakarta', 'updated_at' => $now, 'created_at' => $now]);
        DB::table('plants')->updateOrInsert(['plant_code' => 'PLT-BDG'], ['plant_name' => 'Plant Bandung', 'updated_at' => $now, 'created_at' => $now]);

        $suppliers = [
            ['code' => 'SUP-LBL-001', 'name' => 'PT Labelindo Utama', 'city' => 'Bekasi'],
            ['code' => 'SUP-LBL-002', 'name' => 'PT Sinar Barcode Nusantara', 'city' => 'Tangerang'],
            ['code' => 'SUP-LBL-003', 'name' => 'PT Printpack Solusi', 'city' => 'Karawang'],
            ['code' => 'SUP-LBL-004', 'name' => 'PT Global Thermal Media', 'city' => 'Jakarta'],
            ['code' => 'SUP-LBL-005', 'name' => 'PT Citra Ribbon Indonesia', 'city' => 'Bogor'],
            ['code' => 'SUP-LBL-006', 'name' => 'PT Kemas Label Persada', 'city' => 'Semarang'],
        ];

        foreach ($suppliers as $index => $supplier) {
            DB::table('suppliers')->updateOrInsert(
                ['supplier_code' => $supplier['code']],
                [
                    'supplier_name' => $supplier['name'],
                    'address' => 'Kawasan Industri ' . $supplier['city'] . ' Blok ' . chr(65 + $index),
                    'phone' => '021-77' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'email' => 'sales' . ($index + 1) . '@' . str_replace(' ', '', strtolower($supplier['name'])) . '.co.id',
                    'contact_person' => 'PIC ' . ($index + 1),
                    'status' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $unitPcsId = DB::table('units')->where('unit_code', 'PCS')->value('id');
        $unitRollId = DB::table('units')->where('unit_code', 'ROLL')->value('id');

        $primaryCategoryId = DB::table('item_categories')->where('category_code', 'CAT-PRIM')->value('id');
        $secondaryCategoryId = DB::table('item_categories')->where('category_code', 'CAT-SECO')->value('id');
        $consumableCategoryId = DB::table('item_categories')->where('category_code', 'CAT-CONS')->value('id');

        $items = [
            ['code' => 'LBL-PRIM-001', 'name' => 'Label Botol Sirup 60 ml', 'category_id' => $primaryCategoryId, 'spec' => 'Glossy white 45 x 20 mm', 'unit_id' => $unitPcsId],
            ['code' => 'LBL-PRIM-002', 'name' => 'Label Sachet Powder 20 gr', 'category_id' => $primaryCategoryId, 'spec' => 'BOPP matte 50 x 25 mm', 'unit_id' => $unitPcsId],
            ['code' => 'LBL-PRIM-003', 'name' => 'Label Tube Gel 30 gr', 'category_id' => $primaryCategoryId, 'spec' => 'PE white 55 x 30 mm', 'unit_id' => $unitPcsId],
            ['code' => 'LBL-SECO-001', 'name' => 'Label Karton FG A', 'category_id' => $secondaryCategoryId, 'spec' => 'Thermal semi coating 100 x 75 mm', 'unit_id' => $unitPcsId],
            ['code' => 'LBL-SECO-002', 'name' => 'Label Karton FG B', 'category_id' => $secondaryCategoryId, 'spec' => 'Thermal transfer 102 x 76 mm', 'unit_id' => $unitPcsId],
            ['code' => 'LBL-SECO-003', 'name' => 'Label Pallet Export', 'category_id' => $secondaryCategoryId, 'spec' => 'Matte paper 148 x 100 mm', 'unit_id' => $unitPcsId],
            ['code' => 'RBN-110-300', 'name' => 'Ribbon Wax Resin 110mm x 300m', 'category_id' => $consumableCategoryId, 'spec' => 'Black premium grade', 'unit_id' => $unitRollId],
            ['code' => 'RBN-084-300', 'name' => 'Ribbon Wax Resin 84mm x 300m', 'category_id' => $consumableCategoryId, 'spec' => 'Black standard grade', 'unit_id' => $unitRollId],
            ['code' => 'RBN-060-250', 'name' => 'Ribbon Thermal 60mm x 250m', 'category_id' => $consumableCategoryId, 'spec' => 'Near edge compatible', 'unit_id' => $unitRollId],
            ['code' => 'LBL-PRIM-004', 'name' => 'Label Jar 100 ml', 'category_id' => $primaryCategoryId, 'spec' => 'Clear transparent 60 x 35 mm', 'unit_id' => $unitPcsId],
            ['code' => 'LBL-SECO-004', 'name' => 'Shipping Label Domestic', 'category_id' => $secondaryCategoryId, 'spec' => 'Direct thermal 100 x 150 mm', 'unit_id' => $unitPcsId],
            ['code' => 'LBL-PRIM-005', 'name' => 'Label Foil Premium', 'category_id' => $primaryCategoryId, 'spec' => 'Silver foil 40 x 18 mm', 'unit_id' => $unitPcsId],
        ];

        foreach ($items as $item) {
            DB::table('items')->updateOrInsert(
                ['item_code' => $item['code']],
                [
                    'item_name' => $item['name'],
                    'category_id' => $item['category_id'],
                    'specification' => $item['spec'],
                    'unit_id' => $item['unit_id'],
                    'active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
