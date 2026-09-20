<?php

return [
    'entity' => [
        'singular' => 'Supplier',
        'plural' => 'Suppliers',
        'title' => 'Manajemen Supplier',
        'description' => 'Kelola data master supplier',
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
            'label' => 'Alamat',
            'placeholder' => 'Alamat lengkap supplier',
            'help' => 'Alamat domicile supplier',
        ],
        'phone' => [
            'label' => 'Telepon',
            'placeholder' => 'No. telepon supplier',
            'help' => 'Nomor telepon kontak supplier',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'Email supplier',
            'help' => 'Alamat email supplier',
        ],
        'contact_person' => [
            'label' => 'Kontak',
            'placeholder' => 'Nama PIC supplier',
            'help' => 'Nama orang kontak di supplier',
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
            'address' => 'Alamat',
            'phone' => 'Telepon',
            'email' => 'Email',
            'contact_person' => 'Kontak',
            'updated_at' => 'Terakhir Diubah',
            'status' => 'Status',
            'actions' => 'Aksi',
        ],
        'detail' => [
            'supplier_code' => 'Kode Supplier',
            'supplier_name' => 'Nama Supplier',
            'address' => 'Alamat',
            'phone' => 'Telepon',
            'email' => 'Email',
            'contact_person' => 'Kontak',
            'status' => 'Status',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diubah Pada',
        ],
    ],

    'actions' => [
        'create' => 'Tambah Supplier',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'view' => 'Detail',
        'activate' => 'Aktifkan',
        'deactivate' => 'Nonaktifkan',
    ],

    'validation' => [
        'supplier_code.required' => 'Kode supplier wajib diisi.',
        'supplier_code.unique' => 'Kode supplier sudah digunakan.',
        'supplier_code.max' => 'Kode supplier maksimal 50 karakter.',
        'supplier_name.required' => 'Nama supplier wajib diisi.',
        'supplier_name.max' => 'Nama supplier maksimal 255 karakter.',
        'email.email' => 'Format email tidak valid.',
        'email.max' => 'Email maksimal 255 karakter.',
        'address.max' => 'Alamat maksimal 500 karakter.',
        'phone.max' => 'Telepon maksimal 50 karakter.',
        'contact_person.max' => 'Kontak maksimal 255 karakter.',
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
];
