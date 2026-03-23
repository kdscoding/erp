<?php

namespace Database\Seeders;

use App\Support\DocumentTermCodes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTermSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $terms = [
            // PO status
            [
                'group_key' => DocumentTermCodes::GROUP_PO_STATUS,
                'code' => DocumentTermCodes::PO_ISSUED,
                'label' => 'PO Issued / Waiting Progress',
                'badge_class' => 'bg-secondary',
                'badge_text' => 'text-white',
                'sort_order' => 10,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_STATUS,
                'code' => DocumentTermCodes::PO_OPEN,
                'label' => 'Open / In Progress',
                'badge_class' => 'bg-warning',
                'badge_text' => 'text-dark',
                'sort_order' => 20,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_STATUS,
                'code' => DocumentTermCodes::PO_LATE,
                'label' => 'Late / Need Follow Up',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 30,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_STATUS,
                'code' => DocumentTermCodes::PO_CLOSED,
                'label' => 'Completed',
                'badge_class' => 'bg-success',
                'badge_text' => 'text-white',
                'sort_order' => 40,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_STATUS,
                'code' => DocumentTermCodes::PO_CANCELLED,
                'label' => 'Cancelled',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 50,
            ],

            // PO item status
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_WAITING,
                'label' => 'Waiting Supplier Confirmation',
                'badge_class' => 'bg-secondary',
                'badge_text' => 'text-white',
                'sort_order' => 10,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_CONFIRMED,
                'label' => 'ETD Confirmed',
                'badge_class' => 'bg-warning',
                'badge_text' => 'text-dark',
                'sort_order' => 20,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_LATE,
                'label' => 'ETD Overdue',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 30,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_PARTIAL,
                'label' => 'Partially Received',
                'badge_class' => 'bg-primary',
                'badge_text' => 'text-white',
                'sort_order' => 40,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_CLOSED,
                'label' => 'Completed',
                'badge_class' => 'bg-success',
                'badge_text' => 'text-white',
                'sort_order' => 50,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_CANCELLED,
                'label' => 'Cancelled',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 60,
            ],

            // Shipment status
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_DRAFT,
                'label' => 'Draft',
                'badge_class' => 'bg-secondary',
                'badge_text' => 'text-white',
                'sort_order' => 10,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_SHIPPED,
                'label' => 'Sent / In Transit',
                'badge_class' => 'bg-info',
                'badge_text' => 'text-white',
                'sort_order' => 20,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
                'label' => 'Partially Received',
                'badge_class' => 'bg-primary',
                'badge_text' => 'text-white',
                'sort_order' => 30,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_RECEIVED,
                'label' => 'Completed',
                'badge_class' => 'bg-success',
                'badge_text' => 'text-white',
                'sort_order' => 40,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_CANCELLED,
                'label' => 'Cancelled',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 50,
            ],

            // Goods receipt status
            [
                'group_key' => DocumentTermCodes::GROUP_GOODS_RECEIPT_STATUS,
                'code' => DocumentTermCodes::GR_POSTED,
                'label' => 'Posted',
                'badge_class' => 'bg-info',
                'badge_text' => 'text-white',
                'sort_order' => 10,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_GOODS_RECEIPT_STATUS,
                'code' => DocumentTermCodes::GR_CANCELLED,
                'label' => 'Cancelled',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 20,
            ],

            // Notes
            [
                'group_key' => DocumentTermCodes::GROUP_PO_HISTORY_NOTE,
                'code' => DocumentTermCodes::NOTE_RELEASED_NEW_PO,
                'label' => 'Released new PO',
                'badge_class' => null,
                'badge_text' => null,
                'sort_order' => 10,
            ],
        ];

        foreach ($terms as $term) {
            DB::table('document_terms')->updateOrInsert(
                ['group_key' => $term['group_key'], 'code' => $term['code']],
                [
                    'label' => $term['label'],
                    'badge_class' => $term['badge_class'],
                    'badge_text' => $term['badge_text'],
                    'description' => $term['description'] ?? null,
                    'is_active' => true,
                    'sort_order' => $term['sort_order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
