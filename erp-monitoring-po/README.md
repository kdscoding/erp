# ERP Monitoring PO, Shipment, dan Receiving

Dokumen ini disusun ulang dari implementasi aktif di kode saat ini pada folder `app`, `routes`, `database`, `resources`, dan `tests`. Tujuannya adalah menjadikan README sebagai dasar dokumentasi operasional, acuan pengembangan, dan bahan turunan untuk SOP.

Dokumen pendukung sekarang dikumpulkan di folder [docs](./docs/README.md) agar tidak tersebar di root aplikasi.

## Ringkasan Sistem

Sistem ini adalah aplikasi internal berbasis Laravel untuk mengelola dan memonitor alur:

1. master data supplier, item, kategori item, unit, warehouse, dan plant
2. pembuatan Purchase Order manual
3. konfirmasi jadwal item melalui ETD
4. penyusunan draft shipment per supplier
5. konfirmasi shipment menjadi dokumen kirim
6. posting goods receipt berbasis shipment
7. monitoring outstanding, dashboard, summary, dan traceability
8. pengelolaan user, settings, document terms, dan audit dasar

Fokus sistem saat ini adalah monitoring outstanding dan jejak proses procurement, bukan approval workflow bertingkat.

## Catatan UI Saat Ini

Struktur UI sedang masuk fase penyederhanaan.

Keputusan terbaru:

- `Monitoring Hub` menjadi layar utama untuk membaca outstanding
- mode utama di monitoring adalah:
  - `PO View`
  - `Item View`
- `Summary PO` dan `Summary Item` tidak lagi diposisikan sebagai report utama yang berdiri sendiri
- dashboard disederhanakan agar fokus ke:
  - KPI utama
  - top delayed suppliers
  - action center

Catatan penting:

- route `summary.po` dan `summary.item` masih ada untuk kompatibilitas sementara
- tetapi arah produk ke depan adalah mengonsolidasikan layar-layar tersebut ke `Monitoring Hub`
- `Traceability` sedang digeser menjadi workspace investigasi, bukan report agregat kedua

## Teknologi dan Dependensi Utama

- PHP `^8.3`
- Laravel `^13.0`
- Laravel Breeze
- MySQL
- `maatwebsite/excel` untuk kebutuhan export/import Excel tertentu

## Modul Aktif

- Dashboard Outstanding
- Monitoring Hub
- Purchase Order
- Traceability
- Supplier Performance
- Audit Viewer
- Shipment Worklist, Draft Builder, Archive
- Receiving Process dan Receiving History
- Master Data:
  - Supplier
  - Item Category
  - Item
  - Unit
  - Warehouse
  - Plant
- Settings:
  - General settings
  - Document terms
  - User management
- Profile dan authentication

## Akses dan Role

Role aktif:

- `administrator`
- `staff`
- `supervisor`

Hak akses ringkas:

- `administrator`
  - full access
  - settings
  - user management
  - master data
  - PO
  - shipment
  - receiving
  - monitoring/report
- `staff`
  - master data
  - PO
  - shipment
  - receiving
  - monitoring/report
- `supervisor`
  - dashboard
  - summary report
  - monitoring PO
  - traceability
  - detail PO
  - tidak dapat memproses receiving

Middleware utama:

- seluruh modul bisnis berada di grup `auth`
- modul bisnis utama berada di grup `auth + verified`
- pembatasan role dijalankan oleh `RoleMiddleware`

## Login dan User

- login menggunakan `NIK`, bukan email
- user harus aktif (`is_active = 1`)
- validasi login memakai kombinasi:
  - `nik`
  - `password`
  - `is_active`
- sistem menyediakan alur permintaan reset password:
  - user mengajukan request reset password dari halaman lupa password dengan `NIK` dan catatan
  - request disimpan ke tabel `password_reset_requests`
  - administrator membuka halaman edit user
  - administrator hanya bisa reset password bila ada request yang masih `pending`
  - setelah diproses, request berubah menjadi `processed`

## Akun Demo Seeder

- Administrator
  - NIK: `10000001`
  - Email: `admin@erp.local`
  - Password: `password`
- Staff
  - NIK: `10000002`
  - Email: `staff@erp.local`
  - Password: `password`
- Supervisor
  - NIK: `10000003`
  - Email: `supervisor@erp.local`
  - Password: `password`

## Instalasi Baru

```bash
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Jika frontend asset perlu dibangun:

```bash
npm install
npm run build
```

## Upgrade Database Existing

Untuk database lama, jalankan:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=MasterDataSeeder
php artisan db:seed --class=DocumentTermSeeder
```

Catatan:

- hindari `migrate:fresh` jika data transaksi lama masih dipakai
- `DocumentTermSeeder` penting karena status tampilan dan badge mengambil data dari `document_terms`
- migration status terbaru juga menambahkan dan mengisi kolom internal code pada transaksi, histori, dan term catalog

## Konfigurasi Environment Dasar

```env
APP_TIMEZONE=Asia/Jakarta
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_monitoring_po
DB_USERNAME=root
DB_PASSWORD=
```

## Penomoran Dokumen Otomatis

Nomor dokumen digenerate oleh `App\Support\ErpFlow::generateNumber()` dengan format:

- PO: `PO-YYYYMMDD-####`
- Shipment: `SHP-YYYYMMDD-####`
- Goods Receipt: `GR-YYYYMMDD-####`

Nomor manual tetap bisa diisi untuk PO. Jika `po_number` dikosongkan saat create, sistem akan generate otomatis.

## Master Data dan Aturan Normalisasi

### Supplier

Field utama:

- `supplier_code`
- `supplier_name`
- `address`
- `phone`
- `email`
- `contact_person`
- `status`

Aturan:

- `supplier_code` di-trim lalu di-uppercase
- harus unik
- supplier tidak di-hard delete dari flow aplikasi
- status supplier dapat diaktifkan/nonaktifkan

### Item Category

Field utama:

- `category_code`
- `category_name`
- `description`
- `is_active`

Aturan:

- `category_code` di-trim lalu di-uppercase
- harus unik
- kategori dapat diaktifkan/nonaktifkan

### Item

Field utama:

- `item_code`
- `item_name`
- `category_id`
- `unit_id`
- `specification`
- `active`

Aturan:

- `item_code` di-trim lalu di-uppercase
- harus unik
- item dapat diaktifkan/nonaktifkan
- item mendukung kategori master melalui `category_id`

### Unit, Warehouse, Plant

Field master ini dipakai sebagai referensi transaksi:

- Unit:
  - `unit_code`
  - `unit_name`
- Warehouse:
  - `warehouse_code`
  - `warehouse_name`
  - `location`
- Plant:
  - `plant_code`
  - `plant_name`

Kode pada master ini juga dinormalisasi ke uppercase saat simpan/update.

## Settings dan Document Terms

Halaman settings saat ini mengelola dua hal:

1. `allow_over_receipt`
2. `document_terms`

### allow_over_receipt

Jika aktif:

- receiving boleh melebihi sisa qty shipment
- receiving boleh melebihi outstanding PO

Jika tidak aktif:

- receiving diblok bila qty lebih besar dari sisa kiriman
- receiving diblok bila qty lebih besar dari outstanding PO

Default dari seeder adalah `0` atau tidak diizinkan.

### document_terms

`document_terms` dipakai untuk:

- label status tampilan
- sort order status
- aktif/nonaktif opsi term
- badge class dan badge text di UI

Penting:

- perubahan `document_terms` hanya mengubah label dan tampilan
- kode internal status tidak berubah
- flow bisnis tetap mengikuti nilai `code`, bukan label display
- repo saat ini masih dalam fase transisi; beberapa tabel transaksi masih menyimpan legacy string, tetapi lapisan kompatibilitas internal mulai dipisahkan melalui `App\Support\DomainStatus`
- transaksi baru sekarang mulai menyimpan kolom `status_code` / `item_status_code` di samping legacy term untuk jalur migrasi bertahap

## Status Resmi Sistem

Catatan transisi:

- baseline transaksi aktif masih banyak memakai legacy term seperti `Open`, `Late`, `Draft`, dan `Cancelled`
- arah refactor resmi ke depan adalah memakai stable internal code seperti `po_open`, `item_partial`, `shipment_draft`, dan `gr_posted`
- mapping antara internal code dan display term sekarang mulai dipusatkan di `App\Support\DomainStatus`

### PO Header

Legacy term yang masih aktif di data:

- `PO Issued`
- `Open`
- `Late`
- `Closed`
- `Cancelled`

Target internal code:

- `po_issued`
- `po_open`
- `po_late`
- `po_closed`
- `po_cancelled`

Makna:

- `PO Issued`
  - semua item aktif masih waiting
  - belum ada ETD aktif, alokasi shipment, partial receipt, atau close
- `Open`
  - PO sudah berjalan
  - minimal ada ETD, alokasi shipment, partial receipt, atau item selesai
  - tetapi belum masuk kondisi `Late`, `Closed`, atau `Cancelled`
- `Late`
  - minimal ada item aktif yang outstanding dan ETD-nya sudah lewat
- `Closed`
  - seluruh item aktif sudah selesai
  - item `Force Closed` ikut dianggap final karena outstanding menjadi `0`
- `Cancelled`
  - semua item dibatalkan, atau header PO dibatalkan manual

Catatan penting:

- status header PO dihitung ulang otomatis oleh `ErpFlow::refreshPoStatusByOutstanding()`
- status header tidak lagi memakai model lama seperti `Draft`, `Submitted`, atau `Approved`

### PO Item

Legacy term yang masih aktif di data:

- `Waiting`
- `Confirmed`
- `Late`
- `Partial`
- `Closed`
- `Force Closed`
- `Cancelled`

Target internal code:

- `item_waiting`
- `item_confirmed`
- `item_late`
- `item_partial`
- `item_closed`
- `item_force_closed`
- `item_cancelled`

Makna operasional:

- `Waiting`
  - item aktif
  - belum ada ETD
  - belum ada receipt
- `Confirmed`
  - ETD sudah diisi
  - belum ada receipt
- `Late`
  - status monitoring saat item outstanding dan ETD sudah lewat
  - dipakai untuk monitoring/report
- `Partial`
  - item sudah diterima sebagian
  - outstanding masih ada
- `Closed`
  - outstanding `0` karena qty terpenuhi
- `Force Closed`
  - item ditutup paksa
  - outstanding di-set `0`
  - alasan disimpan di `cancel_reason` dengan prefix `[FORCE CLOSE]`
- `Cancelled`
  - item dibatalkan sebelum penerimaan

Catatan penting:

- `Late` adalah status monitoring yang muncul dari evaluasi tanggal ETD dan outstanding
- item tidak mempunyai proses approve manual

### Shipment

Legacy term yang masih aktif di data:

- `Draft`
- `Shipped`
- `Partial Received`
- `Received`
- `Cancelled`

Target internal code:

- `shipment_draft`
- `shipment_shipped`
- `shipment_partial_received`
- `shipment_received`
- `shipment_cancelled`

Makna:

- `Draft`
  - shipment sudah disusun tetapi belum dikonfirmasi sebagai dokumen kirim
- `Shipped`
  - draft sudah dikonfirmasi menjadi dokumen kirim
  - siap diproses receiving
- `Partial Received`
  - minimal ada line shipment yang sudah diterima
  - tetapi belum semua line complete
- `Received`
  - seluruh line shipment sudah received penuh
- `Cancelled`
  - draft dibatalkan

### Goods Receipt

Legacy term yang masih aktif di data:

- `Posted`
- `Cancelled`

Target internal code:

- `gr_posted`
- `gr_cancelled`

Makna:

- `Posted`
  - receiving sudah tercatat dan mengubah qty transaksi
- `Cancelled`
  - GR dibatalkan
  - qty pada PO item dan shipment item dikembalikan

## Alur Proses Sistem

### 1. Persiapan Master Data

Sebelum transaksi, siapkan minimal:

- supplier
- unit
- item category
- item
- warehouse jika receiving perlu referensi gudang
- plant jika header PO perlu referensi plant

Master supplier, kategori item, dan item mendukung aktif/nonaktif. Unit, warehouse, dan plant saat ini berfokus pada create/update.

### 2. Create Purchase Order

Proses:

1. user membuka menu `PO -> Create`
2. isi:
   - nomor PO opsional
   - tanggal PO
   - supplier
   - catatan header
   - item lines
3. setiap line item wajib punya:
   - item
   - ordered qty
4. line item opsional:
   - unit price
   - remarks
5. sistem membuat header PO
6. jika `po_number` kosong, sistem generate otomatis
7. seluruh item dibuat dengan:
   - `received_qty = 0`
   - `outstanding_qty = ordered_qty`
   - `item_status = Waiting`
8. header PO dibuat dengan status `PO Issued`
9. histori status awal disimpan ke `po_status_histories`
10. audit log create disimpan ke `audit_logs`

### 3. Update ETD Item

ETD dikelola per item PO, bukan per header.

Aturan:

- hanya bisa diubah jika header PO belum final
- item tidak boleh `Closed`, `Force Closed`, atau `Cancelled`

Efek saat update ETD:

- jika outstanding `0` maka status tetap `Closed`
- jika sudah ada receipt maka status menjadi `Partial`
- jika ETD diisi dan belum ada receipt maka status menjadi `Confirmed`
- jika ETD dikosongkan dan belum ada receipt maka status menjadi `Waiting`
- setelah itu header PO dihitung ulang otomatis
- `eta_date` header disinkronkan dari earliest active item schedule melalui `ErpFlow::resolvePoEtaDate()`

### 4. Cancel Item PO

Aturan:

- hanya item aktif
- belum pernah diterima
- header PO belum final

Efek:

- item menjadi `Cancelled`
- `outstanding_qty` menjadi `0`
- alasan disimpan di `cancel_reason`
- header PO dihitung ulang

### 5. Force Close Item PO

Dipakai saat sisa outstanding tidak akan diteruskan.

Aturan:

- header PO belum final
- item belum final
- item masih punya outstanding

Efek:

- item menjadi `Force Closed`
- `outstanding_qty` menjadi `0`
- alasan disimpan di `cancel_reason`
- header PO dihitung ulang

### 6. Cancel Header PO

Aturan:

- header PO tidak boleh sudah `Closed`
- header PO tidak boleh sudah `Cancelled`

Efek:

- header menjadi `Cancelled`
- `eta_date` header dihapus
- item non-final selain `Closed` dan `Force Closed` diubah menjadi `Cancelled`
- histori perubahan disimpan
- audit log disimpan

### 7. Create Draft Shipment

Shipment disusun dari kandidat item PO yang masih bisa dikirim.

Aturan inti:

- shipment hanya boleh berisi item dari supplier yang sama
- delivery note harus unik per supplier selama shipment lain belum `Cancelled`
- invoice number, jika diisi, juga harus unik per supplier selama shipment lain belum `Cancelled`
- qty kirim per item tidak boleh melebihi `available_to_ship_qty`
- draft minimal berisi satu item

Input shipment:

- shipment date
- delivery note number
- invoice number opsional
- invoice date opsional
- invoice currency opsional
- supplier remark opsional
- flag `po_reference_missing` opsional
- selected items
- shipped qty per item
- invoice unit price per item opsional

Karakter proses:

- satu shipment dapat mencakup beberapa PO selama supplier-nya sama
- shipment menyimpan:
  - header dokumen
  - line per `purchase_order_item_id`
  - harga invoice di level line

Status hasil create:

- shipment baru selalu `Draft`

### 8. Edit Draft Shipment

Yang bisa diubah:

- shipment date
- delivery note
- invoice header
- supplier remark
- qty line
- harga invoice line
- item line yang dipertahankan

Aturan:

- hanya shipment `Draft`
- minimal satu line harus tetap ada
- qty line hasil edit tidak boleh melebihi batas aktual yang masih tersedia

### 9. Mark Shipment as Shipped

Saat draft final, user menekan `Mark Shipped`.

Aturan:

- hanya shipment `Draft`
- shipment harus punya line aktif
- delivery note dan invoice tetap dicek ulang duplikasinya pada saat konfirmasi

Efek:

- status shipment menjadi `Shipped`
- user diarahkan ke halaman receiving dengan shipment terpilih
- header PO terkait dihitung ulang

Catatan:

- perubahan shipment ke `Shipped` tidak otomatis menutup PO
- PO tetap `Open` atau `Late` bila masih ada item outstanding lain

### 10. Cancel Draft Shipment

Aturan:

- hanya shipment `Draft`

Efek:

- status shipment menjadi `Cancelled`
- line shipment tidak dihapus
- PO terkait dihitung ulang

### 11. Receiving Process

Receiving saat ini berorientasi pada dokumen shipment.

Dokumen yang bisa diproses:

- shipment `Shipped`
- shipment `Partial Received`
- line shipment yang masih memiliki sisa kiriman

Halaman process menampilkan:

- shipment worklist
- filter supplier
- filter delivery note
- keyword shipment / PO / invoice / supplier
- form receiving per shipment

Input utama receiving:

- `receipt_date`
- `document_number`
- `note`
- `attachment` opsional
- qty terima per line shipment

Aturan inti:

- minimal satu qty receiving harus lebih dari `0`
- jika `allow_over_receipt = 0`:
  - qty tidak boleh melebihi sisa qty shipment
  - qty tidak boleh melebihi outstanding PO
- receiving tidak bisa diproses untuk shipment selain `Shipped` atau `Partial Received`

Efek posting receiving:

- create header GR dengan status `Posted`
- create line GR per shipment item yang diisi qty
- update:
  - `purchase_order_items.received_qty`
  - `purchase_order_items.outstanding_qty`
  - `purchase_order_items.item_status`
  - `shipment_items.received_qty`
- refresh status PO
- refresh status shipment
- simpan attachment ke `storage/app/public/attachments/receiving` bila ada
- simpan attachment metadata ke tabel `attachments`

Status item setelah receiving:

- jika outstanding masih ada: `Partial`
- jika outstanding `0`: `Closed`

Status shipment setelah receiving:

- `Shipped` jika belum ada line received
- `Partial Received` jika ada line received tetapi belum semua complete
- `Received` jika semua line complete

### 12. Single-Line Receiving

Controller juga mendukung posting receiving per satu `shipment_item_id`. Secara bisnis hasilnya sama:

- create satu GR
- update qty PO item
- update qty shipment item
- refresh PO
- refresh shipment

### 13. Cancel Goods Receipt

GR yang sudah `Posted` bisa dibatalkan dari receiving history.

Syarat:

- GR harus `Posted`
- wajib isi `cancel_reason`
- GR harus punya item

Efek cancel GR:

- status GR menjadi `Cancelled`
- `cancel_reason`, `cancelled_by`, dan `cancelled_at` diisi
- qty receiving pada item GR dikembalikan dari:
  - `purchase_order_items.received_qty`
  - `purchase_order_items.outstanding_qty`
  - `shipment_items.received_qty`
- status item dihitung ulang:
  - `Closed` jika outstanding `0`
  - `Partial` jika received masih ada
  - `Confirmed` jika belum ada receipt dan ETD ada
  - `Waiting` jika belum ada receipt dan ETD kosong
- status shipment dihitung ulang
- status PO dihitung ulang

## Aturan Monitoring Penting

### ETA Header PO

`purchase_orders.eta_date` tidak diinput manual sebagai sumber utama. Nilai ini disinkronkan dari earliest active item schedule:

- `MIN(COALESCE(eta_date, etd_date))`
- item cancelled tidak ikut dihitung
- item dengan outstanding `0` tidak ikut dihitung

### Status PO adalah status monitoring

Header PO sekarang tidak mewakili approval process, melainkan kondisi operasional outstanding.

Contoh:

- ada satu item confirmed dan item lain masih waiting: header bisa menjadi `Open`
- ada item overdue ETD: header menjadi `Late`
- semua item aktif selesai atau force close: header menjadi `Closed`

### Status item `Late` bersifat monitoring

Status `Late` dipakai luas pada query report dan tampilan item monitoring ketika:

- ETD terisi
- outstanding masih ada
- tanggal ETD sudah lewat

### Shipment dapat menggabungkan beberapa PO

Satu dokumen shipment boleh mencakup lebih dari satu PO selama:

- semua line berasal dari supplier yang sama

### Unik dokumen supplier

Untuk supplier yang sama:

- `delivery_note_number` tidak boleh dipakai dua kali pada shipment aktif
- `invoice_number` juga tidak boleh dipakai dua kali pada shipment aktif

Shipment `Cancelled` tidak lagi memblokir reuse nomor tersebut.

## Halaman Monitoring dan Report

### Dashboard

Dashboard sekarang diposisikan sebagai executive overview, bukan report detail penuh.

Isi utama yang dipertahankan:

- open PO
- at-risk items
- shipment hari ini
- receiving hari ini
- top delayed suppliers
- action center
- shortcut ke monitoring/report utama

Filter dashboard:

- supplier
- tanggal PO dari
- tanggal PO sampai

### Monitoring Hub

`Monitoring Hub` adalah layar utama monitoring outstanding dan menggantikan overlap antara:

- `Summary Outstanding PO`
- `Summary Outstanding Item`
- sebagian isi `Monitoring PO` lama

Mode utama:

- `PO View`
- `Item View`

Isi utama:

- filter tunggal
- summary chips tunggal
- tabel utama sesuai mode
- link drill-down ke detail PO

Mendukung export Excel.

### Summary Outstanding PO / Item

Route lama masih dipertahankan untuk kompatibilitas, tetapi secara produk keduanya bukan lagi halaman report utama. Arah resminya adalah redirect atau transisi penuh ke `Monitoring Hub`.

### Monitoring PO

Menu ini sekarang secara UX diposisikan sebagai `Monitoring Hub`, walau nama route historis masih dipertahankan di beberapa bagian kode.

Halaman detail PO menampilkan:

- header PO
- item summary
- histori status
- item monitoring
- update ETD
- cancel item
- force close item
- cancel header PO
- tracking shipment dan goods receipt per item
- export Excel detail PO

### Traceability

Halaman traceability sedang diarahkan menjadi workspace investigasi.

Struktur target yang sekarang mulai dipakai:

- panel kiri untuk hasil pencarian atau daftar PO
- panel kanan untuk timeline event dan detail per item

Filter traceability saat ini:

- `po_number`

Catatan:

- detail tracking shipment dan GR paling lengkap masih ada di halaman detail PO
- traceability akan terus digeser dari report agregat menjadi layar investigasi

## Audit dan Histori

Sistem sudah memakai audit dasar pada area penting:

- create PO
- update ETD item
- cancel item
- force close item
- cancel PO
- create shipment
- update shipment
- mark shipped
- cancel draft shipment
- create goods receipt
- cancel goods receipt
- reset password oleh admin

Tabel terkait:

- `audit_logs`
- `po_status_histories`
- `password_reset_requests`
- `attachments`

## Export dan Import yang Sudah Aktif

Fitur yang sudah aktif saat ini:

- export monitoring PO ke Excel
- export detail PO ke Excel
- export summary outstanding PO ke Excel
- export summary outstanding item ke Excel
- export draft shipment ke Excel
- import draft shipment dari Excel
- download template draft shipment

Fitur export PDF umum belum terlihat sebagai flow aktif di kode saat ini.

## Struktur Data Inti

Tabel inti operasional:

- `purchase_orders`
- `purchase_order_items`
- `shipments`
- `shipment_items`
- `goods_receipts`
- `goods_receipt_items`

Tabel pendukung:

- `suppliers`
- `items`
- `item_categories`
- `units`
- `warehouses`
- `plants`
- `roles`
- `user_roles`
- `settings`
- `document_terms`
- `po_status_histories`
- `audit_logs`
- `attachments`
- `password_reset_requests`

## Testing

Menjalankan test:

```bash
php artisan test
```

Coverage feature test yang sudah terlihat:

- flow create PO
- flow shipment draft -> shipped -> receiving -> close
- multi-PO per shipment
- cancel draft shipment
- pembatasan over-receipt
- role restriction receiving
- force close item
- cancel goods receipt dan reversal qty
- sinkronisasi ETA header PO
- tracking shipment dan goods receipt di detail PO
- validasi normalisasi master data

## Catatan Pengembangan

Jika README ini dipakai sebagai dasar SOP:

- bedakan dengan tegas antara legacy term yang masih tersimpan di data dan internal code target refactor
- pisahkan antara:
  - status internal
  - label display dari `document_terms`
- pakai halaman detail PO sebagai acuan utama untuk tracking lengkap
- pakai receiving history sebagai acuan reversal transaksi
- jangan jadikan `summary-po` dan `summary-item` sebagai referensi UX utama baru

Jika ingin refactor besar:

- pertahankan flow bisnis yang terdokumentasi di README ini sebagai baseline
- jadikan README ini sebagai kontrak perilaku sistem sebelum memecah controller, service, dan query layer

## Hal Yang Sudah Tidak Relevan dan Dihapus Dari README Lama

Dokumentasi lama yang sudah tidak dipakai lagi dan tidak boleh dijadikan acuan:

- flow status PO lama berbasis `Draft -> Submitted -> Approved`
- asumsi bahwa `shipment_items` tidak dipakai
- asumsi bahwa export Excel belum aktif
- istilah lama yang menyamakan status header PO dengan approval lifecycle

Gunakan README ini sebagai acuan sistem saat ini.
