<?php

return [
    'entity' => [
        'singular' => 'Shipment',
        'plural' => 'Shipments',
        'title' => 'Manajemen Shipment (Pengiriman)',
        'description' => 'Kelola dokumen pengiriman barang dari supplier (Delivery Note, Invoice, Receiving)',
    ],

    'fields' => [
        'shipment_number' => [
            'label' => 'Nomor Shipment',
            'placeholder' => 'Otomatis tergenerate',
            'help' => 'Nomor dokumen shipment (auto-generated)',
        ],
        'shipment_date' => [
            'label' => 'Tanggal Shipment',
            'placeholder' => 'Pilih tanggal',
            'help' => 'Tanggal pengiriman barang',
        ],
        'supplier_id' => [
            'label' => 'Supplier',
            'placeholder' => 'Pilih supplier',
            'help' => 'Supplier pengirim barang',
        ],
        'supplier_name' => [
            'label' => 'Nama Supplier',
        ],
        'delivery_note_number' => [
            'label' => 'No. Delivery Note',
            'placeholder' => 'No surat jalan supplier',
            'help' => 'Nomor surat jalan/delivery note dari supplier',
        ],
        'invoice_number' => [
            'label' => 'No. Invoice',
            'placeholder' => 'No invoice supplier',
            'help' => 'Nomor invoice dari supplier (opsional)',
        ],
        'invoice_date' => [
            'label' => 'Tgl Invoice',
            'placeholder' => 'Pilih tanggal',
            'help' => 'Tanggal invoice supplier',
        ],
        'invoice_currency' => [
            'label' => 'Mata Uang',
            'placeholder' => 'IDR',
            'help' => 'Mata uang invoice (default: IDR)',
        ],
        'supplier_remark' => [
            'label' => 'Catatan Supplier',
            'placeholder' => 'Catatan internal atau info tambahan',
            'help' => 'Catatan tambahan dari atau untuk supplier',
        ],
        'status' => [
            'label' => 'Status',
            'help' => 'Status dokumen shipment',
        ],
        'po_numbers' => [
            'label' => 'Nomor PO',
        ],
        'po_count' => [
            'label' => 'Jumlah PO',
        ],
        'line_count' => [
            'label' => 'Jumlah Line',
        ],
        'total_shipped_qty' => [
            'label' => 'Total Dikirim',
        ],
        'total_received_qty' => [
            'label' => 'Total Diterima',
        ],
        'total_open_qty' => [
            'label' => 'Sisa',
        ],
        'created_at' => [
            'label' => 'Dibuat',
        ],
        'updated_at' => [
            'label' => 'Terakhir Diubah',
        ],
    ],

    'table_columns' => [
        'worklist' => [
            'select' => '',
            'shipment_number' => 'Shipment',
            'supplier_name' => 'Supplier',
            'po_numbers' => 'PO',
            'delivery_note_number' => 'Delivery Note',
            'invoice_number' => 'Invoice',
            'status' => 'Status',
            'progress' => 'Progress',
            'actions' => 'Aksi',
        ],
        'archive' => [
            'shipment_number' => 'Shipment',
            'supplier_name' => 'Supplier',
            'po_numbers' => 'PO',
            'delivery_note_number' => 'DN',
            'invoice_number' => 'Invoice',
            'status' => 'Status',
            'progress' => 'Progress',
            'actions' => 'Aksi',
        ],
        'detail' => [
            'shipment_number' => 'Nomor Shipment',
            'shipment_date' => 'Tanggal Shipment',
            'supplier_name' => 'Supplier',
            'delivery_note_number' => 'Delivery Note',
            'invoice_number' => 'Invoice',
            'invoice_date' => 'Tgl Invoice',
            'invoice_currency' => 'Currency',
            'supplier_remark' => 'Catatan',
            'status' => 'Status',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diubah Pada',
        ],
        'item_list' => [
            'select' => '',
            'po_number' => 'PO',
            'item_code' => 'Item',
            'item_name' => 'Nama Item',
            'po_unit_price' => 'Harga PO',
            'available_to_ship_qty' => 'Sisa Kirim',
            'shipped_qty' => 'Qty Draft',
            'invoice_unit_price' => 'Harga Invoice',
            'line_total' => 'Total',
            'actions' => 'Aksi',
        ],
        'candidate' => [
            'select' => '',
            'supplier_name' => 'Supplier',
            'po_number' => 'PO',
            'item_code' => 'Item',
            'item_name' => 'Nama Item',
            'unit_price' => 'Harga PO',
            'outstanding_qty' => 'Outstanding',
            'open_shipment_qty' => 'Dialokasikan',
            'available_to_ship_qty' => 'Sisa Kirim',
            'etd_date' => 'ETD',
        ],
    ],

    'actions' => [
        'create' => '+ New Draft',
        'edit' => 'Edit',
        'view' => 'View',
        'export' => 'Export',
        'import' => 'Import',
        'template' => 'Template',
        'mark_shipped' => 'Mark Shipped',
        'receive' => 'Receive',
        'cancel' => 'Cancel',
        'clear_selection' => 'Clear Selection',
        'add_to_draft' => 'Tambahkan ke Draft',
        'remove_draft' => 'Hapus',
        'reset_builder' => 'Reset Builder',
        'save_draft' => 'Simpan Draft Shipment',
    ],

    'validation' => [
        'shipment_date.required' => 'Tanggal shipment wajib diisi.',
        'shipment_date.date' => 'Tanggal shipment tidak valid.',
        'delivery_note_number.required' => 'No. Delivery Note wajib diisi.',
        'supplier_id.required' => 'Supplier wajib dipilih.',
        'supplier_id.exists' => 'Supplier tidak valid.',
        'invoice_currency.max' => 'Kode mata uang maksimal 10 karakter.',
        'selected_items.required' => 'Pilih minimal satu item dari tabel kandidat.',
        'shipped_qty.required' => 'Qty draft wajib diisi.',
        'shipped_qty.numeric' => 'Qty draft harus berupa angka.',
        'shipped_qty.min' => 'Qty draft minimal 0.01.',
        'shipped_qty.*.required' => 'Qty kirim wajib diisi untuk setiap item.',
        'shipped_qty.*.numeric' => 'Qty kirim harus berupa angka.',
        'shipped_qty.*.min' => 'Qty kirim minimal 0.01.',
        'invoice_unit_price.numeric' => 'Harga invoice harus berupa angka.',
        'invoice_unit_price.min' => 'Harga invoice minimal 0.',
        'invoice_unit_price.*.numeric' => 'Harga invoice harus berupa angka.',
        'invoice_unit_price.*.min' => 'Harga invoice tidak boleh negatif.',
        'item.active' => 'Item tidak ditemukan atau tidak aktif.',
        'po.status' => 'PO tidak dalam status yang dapat dikirim.',
        'price.nonnegative' => 'Harga invoice tidak boleh negatif.',
    ],

    'filter_fields' => [
        'worklist' => [
            'supplier_id' => [
                'label' => 'Supplier',
                'type' => 'select',
                'placeholder' => 'Semua Supplier',
            ],
            'delivery_note_number' => [
                'label' => 'Delivery Note',
                'placeholder' => 'No surat jalan',
                'type' => 'text',
            ],
            'invoice_number' => [
                'label' => 'Invoice',
                'placeholder' => 'No invoice',
                'type' => 'text',
            ],
            'keyword' => [
                'label' => 'Keyword',
                'placeholder' => 'Shipment / PO / supplier',
                'type' => 'text',
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'placeholder' => 'Semua Status',
                'dynamic_options' => 'shipment_status',
            ],
        ],
        'create' => [
            'supplier_id' => [
                'label' => 'Supplier',
                'type' => 'select',
                'placeholder' => 'Semua Supplier',
            ],
            'keyword' => [
                'label' => 'Cari Item / PO / Supplier',
                'placeholder' => 'Item code, nama item, nomor PO, nama supplier',
                'type' => 'text',
            ],
        ],
        'archive' => [
            'supplier_id' => [
                'label' => 'Supplier',
                'type' => 'select',
                'placeholder' => 'Semua Supplier',
            ],
            'delivery_note_number' => [
                'label' => 'Delivery Note',
                'placeholder' => 'No surat jalan',
                'type' => 'text',
            ],
            'invoice_number' => [
                'label' => 'Invoice',
                'placeholder' => 'No invoice',
                'type' => 'text',
            ],
            'keyword' => [
                'label' => 'Keyword',
                'placeholder' => 'Shipment / PO / supplier',
                'type' => 'text',
            ],
        ],
    ],

    'tabs' => [
        'worklist' => 'Worklist',
        'create' => 'Create Draft',
        'archive' => 'Archive',
    ],

    'status_chips' => [
        'worklist' => [
            'draft' => ['label' => 'Draft', 'code' => 'Draft'],
            'shipped' => ['label' => 'Shipped', 'code' => 'Shipped'],
            'partial' => ['label' => 'Partial', 'code' => 'Partial Received'],
        ],
        'archive' => [
            'completed' => ['label' => 'Completed', 'code' => 'Received'],
            'cancelled' => ['label' => 'Cancelled', 'code' => 'Cancelled'],
        ],
    ],
];