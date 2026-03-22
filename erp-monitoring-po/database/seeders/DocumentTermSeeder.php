<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTermSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $terms = [
            ['group_key' => 'po_status', 'code' => 'PO Issued', 'label' => 'Released New PO', 'sort_order' => 10],
            ['group_key' => 'po_status', 'code' => 'Open', 'label' => 'Open / In Progress', 'sort_order' => 20],
            ['group_key' => 'po_status', 'code' => 'Late', 'label' => 'Late / Need Follow Up', 'sort_order' => 30],
            ['group_key' => 'po_status', 'code' => 'Closed', 'label' => 'Completed', 'sort_order' => 40],
            ['group_key' => 'po_status', 'code' => 'Cancelled', 'label' => 'Cancelled', 'sort_order' => 50],

            ['group_key' => 'po_item_status', 'code' => 'Waiting', 'label' => 'Waiting Supplier Confirmation', 'sort_order' => 10],
            ['group_key' => 'po_item_status', 'code' => 'Confirmed', 'label' => 'ETD Confirmed', 'sort_order' => 20],
            ['group_key' => 'po_item_status', 'code' => 'Late', 'label' => 'ETD Overdue', 'sort_order' => 30],
            ['group_key' => 'po_item_status', 'code' => 'Partial', 'label' => 'Partially Received', 'sort_order' => 40],
            ['group_key' => 'po_item_status', 'code' => 'Closed', 'label' => 'Completed', 'sort_order' => 50],
            ['group_key' => 'po_item_status', 'code' => 'Cancelled', 'label' => 'Cancelled', 'sort_order' => 60],

            ['group_key' => 'shipment_status', 'code' => 'Draft', 'label' => 'Draft', 'sort_order' => 10],
            ['group_key' => 'shipment_status', 'code' => 'Shipped', 'label' => 'Sent / In Transit', 'sort_order' => 20],
            ['group_key' => 'shipment_status', 'code' => 'Partial Received', 'label' => 'Partially Received', 'sort_order' => 30],
            ['group_key' => 'shipment_status', 'code' => 'Received', 'label' => 'Completed', 'sort_order' => 40],
            ['group_key' => 'shipment_status', 'code' => 'Cancelled', 'label' => 'Cancelled', 'sort_order' => 50],

            ['group_key' => 'goods_receipt_status', 'code' => 'Posted', 'label' => 'Posted', 'sort_order' => 10],
            ['group_key' => 'goods_receipt_status', 'code' => 'Cancelled', 'label' => 'Cancelled', 'sort_order' => 20],

            ['group_key' => 'po_history_note', 'code' => 'released_new_po', 'label' => 'Released new PO', 'sort_order' => 10],
        ];

        foreach ($terms as $term) {
            DB::table('document_terms')->updateOrInsert(
                ['group_key' => $term['group_key'], 'code' => $term['code']],
                [
                    'label' => $term['label'],
                    'description' => $term['description'] ?? null,
                    'is_active' => true,
                    'sort_order' => $term['sort_order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        DB::table('document_terms')
            ->where('group_key', 'po_status')
            ->whereNotIn('code', ['PO Issued', 'Open', 'Late', 'Closed', 'Cancelled'])
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);
    }
}
