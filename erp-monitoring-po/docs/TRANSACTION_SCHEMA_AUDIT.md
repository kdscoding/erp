# Transaction Schema Audit

Tanggal audit: 2026-03-22

## Ringkasan
- Ada beberapa kolom transaksi yang memang berbeda level dan masih valid.
- Ada beberapa kolom yang overlap secara nama tetapi masih bisa dibenarkan karena konteks bisnisnya berbeda.
- Ada beberapa field yang sudah masuk kategori duplikatif atau tidak tersambung ke flow aktif.

## Temuan Utama

### 1. Dead field di form PO
- View [po/create.blade.php](/c:/laragon/www/erp/erp-monitoring-po/resources/views/po/create.blade.php) sebelumnya punya input `reference_number`.
- Controller [PurchaseOrderController.php](/c:/laragon/www/erp/erp-monitoring-po/app/Http/Controllers/PurchaseOrderController.php) tidak memvalidasi dan tidak menyimpan field itu.
- Tabel `purchase_orders` juga tidak punya kolom `reference_number`.
- Status: sudah dibersihkan dari UI.

### 2. `goods_receipt_items.note` dan `goods_receipt_items.remark` duplikatif
- Schema `goods_receipt_items` punya dua kolom teks: `note` dan `remark`.
- Controller [GoodsReceiptController.php](/c:/laragon/www/erp/erp-monitoring-po/app/Http/Controllers/GoodsReceiptController.php) selalu mengisi keduanya dengan nilai yang sama.
- View [receiving/show.blade.php](/c:/laragon/www/erp/erp-monitoring-po/resources/views/receiving/show.blade.php) juga membaca dengan pola fallback `note ?: remark`.
- Kesimpulan: ini duplikasi nyata.
- Rekomendasi: pertahankan satu kolom saja, idealnya `remark`, lalu migrasikan data dari `note` ke `remark` sebelum drop kolom.

### 3. `purchase_orders.eta_date` overlap dengan ETA di item dan shipment
- Schema `purchase_orders`, `purchase_order_items`, dan `shipments` sama-sama punya `eta_date`.
- Flow aktif lebih banyak bekerja dari `purchase_order_items.etd_date`, `purchase_order_items.outstanding_qty`, dan `shipments.shipment_date` atau status shipment.
- Namun [DashboardController.php](/c:/laragon/www/erp/erp-monitoring-po/app/Http/Controllers/DashboardController.php) dan [ReportController.php](/c:/laragon/www/erp/erp-monitoring-po/app/Http/Controllers/ReportController.php) masih membaca `purchase_orders.eta_date`.
- Masalahnya: `purchase_orders.eta_date` saat ini tidak terlihat di-maintain secara aktif pada flow PO, shipment, dan receiving.
- Kesimpulan: ini bukan duplikasi yang aman dihapus sekarang, tapi sudah rawan menjadi sumber data stale.
- Rekomendasi: tentukan satu sumber ETA header:
  - opsi A: hitung otomatis dari ETA item aktif terdekat/terakhir
  - opsi B: jadikan field manual header dan tampilkan jelas di form PO

### 4. `goods_receipts.document_number` dan `shipments.delivery_note_number` overlap secara nilai, bukan struktur
- Pada flow receiving berbasis dokumen shipment, field `goods_receipts.document_number` default diisi dengan `shipments.delivery_note_number`.
- Ini membuat nilainya sering sama.
- Secara bisnis masih masuk akal, karena GR menyimpan nomor dokumen sumber yang dipakai saat receiving.
- Kesimpulan: bukan duplikasi schema yang wajib dihapus, tapi perlu penamaan yang lebih tegas.
- Rekomendasi:
  - jika maknanya memang selalu nomor dokumen supplier, rename jangka panjang ke `source_document_number`
- jika ingin menampung nomor dokumen GR internal terpisah, pertahankan dua field dan jelaskan bedanya di UI

### 4A. `shipments.purchase_order_id` adalah anchor lama, bukan source of truth shipment composition

- Shipment aktif dapat memuat beberapa `purchase_order_items` dari beberapa PO selama supplier sama.
- Karena itu, komposisi shipment yang benar ada di:
  - `shipment_items.purchase_order_item_id`
  - `purchase_order_items.purchase_order_id`
- Kolom `shipments.purchase_order_id` sekarang hanya berfungsi sebagai anchor/header legacy.

Kesimpulan:

- jangan pakai `shipments.purchase_order_id` sebagai sumber resmi daftar PO pada shipment
- untuk report dan UI, derive daftar PO dari line items
- drop penuh kolom ini perlu redesign lebih besar dan migrasi controller/import/export

### 4B. `goods_receipts.purchase_order_id` juga hanya anchor header

- Receiving berbasis shipment bisa merepresentasikan line dari lebih dari satu PO.
- Source of truth item yang diterima tetap berada di:
  - `goods_receipt_items.purchase_order_item_id`
  - `purchase_order_items.purchase_order_id`
- Kolom `goods_receipts.purchase_order_id` saat ini lebih aman dipahami sebagai anchor/header compatibility field.

Kesimpulan:

- jangan pakai `goods_receipts.purchase_order_id` sebagai sumber resmi daftar PO pada GR multi-line
- UI/report sebaiknya derive daftar PO dari `goods_receipt_items`
- drop penuh kolom ini juga butuh redesign bertahap

### 5. `purchase_orders.notes`, `purchase_order_items.remarks`, `shipments.supplier_remark`, `goods_receipts.remark`
- Nama kolom catatan memang banyak, tetapi levelnya berbeda:
  - `purchase_orders.notes`: catatan header PO
  - `purchase_order_items.remarks`: catatan per item PO
  - `shipments.supplier_remark`: catatan dokumen shipment dari supplier
  - `goods_receipts.remark`: catatan transaksi GR
- Kesimpulan: ini bukan duplikasi, tetapi naming-nya belum seragam.
- Rekomendasi: jika ingin rapi, samakan istilah ke satu pola:
  - header level: `remark`
  - line level: `line_remark`
  - external/source level: `supplier_remark` atau `source_remark`

### 6. `shipment_items.note` belum terlihat dipakai flow aktif
- Schema `shipment_items` punya kolom `note`.
- Tidak ditemukan pemakaian bermakna di flow aktif controller/view.
- Kesimpulan: kandidat cleanup, tetapi perlu cek apakah nanti ingin dipakai untuk catatan alokasi shipment per item.
- Rekomendasi: tahan dulu sampai ada keputusan UX di halaman draft/edit shipment.

### 7. `purchase_orders.sent_to_supplier_at`, `approved_by`, `approved_at`, `bc_reference_no`, `bc_reference_date`
- Kolom-kolom ini ada di schema, tetapi belum terlihat dipakai oleh flow aktif controller/view saat ini.
- Kesimpulan: ini adalah field future-use atau legacy-plan, bukan kebutuhan flow sekarang.
- Rekomendasi:
  - jika memang fase sekarang tidak butuh approval dan BC reference, biarkan dulu tetapi tandai sebagai `reserved`
  - jangan hapus tanpa keputusan bisnis karena bisa jadi masih direncanakan untuk fase lanjutan

## Prioritas Cleanup

### Aman dikerjakan cepat
1. Drop salah satu dari `goods_receipt_items.note` / `goods_receipt_items.remark`
2. Hapus field UI yang tidak tersambung seperti `reference_number` pada form PO
3. Drop `goods_receipt_items.item_id` dan baca item melalui `purchase_order_items.item_id`
4. Drop field dormant `purchase_orders.sent_to_supplier_at`, `approved_by`, `approved_at`, `bc_reference_no`, `bc_reference_date`

### Perlu keputusan bisnis dulu
1. Tetapkan sumber resmi ETA header PO
2. Tentukan apakah `goods_receipts.document_number` adalah nomor dokumen supplier atau nomor dokumen GR internal
3. Tentukan apakah `shipment_items.note` akan dipakai sebagai catatan line shipment

### Sebaiknya ditahan dulu
1. `approved_by`
2. `approved_at`
3. `sent_to_supplier_at`
4. `bc_reference_no`
5. `bc_reference_date`

## Saran Tahap Berikutnya
1. Bersihkan `goods_receipt_items.note` dan pertahankan satu kolom remark saja.
2. Rapikan definisi ETA header PO agar dashboard dan report tidak membaca field yang stale.
3. Setelah itu baru audit kolom approval/reference yang masih dormant untuk diputuskan tetap dipertahankan atau dipindah ke fase berikutnya.

## Keputusan Tambahan Per 2026-04-03

- `id` tetap dipertahankan sebagai primary relational key pada master dan transaksi.
- unique code seperti `supplier_code` atau `item_code` **tidak** langsung dijadikan FK utama transaksi.

Alasan:

- code lebih mungkin berubah dibanding surrogate key
- transaksi, audit, import, dan historical consistency membutuhkan key yang stabil
- readability sebaiknya diperbaiki lewat:
  - UI
  - export
  - alternate lookup
  - document_terms internal code
