<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'id' => 1,
                'supplier_code' => 'DMT',
                'supplier_name' => 'CV. DUTA MEDIA TURINDO',
                'address' => 'Kawasan Industri Bekasi Blok A',
                'phone' => '021-770001',
                'email' => 'sales1@demo-supplier.co.id',
                'contact_person' => 'PIC 1',
                'status' => 1,
                'created_at' => '2026-09-18 19:12:25',
                'updated_at' => '2026-09-18 23:07:05',
            ],
            [
                'id' => 2,
                'supplier_code' => 'PAXAR',
                'supplier_name' => 'PT. PAXAR INDONESIA',
                'address' => 'Kawasan Industri Tangerang Blok B',
                'phone' => '021-770002',
                'email' => 'sales2@demo-supplier.co.id',
                'contact_person' => 'PIC 2',
                'status' => 1,
                'created_at' => '2026-09-18 19:12:25',
                'updated_at' => '2026-09-18 23:07:18',
            ],
            [
                'id' => 3,
                'supplier_code' => 'AVERY',
                'supplier_name' => 'PT. AVERY INDONESIA',
                'address' => 'Kawasan Industri Karawang Blok C',
                'phone' => '021-770003',
                'email' => 'sales3@demo-supplier.co.id',
                'contact_person' => 'PIC 3',
                'status' => 1,
                'created_at' => '2026-09-18 19:12:25',
                'updated_at' => '2026-09-18 23:59:09',
            ],
        ]);
    }
}
