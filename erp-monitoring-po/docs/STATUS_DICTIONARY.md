# Status Dictionary

Dokumen ini adalah kamus status resmi sistem. Gunakan dokumen ini untuk menyamakan pemahaman antara developer, QA, admin, staff, dan supervisor.

## Aturan Umum

- `code` adalah status internal sistem
- label tampilan bisa berbeda karena diatur di `document_terms`
- logika bisnis mengikuti `code`, bukan label display

## Catatan Transisi Saat Ini

- data transaksi yang aktif di repo ini masih banyak memakai legacy term seperti `Open`, `Late`, `Draft`, dan `Cancelled`
- arah refactor resmi ke depan adalah memindahkan logika ke stable internal code, lalu memakai `document_terms` hanya untuk display
- mapping transisi ini sekarang dipusatkan di `App\Support\DomainStatus`

Format yang harus dipahami sementara:

- `internal code` = target stabil untuk domain logic
- `legacy term` = nilai string yang masih banyak tersimpan di tabel dan dipakai oleh flow lama
- `document_terms.label` = bahasa tampilan di UI

## Purchase Order Header

### `po_issued`

Legacy term saat ini: `PO Issued`

Makna:

- PO sudah dibuat
- item aktif masih menunggu progres
- belum ada kondisi operasional yang mendorong header ke status lain

Kondisi umum:

- item aktif masih `Waiting`

Sifat:

- bukan final

### `po_open`

Legacy term saat ini: `Open`

Makna:

- PO sedang berjalan
- ada progres seperti ETD, shipment allocation, partial receipt, atau item active yang sudah mulai bergerak

Kondisi umum:

- ada item `Confirmed`
- ada alokasi shipment
- ada item `Partial`
- ada item aktif selesai tetapi header belum final penuh

Sifat:

- bukan final

### `po_late`

Legacy term saat ini: `Late`

Makna:

- ada item aktif outstanding dengan ETD yang sudah lewat

Sifat:

- bukan final

### `po_closed`

Legacy term saat ini: `Closed`

Makna:

- seluruh item aktif sudah selesai
- outstanding item aktif sudah `0`

Catatan:

- item `Force Closed` ikut dianggap final

Sifat:

- final

### `po_cancelled`

Legacy term saat ini: `Cancelled`

Makna:

- header PO dibatalkan
- atau semua item menjadi batal

Sifat:

- final

## Purchase Order Item

### `item_waiting`

Legacy term saat ini: `Waiting`

Makna:

- item aktif
- belum ada receipt
- ETD belum diisi

Sifat:

- bukan final

### `item_confirmed`

Legacy term saat ini: `Confirmed`

Makna:

- ETD sudah diisi
- belum ada receipt

Sifat:

- bukan final

### `item_late`

Legacy term saat ini: `Late`

Makna:

- item masih outstanding
- ETD terisi
- ETD sudah lewat

Catatan:

- ini status monitoring

Sifat:

- bukan final

### `item_partial`

Legacy term saat ini: `Partial`

Makna:

- item sudah diterima sebagian
- outstanding masih ada

Sifat:

- bukan final

### `item_closed`

Legacy term saat ini: `Closed`

Makna:

- qty item sudah terpenuhi
- outstanding `0`

Sifat:

- final

### `item_force_closed`

Legacy term saat ini: `Force Closed`

Makna:

- item dihentikan manual
- outstanding dipaksa menjadi `0`
- tidak harus berarti qty terpenuhi secara fisik

Sifat:

- final

### `item_cancelled`

Legacy term saat ini: `Cancelled`

Makna:

- item dibatalkan
- tidak dilanjutkan ke receiving

Sifat:

- final

## Shipment

### `shipment_draft`

Legacy term saat ini: `Draft`

Makna:

- shipment masih berupa draft
- belum resmi menjadi dokumen kirim operasional

Sifat:

- bukan final

### `shipment_shipped`

Legacy term saat ini: `Shipped`

Makna:

- draft shipment sudah dikonfirmasi
- siap diproses receiving

Sifat:

- bukan final

### `shipment_partial_received`

Legacy term saat ini: `Partial Received`

Makna:

- sebagian qty atau line shipment sudah diterima
- shipment belum complete

Sifat:

- bukan final

### `shipment_received`

Legacy term saat ini: `Received`

Makna:

- semua line shipment sudah complete

Sifat:

- final

### `shipment_cancelled`

Legacy term saat ini: `Cancelled`

Makna:

- draft shipment dibatalkan

Sifat:

- final

## Goods Receipt

### `gr_posted`

Legacy term saat ini: `Posted`

Makna:

- receiving valid dan sudah memengaruhi qty transaksi

Sifat:

- bukan final mutlak karena masih bisa dibatalkan

### `gr_cancelled`

Legacy term saat ini: `Cancelled`

Makna:

- GR dibatalkan
- qty receiving dikembalikan

Sifat:

- final

## Trigger Umum Perubahan Status

## PO Header

- create PO -> `po_issued`
- ada progres item/ETD/shipment/partial -> `po_open`
- ada item overdue -> `po_late`
- semua item aktif final -> `po_closed`
- cancel header atau semua item batal -> `po_cancelled`

## PO Item

- create line item -> `item_waiting`
- ETD diisi -> `item_confirmed`
- ETD lewat dan outstanding masih ada -> `item_late`
- receiving sebagian -> `item_partial`
- qty terpenuhi -> `item_closed`
- force close manual -> `item_force_closed`
- cancel item -> `item_cancelled`

## Shipment

- create shipment -> `shipment_draft`
- mark shipped -> `shipment_shipped`
- receiving sebagian -> `shipment_partial_received`
- receiving penuh -> `shipment_received`
- cancel draft -> `shipment_cancelled`

## Goods Receipt

- posting receiving -> `gr_posted`
- cancel receiving -> `gr_cancelled`

## Status Final dan Non-Final

### Final

- PO:
  - `po_closed`
  - `po_cancelled`
- PO Item:
  - `item_closed`
  - `item_force_closed`
  - `item_cancelled`
- Shipment:
  - `shipment_received`
  - `shipment_cancelled`
- GR:
  - `gr_cancelled`

### Non-Final

- PO:
  - `po_issued`
  - `po_open`
  - `po_late`
- PO Item:
  - `item_waiting`
  - `item_confirmed`
  - `item_late`
  - `item_partial`
- Shipment:
  - `shipment_draft`
  - `shipment_shipped`
  - `shipment_partial_received`
- GR:
  - `gr_posted`

## Catatan Penting

- status PO header adalah status monitoring operasional
- status item lebih penting untuk membaca kondisi aktual line item
- selama migrasi, legacy term masih boleh muncul di tabel lama, badge, atau filter lama
- internal code harus diperlakukan sebagai target source of truth baru
- jangan mengubah arti status tanpa update:
  - kode
  - test
  - README
  - SOP
