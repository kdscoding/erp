<?php

return [
    'entity' => [
        'singular' => 'Unit',
        'plural' => 'Units',
        'title' => 'Manajemen Unit of Measure (UOM)',
        'description' => 'Kelola satuan pengukuran untuk item/produk',
    ],

    'fields' => [
        'unit_code' => [
            'label' => 'Kode Unit',
            'placeholder' => 'Mis. PCS, KG, M, LTR',
            'help' => 'Kode singkat unit (contoh: PCS, KG, M, LTR, BOX), akan diubah ke huruf besar otomatis',
        ],
        'unit_name' => [
            'label' => 'Nama Unit',
            'placeholder' => 'Nama lengkap unit',
            'help' => 'Nama lengkap satuan pengukuran (contoh: Pieces, Kilogram, Meter, Liter, Box)',
        ],
        'item_count' => [
            'label' => 'Jumlah Item',
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
            'unit_code' => 'Kode',
            'unit_name' => 'Nama Unit',
            'item_count' => 'Digunakan',
            'created_at' => 'Terakhir Diubah',
            'actions' => 'Aksi',
        ],
        'detail' => [
            'unit_code' => 'Kode Unit',
            'unit_name' => 'Nama Unit',
            'item_count' => 'Jumlah Item',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diubah Pada',
        ],
        'export' => [
            'unit_code' => 'Kode Unit',
            'unit_name' => 'Nama Unit',
            'created_at' => 'Tanggal Dibuat',
            'updated_at' => 'Tanggal Diubah',
        ],
    ],

    'actions' => [
        'create' => 'Tambah Unit',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'view' => 'Detail',
        'export' => 'Export Excel',
    ],

    'validation' => [
        'unit_code.required' => 'Kode unit wajib diisi.',
        'unit_code.unique' => 'Kode unit sudah digunakan.',
        'unit_code.max' => 'Kode unit maksimal 50 karakter.',
        'unit_name.required' => 'Nama unit wajib diisi.',
        'unit_name.max' => 'Nama unit maksimal 100 karakter.',
    ],

    'filter_fields' => [
        'q' => [
            'label' => 'Pencarian',
            'placeholder' => 'Cari kode atau nama unit...',
            'type' => 'text',
        ],
    ],
];