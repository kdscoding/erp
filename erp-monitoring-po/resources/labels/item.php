<?php

return [
    'entity' => [
        'singular' => 'Item',
        'plural' => 'Items',
        'title' => 'Katalog Item/Produk',
        'description' => 'Kelola data master item, barang, dan material',
    ],

    'fields' => [
        'item_code' => [
            'label' => 'Kode Item',
            'placeholder' => 'Kode unik (contoh: ITM-001)',
            'help' => 'Kode identifikasi unik item, akan diubah ke huruf besar otomatis',
        ],
        'item_name' => [
            'label' => 'Nama Item',
            'placeholder' => 'Nama barang/material',
            'help' => 'Nama lengkap barang atau material',
        ],
        'category_id' => [
            'label' => 'Kategori',
            'placeholder' => 'Pilih kategori',
            'help' => 'Kategori item untuk pengelompokan',
        ],
        'category' => [
            'label' => 'Kategori (Legacy)',
            'placeholder' => 'Nama kategori',
            'help' => 'Kategori item (field legacy, gunakan category_id jika tersedia)',
        ],
        'unit_id' => [
            'label' => 'Unit',
            'placeholder' => 'Pilih unit',
            'help' => 'Unit of Measure (UOM) untuk item ini',
        ],
        'specification' => [
            'label' => 'Spesifikasi',
            'placeholder' => 'Ukuran, material, warna, printer match, dll',
            'help' => 'Detail spesifikasi teknis item',
        ],
        'active' => [
            'label' => 'Status',
            'placeholder' => 'Pilih status',
            'help' => 'Status aktif item dapat digunakan dalam PO',
            'options' => [
                1 => 'Aktif',
                0 => 'Nonaktif',
                true => 'Aktif',
                false => 'Nonaktif',
            ],
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
            'item_code' => 'Kode',
            'item_name' => 'Nama Barang',
            'category_name' => 'Kategori',
            'category' => 'Kategori',
            'unit_name' => 'Unit',
            'updated_at' => 'Terakhir Diubah',
            'active' => 'Status',
            'actions' => 'Aksi',
        ],
        'detail' => [
            'item_code' => 'Kode Item',
            'item_name' => 'Nama Item',
            'category_name' => 'Kategori',
            'category' => 'Kategori',
            'unit_name' => 'Unit',
            'specification' => 'Spesifikasi',
            'active' => 'Status',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diubah Pada',
        ],
        'export' => [
            'item_code' => 'Kode Item',
            'item_name' => 'Nama Item',
            'category_code' => 'Kode Kategori',
            'unit_code' => 'Kode Unit',
            'specification' => 'Spesifikasi',
            'active' => 'Status',
        ],
        'import_template' => [
            'item_code' => 'item_code',
            'item_name' => 'item_name',
            'category_code' => 'category_code',
            'unit_code' => 'unit_code',
            'specification' => 'specification',
        ],
    ],

    'actions' => [
        'create' => 'Tambah Item',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'view' => 'Detail',
        'activate' => 'Aktifkan',
        'deactivate' => 'Nonaktifkan',
        'export' => 'Export Excel',
        'import' => 'Import Excel',
        'template' => 'Template Excel',
    ],

    'validation' => [
        'item_code.required' => 'Kode item wajib diisi.',
        'item_code.unique' => 'Kode item sudah digunakan.',
        'item_code.max' => 'Kode item maksimal 50 karakter.',
        'item_name.required' => 'Nama item wajib diisi.',
        'item_name.max' => 'Nama item maksimal 255 karakter.',
        'unit_id.exists' => 'Unit tidak valid.',
        'category_id.exists' => 'Kategori tidak valid.',
        'specification.max' => 'Spesifikasi maksimal 500 karakter.',
        'active.required' => 'Status wajib dipilih.',
        'active.boolean' => 'Status tidak valid.',
    ],

    'filter_fields' => [
        'q' => [
            'label' => 'Pencarian',
            'placeholder' => 'Cari kode atau nama item...',
            'type' => 'text',
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'select',
            'options' => [
                '' => 'Semua',
                1 => 'Aktif',
                0 => 'Nonaktif',
            ],
        ],
        'category_id' => [
            'label' => 'Kategori',
            'type' => 'select',
            'placeholder' => 'Semua kategori',
        ],
        'unit_id' => [
            'label' => 'Unit',
            'type' => 'select',
            'placeholder' => 'Semua unit',
        ],
    ],
];