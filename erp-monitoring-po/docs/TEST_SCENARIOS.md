# Test Scenarios

Dokumen ini merangkum skenario test bisnis yang penting untuk menjaga stabilitas sistem saat refactor atau penambahan fitur.

## Tujuan

- menjadi checklist QA
- menjadi acuan saat menambah feature test
- menjadi pagar pengaman sebelum refactor besar

## Skenario Inti Purchase Order

### PO-01 Create PO dengan nomor otomatis

Expected:

- PO tersimpan
- nomor otomatis terbentuk
- status header `PO Issued`
- item status `Waiting`

### PO-02 Create PO dengan nomor manual

Expected:

- PO tersimpan dengan nomor manual
- nomor tidak bentrok

### PO-03 Update ETD item dari kosong ke tanggal valid

Expected:

- item berubah ke `Confirmed` jika belum ada receiving
- header PO dihitung ulang
- ETA header tersinkron

### PO-04 Kosongkan ETD item yang belum ada receiving

Expected:

- item kembali ke `Waiting`

### PO-05 Update ETD saat item sudah final

Expected:

- aksi ditolak

### PO-06 Cancel item sebelum ada receiving

Expected:

- item `Cancelled`
- outstanding `0`
- header PO dihitung ulang

### PO-07 Cancel item yang sudah pernah diterima

Expected:

- aksi ditolak

### PO-08 Force close item yang masih outstanding

Expected:

- item `Force Closed`
- outstanding `0`

### PO-09 Force close item yang sudah final

Expected:

- aksi ditolak

### PO-10 Cancel header PO

Expected:

- header menjadi `Cancelled`
- item non-final ikut `Cancelled`

### PO-11 Cancel header PO yang sudah final

Expected:

- aksi ditolak

## Skenario Inti Shipment

### SHP-01 Create draft shipment dari satu item

Expected:

- shipment `Draft`
- line shipment tersimpan

### SHP-02 Create draft shipment dari banyak item satu supplier

Expected:

- draft berhasil

### SHP-03 Create draft shipment dari item beda supplier

Expected:

- aksi ditolak

### SHP-04 Duplicate delivery note pada supplier yang sama

Expected:

- aksi ditolak jika shipment aktif masih ada

### SHP-05 Duplicate invoice pada supplier yang sama

Expected:

- aksi ditolak jika shipment aktif masih ada

### SHP-06 Reuse delivery note setelah shipment cancelled

Expected:

- aksi boleh

### SHP-07 Qty shipment melebihi available to ship

Expected:

- aksi ditolak

### SHP-08 Edit draft shipment

Expected:

- header dan line terupdate

### SHP-09 Edit draft hingga line terakhir terhapus

Expected:

- aksi ditolak

### SHP-10 Mark draft menjadi shipped

Expected:

- status `Shipped`
- redirect ke receiving

### SHP-11 Cancel draft shipment

Expected:

- status `Cancelled`
- shipment tidak dihapus

## Skenario Inti Receiving

### GR-01 Receiving line tunggal valid

Expected:

- GR `Posted`
- qty PO bertambah
- qty shipment bertambah
- status item terupdate

### GR-02 Receiving multi-line per shipment

Expected:

- satu GR terbentuk
- semua line receiving tersimpan

### GR-03 Receiving melebihi sisa shipment saat over-receipt off

Expected:

- aksi ditolak

### GR-04 Receiving melebihi outstanding PO saat over-receipt off

Expected:

- aksi ditolak

### GR-05 Receiving saat shipment belum `Shipped`

Expected:

- aksi ditolak

### GR-06 Receiving saat PO sudah final

Expected:

- aksi ditolak

### GR-07 Receiving item cancelled

Expected:

- aksi ditolak

### GR-08 Receiving penuh sampai line complete

Expected:

- item `Closed`
- shipment bisa menjadi `Received`

### GR-09 Receiving parsial

Expected:

- item `Partial`
- shipment `Partial Received`

### GR-10 Cancel GR posted

Expected:

- status GR `Cancelled`
- qty kembali
- status shipment dan PO refresh

### GR-11 Cancel GR yang bukan `Posted`

Expected:

- aksi ditolak

## Skenario Monitoring

### MON-01 Header PO menjadi Open saat sebagian item sudah punya ETD

Expected:

- PO `Open`

### MON-02 Header PO menjadi Late saat ada item overdue

Expected:

- PO `Late`

### MON-03 Header PO menjadi Closed saat semua item aktif final

Expected:

- PO `Closed`

### MON-04 ETA header mengikuti item aktif terdekat

Expected:

- `eta_date` header sinkron

### MON-05 Traceability menampilkan first dan last receipt

Expected:

- tanggal receipt pertama dan terakhir tampil benar

### MON-06 Detail PO menampilkan tracking shipment dan GR

Expected:

- semua shipment dan GR terkait item terlihat

## Skenario Role dan Security

### SEC-01 Supervisor tidak bisa akses receiving

Expected:

- `403`

### SEC-02 Staff tidak bisa akses settings

Expected:

- ditolak

### SEC-03 User inactive tidak bisa login

Expected:

- login gagal

### SEC-04 Admin reset password tanpa request pending

Expected:

- aksi ditolak

### SEC-05 Admin reset password dengan request pending

Expected:

- password berubah
- request menjadi `processed`

## Skenario Master Data

### MD-01 Supplier code otomatis uppercase

Expected:

- kode tersimpan uppercase

### MD-02 Item code otomatis uppercase

Expected:

- kode tersimpan uppercase

### MD-03 Supplier code duplikat

Expected:

- aksi ditolak

### MD-04 Item code duplikat

Expected:

- aksi ditolak

### MD-05 Toggle status supplier

Expected:

- status berubah tanpa menghapus data

### MD-06 Toggle status item

Expected:

- status berubah tanpa menghapus data

## Prioritas Test Saat Refactor

Jika waktu terbatas, minimal pastikan skenario berikut aman:

1. `PO-01`
2. `PO-03`
3. `PO-06`
4. `PO-08`
5. `SHP-01`
6. `SHP-04`
7. `SHP-10`
8. `GR-01`
9. `GR-09`
10. `GR-10`
11. `MON-02`
12. `SEC-01`

## Penutup

Dokumen ini sebaiknya dipakai sebagai checklist saat:

- code review
- QA regression
- refactor besar
- penambahan flow baru
