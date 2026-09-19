<?php

return [
    'entity' => [
        'singular' => 'Tracking',
        'plural' => 'Tracking',
        'title' => 'Fulfillment Tracking',
        'description' => 'Monitoring terpadu untuk Purchase Order — kode barang, supplier, dan sisa pesanan',
    ],

    'fields' => [
        'identifier' => [
            'label' => 'PO Number',
        ],
        'ref_type' => [
            'label' => 'Tipe Referensi',
        ],
        'ref_param' => [
            'label' => 'Parameter Referensi',
        ],
        'item_codes' => [
            'label' => 'Item Codes',
        ],
        'item_names' => [
            'label' => 'Nama Barang',
        ],
        'description' => [
            'label' => 'Tanggal',
        ],
        'supplier_name' => [
            'label' => 'Supplier',
        ],
        'ordered_qty' => [
            'label' => 'Ordered',
        ],
        'shipped_qty' => [
            'label' => 'Dikirim',
        ],
        'received_qty' => [
            'label' => 'Diterima',
        ],
        'outstanding_qty' => [
            'label' => 'Sisa',
        ],
        'stage' => [
            'label' => 'Status PO',
        ],
        'stage_class' => [
            'label' => 'Status Class',
        ],
        'stage_icon' => [
            'label' => 'Status Icon',
        ],
        'item_statuses' => [
            'label' => 'Status Barang',
        ],
    ],

    'table_columns' => [
        'index' => [
            'identifier' => 'PO Number',
            'item_codes' => 'Item Codes',
            'item_names' => 'Nama Barang',
            'description' => 'Tanggal',
            'supplier_name' => 'Supplier',
            'ordered_qty' => 'Ordered',
            'shipped_qty' => 'Dikirim',
            'received_qty' => 'Diterima',
            'outstanding_qty' => 'Sisa',
            'stage' => 'Status PO',
            'item_statuses' => 'Status Barang',
            'actions' => 'Aksi',
        ],
        'detail_item' => [
            'item_code' => 'Kode Item',
            'item_name' => 'Nama Item',
            'stage' => 'Status',
            'stage_class' => 'Status Class',
            'stage_icon' => 'Status Icon',
            'item_status' => 'Status Barang',
            'item_status_class' => 'Status Barang Class',
            'ordered_qty' => 'Ordered',
            'shipped_qty' => 'Dikirim',
            'received_qty' => 'Diterima',
            'outstanding_qty' => 'Sisa',
        ],
        'detail_shipment' => [
            'shipment_number' => 'Shipment',
            'shipment_date' => 'Tanggal',
            'delivery_note_number' => 'DN',
            'shipped_qty' => 'Shipped',
            'received_qty' => 'Received',
            'remaining_qty' => 'Sisa',
        ],
    ],

    'actions' => [
        'toggle_detail' => 'Detail',
        'view_po' => 'Lihat PO',
        'view_shipment' => 'Lihat Shipment',
    ],

    'filter_fields' => [
        'supplier_id' => [
            'label' => 'Supplier',
            'type' => 'select',
            'placeholder' => 'Semua Supplier',
        ],
        'date_from' => [
            'label' => 'Tanggal PO Dari',
            'type' => 'date',
        ],
        'date_to' => [
            'label' => 'Tanggal PO Sampai',
            'type' => 'date',
        ],
        'type' => [
            'label' => 'Tipe',
            'type' => 'select',
            'options' => [
                'all' => 'Semua',
                'po' => 'PO Saja',
                'barang' => 'Barang Saja',
            ],
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'select',
            'options' => [
                'all' => 'Semua',
                'PO Issued' => 'PO Issued',
                'Open' => 'Open',
                'Late' => 'Late',
                'Closed' => 'Closed',
                'Cancelled' => 'Cancelled',
                'Full' => 'Full',
                'Partial' => 'Partial',
                'Delayed' => 'Delayed',
            ],
        ],
    ],

    'search' => [
        'placeholder' => 'Cari PO, kode barang...',
        'aria_label' => 'Cari PO, kode barang',
    ],

    'stage_badges' => [
        'waiting' => ['class' => 'stage-waiting', 'icon' => 'fa-clock', 'label' => 'Waiting'],
        'confirmed' => ['class' => 'stage-confirmed', 'icon' => 'fa-check', 'label' => 'Confirmed'],
        'shipped' => ['class' => 'stage-shipped', 'icon' => 'fa-truck', 'label' => 'Shipped'],
        'partial' => ['class' => 'stage-partial', 'icon' => 'fa-box', 'label' => 'Partial'],
        'closed' => ['class' => 'stage-closed', 'icon' => 'fa-check-circle', 'label' => 'Closed'],
        'late' => ['class' => 'stage-late', 'icon' => 'fa-exclamation-triangle', 'label' => 'Late'],
        'cancelled' => ['class' => 'stage-cancelled', 'icon' => 'fa-times-circle', 'label' => 'Cancelled'],
    ],

    'item_status_badges' => [
        'waiting' => ['class' => 'stage-waiting', 'label' => 'Waiting'],
        'confirmed' => ['class' => 'stage-confirmed', 'label' => 'Confirmed'],
        'shipped' => ['class' => 'stage-shipped', 'label' => 'Shipped'],
        'partial' => ['class' => 'stage-partial', 'label' => 'Partial'],
        'closed' => ['class' => 'stage-closed', 'label' => 'Closed'],
        'late' => ['class' => 'stage-late', 'label' => 'Late'],
        'cancelled' => ['class' => 'stage-cancelled', 'label' => 'Cancelled'],
        'force_closed' => ['class' => 'stage-cancelled', 'label' => 'Force Closed'],
    ],
];