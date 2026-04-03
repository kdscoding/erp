<?php

namespace Database\Seeders;

use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
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
                'internal_code' => DomainStatus::PO_ISSUED,
                'label' => 'PO Issued / Waiting Progress',
                'badge_class' => 'bg-secondary',
                'badge_text' => 'text-white',
                'sort_order' => 10,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_STATUS,
                'code' => DocumentTermCodes::PO_OPEN,
                'internal_code' => DomainStatus::PO_OPEN,
                'label' => 'Open / In Progress',
                'badge_class' => 'bg-warning',
                'badge_text' => 'text-dark',
                'sort_order' => 20,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_STATUS,
                'code' => DocumentTermCodes::PO_LATE,
                'internal_code' => DomainStatus::PO_LATE,
                'label' => 'Late / Need Follow Up',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 30,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_STATUS,
                'code' => DocumentTermCodes::PO_CLOSED,
                'internal_code' => DomainStatus::PO_CLOSED,
                'label' => 'Completed',
                'badge_class' => 'bg-success',
                'badge_text' => 'text-white',
                'sort_order' => 40,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_STATUS,
                'code' => DocumentTermCodes::PO_CANCELLED,
                'internal_code' => DomainStatus::PO_CANCELLED,
                'label' => 'Cancelled',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 50,
            ],

            // PO item status
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_WAITING,
                'internal_code' => DomainStatus::ITEM_WAITING,
                'label' => 'Waiting Supplier Confirmation',
                'badge_class' => 'bg-secondary',
                'badge_text' => 'text-white',
                'sort_order' => 10,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_CONFIRMED,
                'internal_code' => DomainStatus::ITEM_CONFIRMED,
                'label' => 'ETD Confirmed',
                'badge_class' => 'bg-warning',
                'badge_text' => 'text-dark',
                'sort_order' => 20,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_LATE,
                'internal_code' => DomainStatus::ITEM_LATE,
                'label' => 'ETD Overdue',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 30,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_PARTIAL,
                'internal_code' => DomainStatus::ITEM_PARTIAL,
                'label' => 'Partially Received',
                'badge_class' => 'bg-primary',
                'badge_text' => 'text-white',
                'sort_order' => 40,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_CLOSED,
                'internal_code' => DomainStatus::ITEM_CLOSED,
                'label' => 'Completed',
                'badge_class' => 'bg-success',
                'badge_text' => 'text-white',
                'sort_order' => 50,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_FORCE_CLOSED,
                'internal_code' => DomainStatus::ITEM_FORCE_CLOSED,
                'label' => 'Force Closed',
                'badge_class' => 'bg-dark',
                'badge_text' => 'text-white',
                'sort_order' => 55,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_PO_ITEM_STATUS,
                'code' => DocumentTermCodes::ITEM_CANCELLED,
                'internal_code' => DomainStatus::ITEM_CANCELLED,
                'label' => 'Cancelled',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 60,
            ],

            // Shipment status
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_DRAFT,
                'internal_code' => DomainStatus::SHIPMENT_DRAFT,
                'label' => 'Draft',
                'badge_class' => 'bg-secondary',
                'badge_text' => 'text-white',
                'sort_order' => 10,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_SHIPPED,
                'internal_code' => DomainStatus::SHIPMENT_SHIPPED,
                'label' => 'Sent / In Transit',
                'badge_class' => 'bg-info',
                'badge_text' => 'text-white',
                'sort_order' => 20,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
                'internal_code' => DomainStatus::SHIPMENT_PARTIAL_RECEIVED,
                'label' => 'Partially Received',
                'badge_class' => 'bg-primary',
                'badge_text' => 'text-white',
                'sort_order' => 30,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_RECEIVED,
                'internal_code' => DomainStatus::SHIPMENT_RECEIVED,
                'label' => 'Completed',
                'badge_class' => 'bg-success',
                'badge_text' => 'text-white',
                'sort_order' => 40,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_SHIPMENT_STATUS,
                'code' => DocumentTermCodes::SHIPMENT_CANCELLED,
                'internal_code' => DomainStatus::SHIPMENT_CANCELLED,
                'label' => 'Cancelled',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 50,
            ],

            // GR status
            [
                'group_key' => DocumentTermCodes::GROUP_GOODS_RECEIPT_STATUS,
                'code' => DocumentTermCodes::GR_POSTED,
                'internal_code' => DomainStatus::GR_POSTED,
                'label' => 'Posted',
                'badge_class' => 'bg-info',
                'badge_text' => 'text-white',
                'sort_order' => 10,
            ],
            [
                'group_key' => DocumentTermCodes::GROUP_GOODS_RECEIPT_STATUS,
                'code' => DocumentTermCodes::GR_CANCELLED,
                'internal_code' => DomainStatus::GR_CANCELLED,
                'label' => 'Cancelled',
                'badge_class' => 'bg-danger',
                'badge_text' => 'text-white',
                'sort_order' => 20,
            ],

            // Notes
            [
                'group_key' => DocumentTermCodes::GROUP_PO_HISTORY_NOTE,
                'code' => DocumentTermCodes::NOTE_RELEASED_NEW_PO,
                'internal_code' => null,
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
                    'internal_code' => $term['internal_code'] ?? null,
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
