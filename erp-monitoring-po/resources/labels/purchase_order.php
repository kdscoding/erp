<?php

return [
    'entity' => [
        'singular' => 'Purchase Order',
        'plural' => 'Purchase Orders',
        'title' => 'Manajemen Purchase Order',
        'description' => 'Kelola dokumen Purchase Order dari pembuatan hingga penyelesaian',
    ],

    'fields' => [
        'po_number' => [
            'label' => 'Nomor PO',
            'placeholder' => 'Kosongkan untuk auto number',
            'help' => 'Nomor dokumen PO, otomatis tergenerate jika dikosongkan',
        ],
        'po_date' => [
            'label' => 'Tanggal PO',
            'placeholder' => 'Pilih tanggal',
            'help' => 'Tanggal pembuatan Purchase Order',
        ],
        'supplier_id' => [
            'label' => 'Supplier',
            'placeholder' => 'Pilih supplier',
            'help' => 'Supplier untuk Purchase Order ini',
        ],
        'supplier_code' => [
            'label' => 'Kode Supplier',
        ],
        'supplier_name' => [
            'label' => 'Nama Supplier',
        ],
        'eta_date' => [
            'label' => 'ETA',
            'placeholder' => 'Pilih tanggal ETA',
            'help' => 'Estimated Time of Arrival (tanggal kedatangan diperkirakan)',
        ],
        'notes' => [
            'label' => 'Catatan',
            'placeholder' => 'Catatan internal (opsional)',
            'help' => 'Catatan internal untuk tim procurement',
        ],
        'status' => [
            'label' => 'Status',
            'help' => 'Status header PO',
        ],
        'cancel_reason' => [
            'label' => 'Alasan Pembatalan',
            'placeholder' => 'Masukkan alasan pembatalan',
            'help' => 'Alasan mengapa PO dibatalkan',
        ],
        'force_close_reason' => [
            'label' => 'Alasan Force Close',
            'placeholder' => 'Masukkan alasan force close',
            'help' => 'Alasan memaksa menutup PO',
        ],
        'created_at' => [
            'label' => 'Dibuat',
        ],
        'updated_at' => [
            'label' => 'Terakhir Diubah',
        ],
    ],

    'table_columns' => [
        'index' => [
            'po_number' => 'PO',
            'supplier_code' => 'Supplier',
            'supplier_name' => 'Nama Supplier',
            'po_date' => 'Tanggal',
            'eta_date' => 'ETA',
            'status' => 'Status',
            'actions' => 'Aksi',
        ],
        'detail' => [
            'po_number' => 'Nomor PO',
            'po_date' => 'Tanggal PO',
            'supplier_name' => 'Supplier',
            'eta_date' => 'ETA',
            'status' => 'Status',
            'notes' => 'Catatan',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diubah Pada',
        ],
        'export' => [
            'po_number' => 'PO Number',
            'po_date' => 'PO Date',
            'supplier_code' => 'Supplier Code',
            'supplier_name' => 'Supplier Name',
            'eta_date' => 'ETA',
            'status' => 'Status',
            'notes' => 'Notes',
            'cancel_reason' => 'Cancel Reason',
        ],
    ],

    'item_table_columns' => [
        'index' => [
            'item_code' => 'Kode Item',
            'item_name' => 'Nama Item',
            'unit_name' => 'Unit',
            'quantity' => 'Qty',
            'unit_price' => 'Harga',
            'total_price' => 'Total',
            'etd_date' => 'ETD',
            'item_status' => 'Status',
            'actions' => 'Aksi',
        ],
        'export' => [
            'item_code' => 'Item Code',
            'item_name' => 'Item Name',
            'unit_name' => 'Unit',
            'quantity' => 'Ordered Qty',
            'unit_price' => 'Unit Price',
            'total_price' => 'Total Price',
            'etd_date' => 'ETD',
            'item_status' => 'Item Status',
            'cancel_reason' => 'Cancel Reason',
        ],
    ],

    'actions' => [
        'create' => 'Buat PO',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'view' => 'Detail',
        'export' => 'Export Excel',
        'import' => 'Import Excel',
        'template' => 'Template Import',
        'cancel' => 'Batalkan PO',
        'force_close' => 'Force Close',
        'bulk_etd' => 'Bulk Update ETD',
        'add_item' => 'Tambah Item',
    ],

    'validation' => [
        'po_number.unique' => 'Nomor PO sudah digunakan oleh PO lain.',
        'po_date.required' => 'Tanggal PO wajib diisi.',
        'po_date.date' => 'Tanggal PO tidak valid.',
        'supplier_id.required' => 'Supplier wajib dipilih.',
        'supplier_id.exists' => 'Supplier tidak valid.',
        'eta_date.date' => 'Tanggal ETA tidak valid.',
        'notes.max' => 'Catatan maksimal 500 karakter.',
        'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
        'force_close_reason.required' => 'Alasan force close wajib diisi.',
        'item_ids.required' => 'Pilih minimal satu item untuk bulk update ETD.',
        'item_ids.array' => 'Format item tidak valid.',
    ],

    'filter_fields' => [
        'po_number' => [
            'label' => 'Nomor PO',
            'placeholder' => 'Cari nomor PO',
            'type' => 'text',
        ],
        'supplier_code' => [
            'label' => 'Supplier',
            'type' => 'select',
            'placeholder' => 'Semua supplier',
        ],
        'date_from' => [
            'label' => 'Dari Tanggal',
            'type' => 'date',
        ],
        'date_to' => [
            'label' => 'Sampai Tanggal',
            'type' => 'date',
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'select',
            'placeholder' => 'Semua status',
            'dynamic_options' => 'po_status',
        ],
    ],
];