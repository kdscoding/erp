<?php

return [
    'validation' => [
        // Code fields (kode_*)
        'code.required' => 'Kode :field wajib diisi.',
        'code.unique' => 'Kode :field sudah digunakan.',
        'code.max' => 'Kode :field maksimal :max karakter.',
        'code.string' => 'Kode :field harus berupa teks.',

        // Name fields (nama_*, *_name)
        'name.required' => 'Nama :field wajib diisi.',
        'name.max' => 'Nama :field maksimal :max karakter.',
        'name.string' => 'Nama :field harus berupa teks.',

        // Email
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'email.max' => 'Email maksimal :max karakter.',

        // Status/boolean
        'status.required' => 'Status wajib dipilih.',
        'status.boolean' => 'Status tidak valid.',
        'status.in' => 'Status tidak valid.',

        // General
        'required' => ':field wajib diisi.',
        'unique' => ':field sudah digunakan.',
        'max' => ':field maksimal :max karakter.',
        'min' => ':field minimal :min karakter.',
        'numeric' => ':field harus berupa angka.',
        'integer' => ':field harus berupa bilangan bulat.',
        'date' => ':field harus berupa tanggal yang valid.',
        'date_format' => ':field harus berupa tanggal dengan format :format.',
        'exists' => ':field tidak valid.',
        'in' => ':field tidak valid.',
        'array' => ':field harus berupa array.',
        'file' => ':field harus berupa file.',
        'mimes' => 'Format file :field harus :values.',
        'confirmed' => 'Konfirmasi :field tidak cocok.',
        'sometimes' => '',

        // Specific common fields
        'quantity.required' => 'Qty wajib diisi.',
        'quantity.numeric' => 'Qty harus berupa angka.',
        'quantity.min' => 'Qty minimal :min.',
        'quantity.max' => 'Qty maksimal :max.',

        'unit_price.required' => 'Harga wajib diisi.',
        'unit_price.numeric' => 'Harga harus berupa angka.',
        'unit_price.min' => 'Harga minimal :min.',

        'total_price.required' => 'Total wajib diisi.',
        'total_price.numeric' => 'Total harus berupa angka.',

        'description.max' => 'Deskripsi maksimal :max karakter.',

        'notes.max' => 'Catatan maksimal :max karakter.',

        'remark.max' => 'Catatan maksimal :max karakter.',

        'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
        'cancel_reason.max' => 'Alasan pembatalan maksimal :max karakter.',

        'reason.required' => 'Alasan wajib diisi.',

        'file.required' => 'File wajib dipilih.',
        'file.mimes' => 'Format file harus :values.',

        // ID fields
        '*_id.required' => ':field wajib dipilih.',
        '*_id.exists' => ':field tidak valid.',
    ],

    'field_names' => [
        'supplier_code' => 'supplier',
        'supplier_name' => 'supplier',
        'item_code' => 'item',
        'item_name' => 'item',
        'category_code' => 'kategori',
        'category_name' => 'kategori',
        'unit_code' => 'unit',
        'unit_name' => 'unit',
        'po_number' => 'PO',
        'po_date' => 'tanggal PO',
        'shipment_number' => 'shipment',
        'shipment_date' => 'tanggal shipment',
        'delivery_note_number' => 'delivery note',
        'invoice_number' => 'invoice',
        'status' => 'status',
        'quantity' => 'qty',
        'unit_price' => 'harga',
        'total_price' => 'total',
        'specification' => 'spesifikasi',
        'description' => 'deskripsi',
        'notes' => 'catatan',
        'cancel_reason' => 'alasan pembatalan',
        'file' => 'file',
    ],
];