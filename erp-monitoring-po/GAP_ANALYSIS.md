# GAP Analysis dan Rekomendasi Pengembangan

Dokumen ini ditulis menggunakan [README.md](d:/server/laragon/www/erp/erp-monitoring-po/README.md) sebagai acuan perilaku sistem saat ini. Tujuannya adalah memetakan kelebihan, kekurangan, gap operasional, dan prioritas pengembangan lanjutan untuk project ERP Monitoring PO, Shipment, dan Receiving.

## Ringkasan Eksekutif

Project ini sudah memiliki fondasi flow bisnis yang kuat untuk operasional procurement:

- master data
- PO manual
- ETD per item
- shipment draft sampai shipped
- receiving berbasis shipment
- reverse melalui cancel GR
- monitoring outstanding
- summary report
- traceability dasar

Masalah terbesar project saat ini bukan pada alur bisnis utama, melainkan pada:

- struktur kode yang mulai berat di controller
- audit trail yang masih dasar
- dokumentasi SOP yang belum dipisah per role
- traceability yang belum benar-benar end-to-end
- analitik operasional yang masih terbatas

## Kelebihan Sistem Saat Ini

### 1. Flow bisnis inti sudah jelas

Sistem sudah punya urutan kerja yang cukup konsisten:

1. siapkan master data
2. buat PO
3. isi ETD per item
4. susun draft shipment
5. mark shipped
6. proses receiving
7. monitor outstanding dan histori

Ini adalah kekuatan besar karena banyak project internal gagal justru karena flow dasarnya belum stabil.

### 2. Model status sudah lebih sehat

Status saat ini fokus ke kondisi operasional aktual, bukan approval workflow semu.

Kelebihan model sekarang:

- PO header mencerminkan monitoring
- item status lebih dekat ke realita lapangan
- shipment dan GR punya state yang cukup tegas
- final state seperti `Closed`, `Cancelled`, dan `Force Closed` sudah relatif jelas

### 3. Guardrail transaksi sudah ada

Contoh guard yang sudah baik:

- over-receipt bisa diblok dari setting
- draft shipment tidak bisa sembarang dikonfirmasi jika ada duplikasi dokumen supplier
- GR cancel mengembalikan qty
- receiving tidak bisa diproses pada state yang tidak valid
- supervisor dibatasi dari receiving

### 4. Monitoring sudah cukup kaya

Project ini tidak berhenti di transaksi, tetapi sudah punya:

- dashboard
- summary per PO
- summary per item
- monitoring detail PO
- traceability
- export Excel di beberapa area

### 5. Ada dasar audit dan histori

Sudah ada:

- `audit_logs`
- `po_status_histories`
- `password_reset_requests`
- `attachments`

Ini memberi titik awal yang bagus untuk penguatan governance.

## Kekurangan dan Gap Utama

### 1. Controller terlalu gemuk

Ini gap teknis paling besar.

Dampak:

- logic bisnis sulit dicari
- rawan duplikasi aturan
- susah di-test secara granular
- perubahan kecil di satu flow berisiko merusak flow lain

Controller yang paling berisiko:

- `PurchaseOrderController`
- `ShipmentController`
- `GoodsReceiptController`
- `DashboardController`

### 2. Query report dan transaksi masih tercampur

Saat ini controller banyak memuat:

- query list
- query summary
- transform monitoring status
- transaction logic
- validasi proses

Akibatnya:

- kode sulit dibagi antar developer
- review menjadi lebih lambat
- maintainability turun

### 3. Traceability belum full end-to-end

Traceability saat ini masih lebih dekat ke summary item receipt.

Gap yang masih terasa:

- perubahan ETD belum menjadi event timeline yang utuh
- shipment belum muncul sebagai milestone utama di traceability page
- siapa melakukan aksi belum ditampilkan
- histori cancel atau reverse belum tergambar sebagai timeline tunggal

### 4. Audit masih bersifat dasar

Sudah ada tabel audit, tetapi belum ada standar enterprise-like untuk:

- format event
- pemisahan severity
- event metadata
- halaman audit viewer
- filtering audit by module / actor / period

### 5. SOP belum siap konsumsi user operasional

README sekarang sudah akurat sebagai dokumentasi sistem, tetapi belum ideal sebagai SOP harian karena:

- belum dipisah per role
- belum dipisah per menu
- belum berbentuk langkah kerja user
- belum menjawab skenario error/failure secara operasional

### 6. Reporting analitik masih minim

Monitoring ada, tapi analitik manajerial masih terbatas.

Gap analitik:

- aging outstanding
- supplier performance periodik
- shipment lead time
- PO cycle time
- receiving productivity
- trend overdue mingguan/bulanan

### 7. Authorization masih dominan di role level

Saat ini pembatasan utama masih di middleware role.

Gap:

- belum ada policy granular per action
- belum ada pembeda yang halus antar jenis admin/staff jika nanti dibutuhkan
- audit belum dikaitkan kuat ke otorisasi keputusan

### 8. Struktur dokumentasi belum tersegmentasi

Satu README yang sangat padat akan sulit dipakai untuk:

- onboarding developer
- training user operasional
- review QA
- audit proses bisnis

## Yang Harus Dilakukan

## Prioritas 1: Pecah Dokumentasi

Buat dokumen turunan dari README:

- `SOP_OPERASIONAL.md`
- `STATUS_DICTIONARY.md`
- `DEVELOPER_GUIDE.md`
- `TEST_SCENARIOS.md`

Tujuan:

- README tetap menjadi sumber kebenaran sistem
- dokumen lain menjadi turunan sesuai audiens

## Prioritas 2: Refactor Struktur Kode

Pecah logic ke lapisan:

- `FormRequest`
- `Service` atau `Action`
- `Query` atau `Report` classes
- helper/status resolver terpusat

Target awal:

- `TraceabilityController`
- `PurchaseOrderController`
- `GoodsReceiptController`
- `ShipmentController`

## Prioritas 3: Perluas Traceability

Bangun traceability berbasis event timeline tunggal, bukan hanya agregasi receipt.

Minimal event yang perlu tampil:

- PO created
- ETD updated
- shipment draft created
- shipment marked shipped
- GR posted
- GR cancelled
- PO cancelled
- item cancelled
- item force closed

## Prioritas 4: Perkuat Audit Trail

Tambahkan:

- standar payload audit
- actor name
- actor role
- before/after summary
- event timestamp yang jelas
- halaman viewer audit per modul

## Prioritas 5: Tambahkan Analitik

Buat report baru:

- aging outstanding per supplier
- ranking supplier paling terlambat
- on-time receipt ratio
- PO lead time
- shipment-to-receiving lead time

## Rekomendasi Arsitektur

### Struktur target

```text
app/
  Actions/
  Services/
  Queries/
  Http/
    Requests/
  Support/
    Status/
    Timeline/
```

### Prinsip

- controller hanya orchestration
- query monitoring/report dipisah dari transaction logic
- status dan rule transisi tidak ditulis ulang di banyak tempat
- semua perubahan flow kritikal harus punya test

## Quick Wins

Berikut item yang bisa dikerjakan cepat tanpa mengubah perilaku bisnis terlalu besar:

### Quick Win 1

Pindahkan query traceability ke class terpisah.

Manfaat:

- controller lebih tipis
- traceability lebih mudah dikembangkan

### Quick Win 2

Tambahkan dokumen `STATUS_DICTIONARY.md`.

Manfaat:

- user dan developer tidak salah paham makna status

### Quick Win 3

Tambahkan `SOP_OPERASIONAL.md`.

Manfaat:

- README tidak perlu menjadi dokumen training user

### Quick Win 4

Buat helper atau resolver untuk status item dan status PO.

Manfaat:

- mengurangi logic `CASE` dan evaluasi status berulang

### Quick Win 5

Tambahkan feature test khusus traceability dan cancel edge cases.

Manfaat:

- menjaga refactor berikutnya tetap aman

## Risiko Jika Tidak Ditangani

Jika project terus bertambah tanpa perbaikan struktur:

- bug status akan makin sulit dilacak
- perubahan kecil bisa menyebabkan regresi besar
- onboarding developer baru akan lambat
- SOP operasional akan tertinggal dari implementasi
- traceability akan sulit dipercaya untuk audit internal

## Saran Praktis Untuk Tim

- anggap README sebagai kontrak perilaku sistem
- setiap perubahan flow harus update:
  - kode
  - test
  - dokumentasi
- jangan menambah fitur besar baru sebelum area controller inti dirapikan
- mulai dari modul dengan dampak paling besar, bukan dari file kecil acak

## Roadmap Bertahap

### Tahap 1

- buat dokumen SOP dan kamus status
- tambahkan test pada flow kritikal yang belum tertutup

### Tahap 2

- refactor traceability
- refactor PO status resolver
- refactor receiving logic

### Tahap 3

- bangun audit viewer
- bangun timeline traceability terpadu
- tambahkan analitik supplier dan aging

### Tahap 4

- pecah route per modul
- rapikan authorization ke policy
- siapkan fondasi scaling dan onboarding developer

## Penutup

Project ini sudah cukup matang secara logika bisnis inti. Fokus berikutnya sebaiknya bukan menambah banyak flow baru, tetapi memperkuat:

- struktur kode
- dokumentasi operasional
- auditability
- traceability
- analitik

Dengan itu, sistem akan lebih mudah dirawat, lebih aman diubah, dan lebih siap dipakai sebagai aplikasi operasional yang serius.
