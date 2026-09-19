<?php

return [
    'entity' => [
        'singular' => 'Kategori Item',
        'plural' => 'Kategori Item',
        'title' => 'Manajemen Kategori Item',
        'description' => 'Kelola kategori untuk pengelompokan item/produk',
    ],

    'fields' => [
        'category_code' => [
            'label' => 'Kode Kategori',
            'placeholder' => 'Kode unik (contoh: CAT-001)',
            'help' => 'Kode identifikasi unik kategori, akan diubah ke huruf besar otomatis',
        ],
        'category_name' => [
            'label' => 'Nama Kategori',
            'placeholder' => 'Nama kategori',
            'help' => 'Nama lengkap kategori item',
        ],
        'description' => [
            'label' => 'Deskripsi',
            'placeholder' => 'Deskripsi singkat kategori',
            'help' => 'Penjelasan tambahan tentang kategori ini',
        ],
        'is_active' => [
            'label' => 'Status',
            'placeholder' => 'Pilih status',
            'help' => 'Kategori aktif dapat digunakan untuk item baru',
            'options' => [
                1 => 'Aktif',
                0 => 'Nonaktif',
                true => 'Aktif',
                false => 'Nonaktif',
            ],
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
            'category_code' => 'Kode',
            'category_name' => 'Nama Kategori',
            'description' => 'Deskripsi',
            'updated_at' => 'Terakhir Diubah',
            'is_active' => 'Status',
            'actions' => 'Aksi',
        ],
        'detail' => [
            'category_code' => 'Kode Kategori',
            'category_name' => 'Nama Kategori',
            'description' => 'Deskripsi',
            'is_active' => 'Status',
            'item_count' => 'Jumlah Item',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diubah Pada',
        ],
        'export' => [
            'category_code' => 'Kode Kategori',
            'category_name' => 'Nama Kategori',
            'description' => 'Deskripsi',
            'is_active' => 'Status',
            'created_at' => 'Tanggal Dibuat',
            'updated_at' => 'Tanggal Diubah',
        ],
    ],

    'actions' => [
        'create' => 'Tambah Kategori',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'view' => 'Detail',
        'activate' => 'Aktifkan',
        'deactivate' => 'Nonaktifkan',
        'export' => 'Export Excel',
    ],

    'validation' => [
        'category_code.required' => 'Kode kategori wajib diisi.',
        'category_code.unique' => 'Kode kategori sudah digunakan.',
        'category_code.max' => 'Kode kategori maksimal 50 karakter.',
        'category_name.required' => 'Nama kategori wajib diisi.',
        'category_name.max' => 'Nama kategori maksimal 150 karakter.',
        'description.max' => 'Deskripsi maksimal 500 karakter.',
        'is_active.required' => 'Status wajib dipilih.',
        'is_active.boolean' => 'Status tidak valid.',
    ],

    'filter_fields' => [
        'q' => [
            'label' => 'Pencarian',
            'placeholder' => 'Cari kode atau nama kategori...',
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
    ],
];