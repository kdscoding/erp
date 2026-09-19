<?php

return [
    'po_status' => [
        'label' => 'Status PO',
        'options' => [
            'PO Issued' => 'PO Issued',
            'Open' => 'Open',
            'Late' => 'Late',
            'Closed' => 'Closed',
            'Cancelled' => 'Cancelled',
        ],
    ],

    'po_item_status' => [
        'label' => 'Status Item PO',
        'options' => [
            'Waiting' => 'Waiting',
            'Confirmed' => 'Confirmed',
            'Late' => 'Late',
            'Partial' => 'Partial',
            'Closed' => 'Closed',
            'Force Closed' => 'Force Closed',
            'Cancelled' => 'Cancelled',
        ],
    ],

    'shipment_status' => [
        'label' => 'Status Shipment',
        'options' => [
            'Draft' => 'Draft',
            'Shipped' => 'Shipped',
            'Partial Received' => 'Partial Received',
            'Received' => 'Received',
            'Cancelled' => 'Cancelled',
        ],
    ],

    'goods_receipt_status' => [
        'label' => 'Status Goods Receipt',
        'options' => [
            'Posted' => 'Posted',
            'Cancelled' => 'Cancelled',
        ],
    ],

    'active_inactive' => [
        'label' => 'Status Aktif',
        'options' => [
            1 => 'Aktif',
            0 => 'Nonaktif',
            true => 'Aktif',
            false => 'Nonaktif',
        ],
    ],

    'yes_no' => [
        'label' => 'Ya/Tidak',
        'options' => [
            1 => 'Ya',
            0 => 'Tidak',
            true => 'Ya',
            false => 'Tidak',
        ],
    ],
];