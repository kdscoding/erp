# Status Dictionary

Dokumen ini adalah kamus status resmi sistem. Gunakan dokumen ini untuk menyamakan pemahaman antara developer, QA, admin, staff, dan supervisor.

## Aturan Umum

- `code` adalah status internal sistem
- label tampilan bisa berbeda karena diatur di `document_terms`
- logika bisnis mengikuti `code`, bukan label display

## Purchase Order Header

### `PO Issued`

Makna:

- PO sudah dibuat
- item aktif masih menunggu progres
- belum ada kondisi operasional yang mendorong header ke status lain

Kondisi umum:

- item aktif masih `Waiting`

Sifat:

- bukan final

### `Open`

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

### `Late`

Makna:

- ada item aktif outstanding dengan ETD yang sudah lewat

Sifat:

- bukan final

### `Closed`

Makna:

- seluruh item aktif sudah selesai
- outstanding item aktif sudah `0`

Catatan:

- item `Force Closed` ikut dianggap final

Sifat:

- final

### `Cancelled`

Makna:

- header PO dibatalkan
- atau semua item menjadi batal

Sifat:

- final

## Purchase Order Item

### `Waiting`

Makna:

- item aktif
- belum ada receipt
- ETD belum diisi

Sifat:

- bukan final

### `Confirmed`

Makna:

- ETD sudah diisi
- belum ada receipt

Sifat:

- bukan final

### `Late`

Makna:

- item masih outstanding
- ETD terisi
- ETD sudah lewat

Catatan:

- ini status monitoring

Sifat:

- bukan final

### `Partial`

Makna:

- item sudah diterima sebagian
- outstanding masih ada

Sifat:

- bukan final

### `Closed`

Makna:

- qty item sudah terpenuhi
- outstanding `0`

Sifat:

- final

### `Force Closed`

Makna:

- item dihentikan manual
- outstanding dipaksa menjadi `0`
- tidak harus berarti qty terpenuhi secara fisik

Sifat:

- final

### `Cancelled`

Makna:

- item dibatalkan
- tidak dilanjutkan ke receiving

Sifat:

- final

## Shipment

### `Draft`

Makna:

- shipment masih berupa draft
- belum resmi menjadi dokumen kirim operasional

Sifat:

- bukan final

### `Shipped`

Makna:

- draft shipment sudah dikonfirmasi
- siap diproses receiving

Sifat:

- bukan final

### `Partial Received`

Makna:

- sebagian qty atau line shipment sudah diterima
- shipment belum complete

Sifat:

- bukan final

### `Received`

Makna:

- semua line shipment sudah complete

Sifat:

- final

### `Cancelled`

Makna:

- draft shipment dibatalkan

Sifat:

- final

## Goods Receipt

### `Posted`

Makna:

- receiving valid dan sudah memengaruhi qty transaksi

Sifat:

- bukan final mutlak karena masih bisa dibatalkan

### `Cancelled`

Makna:

- GR dibatalkan
- qty receiving dikembalikan

Sifat:

- final

## Trigger Umum Perubahan Status

## PO Header

- create PO -> `PO Issued`
- ada progres item/ETD/shipment/partial -> `Open`
- ada item overdue -> `Late`
- semua item aktif final -> `Closed`
- cancel header atau semua item batal -> `Cancelled`

## PO Item

- create line item -> `Waiting`
- ETD diisi -> `Confirmed`
- ETD lewat dan outstanding masih ada -> `Late`
- receiving sebagian -> `Partial`
- qty terpenuhi -> `Closed`
- force close manual -> `Force Closed`
- cancel item -> `Cancelled`

## Shipment

- create shipment -> `Draft`
- mark shipped -> `Shipped`
- receiving sebagian -> `Partial Received`
- receiving penuh -> `Received`
- cancel draft -> `Cancelled`

## Goods Receipt

- posting receiving -> `Posted`
- cancel receiving -> `Cancelled`

## Status Final dan Non-Final

### Final

- PO:
  - `Closed`
  - `Cancelled`
- PO Item:
  - `Closed`
  - `Force Closed`
  - `Cancelled`
- Shipment:
  - `Received`
  - `Cancelled`
- GR:
  - `Cancelled`

### Non-Final

- PO:
  - `PO Issued`
  - `Open`
  - `Late`
- PO Item:
  - `Waiting`
  - `Confirmed`
  - `Late`
  - `Partial`
- Shipment:
  - `Draft`
  - `Shipped`
  - `Partial Received`
- GR:
  - `Posted`

## Catatan Penting

- status PO header adalah status monitoring operasional
- status item lebih penting untuk membaca kondisi aktual line item
- jangan mengubah arti status tanpa update:
  - kode
  - test
  - README
  - SOP
