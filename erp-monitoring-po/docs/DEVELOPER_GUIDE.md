# Developer Guide

Dokumen ini adalah panduan teknis untuk developer yang akan memelihara, merefactor, atau menambah fitur pada project ERP Monitoring PO, Shipment, dan Receiving.

Dokumen acuan utama:

- [README.md](../README.md)
- [SOP_OPERASIONAL.md](./SOP_OPERASIONAL.md)
- [STATUS_DICTIONARY.md](./STATUS_DICTIONARY.md)
- [GAP_ANALYSIS.md](./GAP_ANALYSIS.md)
- [ACTION_PLAN.md](./ACTION_PLAN.md)

## Tujuan

Guide ini dibuat untuk:

- mempercepat onboarding developer baru
- mengurangi perubahan yang merusak flow bisnis
- memberi aturan kerja saat refactor besar
- menyatukan pola perubahan kode, test, dan dokumentasi

## Prinsip Utama

### 1. README adalah kontrak perilaku sistem

Jika implementasi diubah, dan perubahan itu memengaruhi flow bisnis, maka:

- `README.md` harus diperbarui
- `SOP_OPERASIONAL.md` harus diperbarui jika user-facing flow berubah
- `STATUS_DICTIONARY.md` harus diperbarui jika definisi status berubah

### 2. Status internal tidak boleh diubah sembarangan

Status seperti:

- `PO Issued`
- `Open`
- `Late`
- `Closed`
- `Cancelled`
- `Waiting`
- `Confirmed`
- `Partial`
- `Force Closed`
- `Draft`
- `Shipped`
- `Partial Received`
- `Received`
- `Posted`

adalah bagian inti domain. Perubahan status harus dianggap sebagai perubahan bisnis, bukan sekadar perubahan teknis.

### 3. Refactor tidak boleh mengubah perilaku tanpa sengaja

Saat refactor:

- pertahankan output bisnis
- pertahankan aturan validasi
- pertahankan transisi status
- tambahkan test sebelum mengubah area sensitif

## Struktur Project Saat Ini

Folder penting:

```text
app/
  Actions/
  Http/
    Controllers/
    Middleware/
    Requests/
  Imports/
  Exports/
  Models/
  Providers/
  Support/

database/
  migrations/
  seeders/

resources/
  views/

routes/
  web.php
  auth.php

tests/
  Feature/
```

## Domain Utama

Domain inti sistem:

1. master data
2. purchase order
3. shipment
4. goods receipt
5. monitoring dan reporting
6. authentication dan user management

## Area Paling Sensitif

Developer harus ekstra hati-hati saat mengubah:

- kalkulasi `received_qty`
- kalkulasi `outstanding_qty`
- penentuan status item
- penentuan status header PO
- reverse saat cancel GR
- validasi duplikasi delivery note dan invoice
- aturan over-receipt

Jika menyentuh salah satu area di atas:

- baca test yang relevan lebih dulu
- tambahkan test baru jika coverage belum cukup

## Konvensi Status

Gunakan arsitektur status resmi berikut:

- `App\Support\DomainStatus` untuk internal code yang stabil
- `App\Support\DocumentTermCodes` untuk legacy term yang masih dipakai selama masa transisi
- `App\Support\TermCatalog` atau `App\Support\DocumentTermStatus` untuk label dan badge UI

Jangan:

- menulis string status bebas di banyak tempat
- membuat variasi kapitalisasi baru
- membuat label baru tanpa memperbarui dokumentasi dan seeder

Gunakan:

- `DomainStatus` untuk source of truth baru di domain logic
- `DocumentTermCodes` hanya untuk kompatibilitas transisi
- `TermCatalog` atau `DocumentTermStatus` untuk label dan badge

### Kenapa `document_terms` tidak langsung dihubungkan FK ke tabel transaksi

Saat ini `document_terms` tidak diposisikan sebagai parent table transaksi, karena:

- row di `document_terms` bisa diubah admin untuk kebutuhan display
- transaksi butuh identitas status yang stabil dan tidak mudah berubah
- satu grup term melayani banyak konteks UI, bukan murni state machine domain

Pola yang sedang dipakai sekarang:

- transaksi menyimpan `status_code` atau `item_status_code`
- `document_terms.internal_code` menjadi katalog display berdasarkan code tersebut
- join ke `document_terms` dilakukan lewat kombinasi:
  - `group_key`
  - `internal_code`

Kalau suatu saat ingin FK penuh, jalur yang lebih aman adalah:

- buat master status domain yang immutable, atau
- pecah katalog per domain status

Jangan langsung menjadikan row editable di `document_terms` sebagai FK utama transaksi.

## Konvensi Pengembangan Flow Baru

Jika menambah flow baru:

1. tentukan apakah flow itu transaksi atau report
2. tentukan status yang terdampak
3. tentukan audit yang perlu dicatat
4. tentukan dokumen mana yang perlu diperbarui
5. buat atau update test

Checklist minimal:

- route
- controller
- validation
- transaction rule
- audit
- UI
- test
- dokumentasi

## Pola Perubahan Yang Disarankan

### Untuk perubahan validasi

Disarankan pindah ke `FormRequest` jika validasi sudah panjang atau reuse.

### Untuk perubahan transaksi

Disarankan pindah ke:

- `Action`
- `Service`

Contoh area yang cocok:

- create shipment
- mark shipped
- store receiving
- cancel GR
- force close item

### Untuk perubahan report

Disarankan pindah ke:

- `Query`
- `Report` class

Contoh area yang cocok:

- traceability
- dashboard
- summary report

## Pola Refactor Yang Aman

Urutan refactor yang aman:

1. dokumentasikan perilaku yang ada
2. tambahkan atau pastikan test
3. ekstrak helper/query/service
4. jalankan test
5. verifikasi UI

Jangan melakukan:

- refactor besar sambil ubah definisi status
- refactor besar sambil tambah banyak fitur baru

## Panduan Per Modul

## Purchase Order

Perhatikan area:

- create PO
- update ETD item
- cancel item
- force close item
- cancel header PO
- detail tracking

Hal yang wajib tetap benar:

- status item
- status header
- histori status
- sinkronisasi ETA header

## Shipment

Perhatikan area:

- kandidat item yang masih bisa dikirim
- supplier tunggal dalam satu shipment
- unique delivery note
- unique invoice
- draft edit
- mark shipped
- cancel draft

Hal yang wajib tetap benar:

- `available_to_ship_qty`
- line shipment tidak melebihi batas
- shipment status transition

## Goods Receipt

Perhatikan area:

- receiving process per shipment
- receiving single line
- over-receipt setting
- attachment
- cancel GR

Hal yang wajib tetap benar:

- update qty PO item
- update qty shipment item
- refresh shipment status
- refresh PO status
- reverse qty saat cancel

## Monitoring dan Report

Perhatikan area:

- dashboard
- summary PO
- summary item
- traceability
- detail PO tracking

Hal yang wajib tetap benar:

- hasil report harus konsisten dengan transaksi
- status monitoring tidak boleh bertentangan dengan state dokumen

## Panduan Dokumentasi

Jika ada perubahan:

### Update README ketika

- flow bisnis berubah
- status berubah
- modul aktif berubah
- aturan proses berubah

### Update SOP ketika

- langkah kerja user berubah
- menu berubah
- hak akses berubah
- proses input berubah

### Update Status Dictionary ketika

- definisi status berubah
- status baru ditambahkan
- final state berubah

## Panduan Test

Minimal lakukan salah satu:

- update test existing
- tambah feature test baru

Area yang sebaiknya selalu punya test:

- create PO
- update ETD
- create shipment
- mark shipped
- receiving
- cancel GR
- force close
- duplicate document number rules

Jika bug ditemukan, usahakan:

1. tulis test yang mereproduksi bug
2. perbaiki implementasi
3. pastikan test lolos

## Konvensi Commit

Urutan commit yang disarankan:

1. test atau dokumentasi baseline
2. refactor internal
3. perubahan perilaku jika memang disengaja
4. update dokumen akhir

Contoh commit yang bagus:

- `test: cover duplicate delivery note on active shipment`
- `refactor: extract traceability query from controller`
- `docs: update SOP for receiving reversal`

## Definisi Selesai

Sebuah perubahan dianggap selesai jika:

- kode sudah berubah
- test relevan tersedia atau diperbarui
- dokumentasi relevan diperbarui
- tidak ada istilah status baru yang liar
- flow utama tetap konsisten dengan README

## Anti-Pattern Yang Harus Dihindari

- menulis ulang string status berkali-kali
- membuat query report kompleks langsung di blade
- mencampur validasi, query report, dan transaksi berat dalam satu method panjang
- mengubah flow bisnis tanpa update dokumentasi
- menambah fitur tanpa memikirkan audit dan traceability

## Target Arsitektur Jangka Menengah

Struktur target yang disarankan:

```text
app/
  Actions/
  Services/
  Queries/
  Http/Requests/
  Support/
    Status/
    Audit/
    Timeline/
```

Tujuan:

- controller lebih tipis
- transaction logic terisolasi
- report query reusable
- aturan domain lebih mudah diuji

## Prioritas Teknis Yang Direkomendasikan

Jika developer baru masuk dan harus memilih pekerjaan, urutan terbaik:

1. baca `README.md`
2. baca `STATUS_DICTIONARY.md`
3. baca feature test utama
4. mulai dari refactor `TraceabilityController`
5. lanjut ke resolver status item dan PO

## Penutup

Project ini sudah kuat secara domain. Tugas developer berikutnya adalah menjaga agar pertumbuhan kode tetap tertib.

Prinsip sederhananya:

- satu sumber kebenaran untuk flow
- satu definisi untuk status
- satu perubahan, update test dan dokumentasi juga
