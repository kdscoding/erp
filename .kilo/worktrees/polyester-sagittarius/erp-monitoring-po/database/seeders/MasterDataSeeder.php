<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('units')->updateOrInsert(
            ['unit_code' => 'PCS'],
            ['unit_name' => 'Pieces', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('units')->updateOrInsert(
            ['unit_code' => 'ROLL'],
            ['unit_name' => 'Roll', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('units')->updateOrInsert(
            ['unit_code' => 'SET'],
            ['unit_name' => 'Set', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('item_categories')->updateOrInsert(
            ['category_code' => 'CAT-PRIM'],
            [
                'category_name' => 'Primary Label',
                'description' => 'Label untuk kemasan primer',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('item_categories')->updateOrInsert(
            ['category_code' => 'CAT-SECO'],
            [
                'category_name' => 'Secondary Label',
                'description' => 'Label untuk karton / outer box',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('item_categories')->updateOrInsert(
            ['category_code' => 'CAT-CONS'],
            [
                'category_name' => 'Consumable Printing',
                'description' => 'Ribbon dan consumable printer',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('plants')->updateOrInsert(
            ['plant_code' => 'PLT-JKT'],
            ['plant_name' => 'Plant Jakarta', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('plants')->updateOrInsert(
            ['plant_code' => 'PLT-BDG'],
            ['plant_name' => 'Plant Bandung', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('warehouses')->updateOrInsert(
            ['warehouse_code' => 'WH-RM'],
            [
                'warehouse_name' => 'Raw Material Warehouse',
                'location' => 'Plant Jakarta',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('warehouses')->updateOrInsert(
            ['warehouse_code' => 'WH-PKG'],
            [
                'warehouse_name' => 'Packaging Warehouse',
                'location' => 'Plant Bandung',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $suppliers = [
            ['supplier_code' => 'SUP-LBL-001', 'supplier_name' => 'PT Labelindo Utama', 'city' => 'Bekasi'],
            ['supplier_code' => 'SUP-LBL-002', 'supplier_name' => 'PT Sinar Barcode Nusantara', 'city' => 'Tangerang'],
            ['supplier_code' => 'SUP-LBL-003', 'supplier_name' => 'PT Printpack Solusi', 'city' => 'Karawang'],
            ['supplier_code' => 'SUP-LBL-004', 'supplier_name' => 'PT Global Thermal Media', 'city' => 'Jakarta'],
            ['supplier_code' => 'SUP-LBL-005', 'supplier_name' => 'PT Citra Ribbon Indonesia', 'city' => 'Bogor'],
            ['supplier_code' => 'SUP-LBL-006', 'supplier_name' => 'PT Kemas Label Persada', 'city' => 'Semarang'],
        ];

        foreach ($suppliers as $index => $supplier) {
            DB::table('suppliers')->updateOrInsert(
                ['supplier_code' => $supplier['supplier_code']],
                [
                    'supplier_name' => $supplier['supplier_name'],
                    'address' => 'Kawasan Industri ' . $supplier['city'] . ' Blok ' . chr(65 + $index),
                    'phone' => '021-77' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'email' => 'sales' . ($index + 1) . '@demo-supplier.co.id',
                    'contact_person' => 'PIC ' . ($index + 1),
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $unitPcsId = DB::table('units')->where('unit_code', 'PCS')->value('id');
        $unitRollId = DB::table('units')->where('unit_code', 'ROLL')->value('id');

        $primaryCategoryId = DB::table('item_categories')->where('category_code', 'CAT-PRIM')->value('id');
        $secondaryCategoryId = DB::table('item_categories')->where('category_code', 'CAT-SECO')->value('id');
        $consumableCategoryId = DB::table('item_categories')->where('category_code', 'CAT-CONS')->value('id');

        $items = [
            ['item_code' => 'LBL-PRIM-001', 'item_name' => 'Label Botol Sirup 60 ml', 'category_id' => $primaryCategoryId, 'specification' => 'Glossy white 45 x 20 mm', 'unit_id' => $unitPcsId],
            ['item_code' => 'LBL-PRIM-002', 'item_name' => 'Label Sachet Powder 20 gr', 'category_id' => $primaryCategoryId, 'specification' => 'BOPP matte 50 x 25 mm', 'unit_id' => $unitPcsId],
            ['item_code' => 'LBL-PRIM-003', 'item_name' => 'Label Tube Gel 30 gr', 'category_id' => $primaryCategoryId, 'specification' => 'PE white 55 x 30 mm', 'unit_id' => $unitPcsId],
            ['item_code' => 'LBL-SECO-001', 'item_name' => 'Label Karton FG A', 'category_id' => $secondaryCategoryId, 'specification' => 'Thermal semi coating 100 x 75 mm', 'unit_id' => $unitPcsId],
            ['item_code' => 'LBL-SECO-002', 'item_name' => 'Label Karton FG B', 'category_id' => $secondaryCategoryId, 'specification' => 'Thermal transfer 102 x 76 mm', 'unit_id' => $unitPcsId],
            ['item_code' => 'LBL-SECO-003', 'item_name' => 'Label Pallet Export', 'category_id' => $secondaryCategoryId, 'specification' => 'Matte paper 148 x 100 mm', 'unit_id' => $unitPcsId],
            ['item_code' => 'RBN-110-300', 'item_name' => 'Ribbon Wax Resin 110mm x 300m', 'category_id' => $consumableCategoryId, 'specification' => 'Black premium grade', 'unit_id' => $unitRollId],
            ['item_code' => 'RBN-084-300', 'item_name' => 'Ribbon Wax Resin 84mm x 300m', 'category_id' => $consumableCategoryId, 'specification' => 'Black standard grade', 'unit_id' => $unitRollId],
            ['item_code' => 'RBN-060-250', 'item_name' => 'Ribbon Thermal 60mm x 250m', 'category_id' => $consumableCategoryId, 'specification' => 'Near edge compatible', 'unit_id' => $unitRollId],
            ['item_code' => 'LBL-PRIM-004', 'item_name' => 'Label Jar 100 ml', 'category_id' => $primaryCategoryId, 'specification' => 'Clear transparent 60 x 35 mm', 'unit_id' => $unitPcsId],
            ['item_code' => 'LBL-SECO-004', 'item_name' => 'Shipping Label Domestic', 'category_id' => $secondaryCategoryId, 'specification' => 'Direct thermal 100 x 150 mm', 'unit_id' => $unitPcsId],
            ['item_code' => 'LBL-PRIM-005', 'item_name' => 'Label Foil Premium', 'category_id' => $primaryCategoryId, 'specification' => 'Silver foil 40 x 18 mm', 'unit_id' => $unitPcsId],
        ];

        foreach ($items as $item) {
            DB::table('items')->updateOrInsert(
                ['item_code' => $item['item_code']],
                [
                    'item_name' => $item['item_name'],
                    'category_id' => $item['category_id'],
                    'specification' => $item['specification'],
                    'unit_id' => $item['unit_id'],
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
