<?php

use Database\Seeders\DocumentTermSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\ShipmentSampleSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('erp:reset-demo', function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    foreach ([
        'attachments',
        'audit_logs',
        'goods_receipt_items',
        'goods_receipts',
        'shipment_items',
        'shipments',
        'po_status_histories',
        'purchase_order_items',
        'purchase_orders',
    ] as $table) {
        DB::table($table)->truncate();
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    $this->call(MasterDataSeeder::class);
    $this->call(DocumentTermSeeder::class);
    $this->call(ShipmentSampleSeeder::class);

    $this->info('Data transaksi/demo ERP berhasil dibersihkan dan dibuat ulang.');
})->purpose('Reset transaction/demo ERP data and regenerate sample records');
