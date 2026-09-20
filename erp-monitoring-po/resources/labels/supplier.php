<?php

return [
    'entity' => [
        'singular' => 'Supplier',
        'plural' => 'Suppliers',
        'title' => 'Manajemen Supplier',
        'description' => 'Kelola data supplier untuk procurement',
        'create_title' => ' tambah Supplier',
        'create_subtitle' => 'Input data supplier baru.',
        'edit_title' => 'Edit Supplier',
        'edit_subtitle' => 'Perbarui identitas supplier.',
    ],

    'fields' => [
        'supplier_code' => [
            'label' => 'Kode Supplier',
            'placeholder' => 'Kode unik (contoh: SUP-001)',
            'help' => 'Kode identifikasi unik supplier, akan diubah ke huruf besar otomatis',
        ],
        'supplier_name' => [
            'label' => 'Nama Supplier',
            'placeholder' => 'Nama perusahaan supplier',
            'help' => 'Nama lengkap perusahaan supplier',
        ],
        'address' => [
            'label' => 'alamat',
            'placeholder' => 'alamat supplier',
            'help' => 'alamat lengkap supplier',
        ],
        'phone' => [
            'label' => 'Telepon',
            'placeholder' => 'Nomor telepon',
            'help' => 'Nomor telepon yang dapat dihubungi',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'email@contoh.com',
            'help' => 'Email aktif untuk komunikasi',
        ],
        'contact_person' => [
            'label' => 'Contact Person',
            'placeholder' => 'Nama contact person',
            'help' => 'Nama orang yang dapat dihubungi',
        ],
        'status' => [
            'label' => 'Status',
            'placeholder' => 'Pilih status',
            'help' => 'Status aktif supplier dapat digunakan dalam PO',
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
            'supplier_code' => 'Kode',
            'supplier_name' => 'Nama Supplier',
            'updated_at' => 'Terakhir Diubah',
            'status' => 'Status',
            'actions' => 'Aksi',
        ],
        'detail' => [
            'supplier_code' => 'Kode Supplier',
            'supplier_name' => 'Nama Supplier',
            'status' => 'Status',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diubah Pada',
        ],
        'export' => [
            'supplier_code' => 'Kode Supplier',
            'supplier_name' => 'Nama Supplier',
            'status' => 'Status',
            'created_at' => 'Tanggal Dibuat',
            'updated_at' => 'Tanggal Diubah',
        ],
    ],

    'actions' => [
        'create' => ':Tambah Supplier',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'view' => 'Detail',
        'activate' => 'Aktifkan',
        'deactivate' => 'Nonaktifkan',
        'export' => 'Export Excel',
        'import' => 'Import Excel',
    ],

    'validation' => [
        'supplier_code.required' => 'Kode supplier wajib diisi.',
        'supplier_code.unique' => 'Kode supplier sudah digunakan.',
        'supplier_code.max' => 'Kode supplier maksimal 50 karakter.',
        'supplier_name.required' => 'Nama supplier wajib diisi.',
        'supplier_name.max' => 'Nama supplier maksimal 255 karakter.',
        'status.required' => 'Status wajib dipilih.',
        'status.boolean' => 'Status tidak valid.',
    ],

    'filter_fields' => [
        'q' => [
            'label' => 'Pencarian',
            'placeholder' => 'Cari kode atau nama supplier...',
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

    'search' => [
        'placeholder' => 'Cari kode atau nama supplier...',
        'aria_label' => 'Cari supplier',
    ],

    'empty' => [
        'title' => 'Belum ada data supplier',
        'subtitle' => 'Mulai tambah supplier baru untuk melihat data di sini.',
    ],
];