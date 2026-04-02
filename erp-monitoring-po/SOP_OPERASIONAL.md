# SOP Operasional

Dokumen ini adalah turunan operasional dari [README.md](d:/server/laragon/www/erp/erp-monitoring-po/README.md). Fokusnya adalah cara menggunakan sistem sehari-hari berdasarkan role dan menu.

## Tujuan

SOP ini dipakai untuk:

- panduan kerja user operasional
- referensi onboarding user baru
- penyamaan pemahaman antar admin, staff, dan supervisor

## Role dan Kewenangan

### Administrator

Boleh mengakses:

- dashboard
- summary report
- traceability
- purchase order
- shipment
- receiving
- master data
- settings
- user management

### Staff

Boleh mengakses:

- dashboard
- summary report
- traceability
- purchase order
- shipment
- receiving
- master data

Tidak boleh mengakses:

- settings
- user management

### Supervisor

Boleh mengakses:

- dashboard
- summary report
- traceability
- monitoring PO
- detail PO

Tidak boleh mengakses:

- master data operasional
- create/update shipment
- proses receiving
- settings
- user management

## Alur Kerja Harian Ringkas

Urutan kerja umum:

1. pastikan master data tersedia
2. buat PO jika ada kebutuhan pembelian
3. update ETD item saat supplier sudah konfirmasi
4. susun draft shipment saat supplier mengirim
5. konfirmasi shipment menjadi `Shipped`
6. proses receiving saat barang fisik tiba
7. monitor status dan outstanding melalui dashboard, summary, detail PO, dan traceability

## SOP Login

1. buka halaman login
2. masukkan `NIK`
3. masukkan password
4. klik masuk

Catatan:

- user harus aktif
- jika gagal login, cek NIK, password, dan status user

## SOP Lupa Password

1. buka menu lupa password
2. isi `NIK`
3. isi catatan permintaan reset
4. submit

Setelah itu:

- sistem menyimpan request reset
- administrator akan memproses dari menu user management

## SOP Master Data

## Supplier

### Menambah supplier

1. buka menu `Suppliers`
2. isi kode supplier
3. isi nama supplier
4. lengkapi kontak bila perlu
5. simpan

Catatan:

- kode supplier otomatis dinormalisasi ke huruf besar
- kode supplier tidak boleh duplikat

### Mengubah supplier

1. buka daftar supplier
2. pilih edit
3. ubah data yang diperlukan
4. simpan

### Menonaktifkan supplier

1. buka daftar supplier
2. ubah status supplier
3. gunakan nonaktif jika supplier tidak dipakai sementara

## Item Category

### Menambah kategori

1. buka menu `Item Categories`
2. isi kode kategori
3. isi nama kategori
4. isi deskripsi bila perlu
5. simpan

## Item

### Menambah item

1. buka menu `Items`
2. isi kode item
3. isi nama item
4. pilih kategori jika tersedia
5. pilih unit jika tersedia
6. isi spesifikasi bila perlu
7. simpan

Catatan:

- kode item otomatis dinormalisasi ke huruf besar
- kode item tidak boleh duplikat

## Unit, Warehouse, Plant

Gunakan menu masing-masing untuk:

- tambah data baru
- edit data lama
- menjaga referensi transaksi tetap konsisten

## SOP Purchase Order

## Membuat PO

1. buka menu `PO`
2. pilih `Create`
3. isi:
   - nomor PO jika ada
   - tanggal PO
   - supplier
   - catatan bila perlu
4. tambahkan item satu per satu
5. isi qty order
6. isi harga jika diperlukan
7. simpan

Hasil:

- jika nomor PO kosong, sistem generate otomatis
- status header menjadi `PO Issued`
- semua item mulai dari status `Waiting`

## Update ETD Item

Gunakan saat supplier sudah memberi jadwal.

1. buka detail PO
2. cari item yang akan diupdate
3. isi ETD
4. simpan

Hasil:

- item akan berubah ke `Confirmed` jika belum ada receiving
- jika ETD sudah lewat dan item masih outstanding, monitoring dapat menandai item `Late`
- header PO akan dihitung ulang otomatis

## Cancel Item

Gunakan jika item benar-benar dibatalkan sebelum ada penerimaan.

1. buka detail PO
2. pilih item
3. klik `Cancel`
4. isi alasan pembatalan
5. simpan

Syarat:

- item belum pernah diterima
- PO belum final

## Force Close Item

Gunakan jika sisa outstanding tidak akan dilanjutkan, tetapi item tidak murni dibatalkan.

1. buka detail PO
2. pilih item
3. klik `Force Close`
4. isi alasan
5. simpan

Hasil:

- item menjadi `Force Closed`
- outstanding item menjadi `0`

## Cancel PO

Gunakan hanya jika dokumen header PO memang harus dihentikan.

1. buka detail PO
2. klik `Batalkan PO`
3. isi alasan pembatalan
4. simpan

Syarat:

- PO belum `Closed`
- PO belum `Cancelled`

## SOP Shipment

## Membuat Draft Shipment

Gunakan saat supplier mengirim dokumen pengiriman dan item siap disusun ke shipment.

1. buka menu `Shipment`
2. pilih `Create Draft`
3. cari item kandidat berdasarkan supplier, item, atau PO
4. pilih item yang akan dikirim
5. isi qty kirim per item
6. isi data dokumen:
   - shipment date
   - delivery note number
   - invoice number jika ada
   - invoice date jika ada
   - invoice currency jika ada
   - remark jika ada
7. simpan draft

Aturan penting:

- semua item harus dari supplier yang sama
- delivery note tidak boleh dipakai ulang pada shipment aktif supplier yang sama
- invoice number tidak boleh dipakai ulang pada shipment aktif supplier yang sama
- qty kirim tidak boleh melebihi sisa qty yang bisa dialokasikan

## Edit Draft Shipment

1. buka worklist shipment
2. pilih draft
3. klik edit
4. ubah header atau line yang perlu diperbaiki
5. simpan

Catatan:

- draft minimal harus tetap punya satu item

## Mark Shipped

Gunakan saat draft benar-benar menjadi dokumen kirim resmi.

1. buka worklist shipment
2. pilih draft
3. klik `Mark Shipped`

Hasil:

- status shipment menjadi `Shipped`
- user diarahkan ke halaman receiving

## Cancel Draft Shipment

Gunakan jika draft batal diproses.

1. buka draft shipment
2. klik `Cancel Draft`

Hasil:

- status menjadi `Cancelled`
- dokumen tidak dihapus

## SOP Receiving

## Process Receiving Per Shipment

1. buka menu `Receiving`
2. pilih shipment dari worklist
3. isi:
   - tanggal terima
   - nomor dokumen receiving
   - catatan bila perlu
   - attachment bila perlu
4. isi qty diterima untuk line yang benar-benar datang
5. simpan

Aturan:

- isi minimal satu qty terima
- qty terima tidak boleh melebihi sisa kiriman jika over-receipt tidak diizinkan
- qty terima tidak boleh melebihi outstanding PO jika over-receipt tidak diizinkan

Hasil:

- sistem membuat goods receipt
- qty PO dan shipment diperbarui
- status item, shipment, dan PO dihitung ulang

## Receiving History

Gunakan untuk melihat GR yang sudah diposting.

1. buka menu `Receiving History`
2. cari dokumen berdasarkan nomor dokumen bila perlu
3. buka detail GR

## Cancel Goods Receipt

Gunakan jika GR salah posting.

1. buka detail GR dari receiving history
2. klik aksi cancel
3. isi alasan pembatalan
4. simpan

Hasil:

- status GR menjadi `Cancelled`
- qty receiving dikembalikan
- status shipment dan PO dihitung ulang

## SOP Monitoring

## Dashboard

Gunakan dashboard untuk:

- melihat open PO
- melihat late PO
- melihat shipment hari ini
- melihat receiving hari ini
- memantau supplier berisiko
- memantau item at-risk dan on-time

Langkah kerja:

1. buka dashboard
2. filter supplier jika perlu
3. filter range tanggal PO jika perlu
4. gunakan detail modal untuk membaca prioritas

## Summary Outstanding PO

Gunakan untuk melihat outstanding per header PO.

Kapan dipakai:

- review meeting
- follow up supplier
- monitoring dokumen aktif

## Summary Outstanding Item

Gunakan untuk melihat item mana yang outstanding paling besar.

Kapan dipakai:

- follow up item prioritas
- identifikasi item bottleneck

## Detail PO

Gunakan halaman ini sebagai sumber utama status per item.

Di halaman ini user dapat:

- melihat header PO
- melihat ringkasan item
- melihat histori status
- update ETD
- cancel item
- force close item
- cancel header PO
- melihat tracking shipment dan GR per item

## Traceability

Gunakan traceability untuk membaca ringkasan perjalanan item PO sampai receiving.

Langkah:

1. buka menu `Traceability`
2. cari berdasarkan nomor PO
3. baca:
   - tanggal PO
   - supplier
   - item
   - ETD
   - received vs ordered
   - first receipt
   - last receipt

Catatan:

- untuk tracking yang lebih detail, gunakan halaman detail PO

## SOP Settings

## General Settings

Administrator dapat mengubah:

- `allow_over_receipt`

Gunakan hati-hati:

- aktifkan hanya jika bisnis mengizinkan penerimaan lebih dari outstanding atau lebih dari qty kirim

## Document Terms

Administrator dapat mengubah:

- label display status
- urutan tampil
- aktif/nonaktif term
- badge color

Catatan penting:

- ini tidak mengubah kode internal status
- ini hanya mengubah label tampilan

## SOP User Management

## Menambah user

1. buka menu `Daftar User`
2. pilih tambah user
3. isi:
   - nama
   - NIK
   - email
   - role
   - password
4. simpan

## Mengubah user

1. buka menu user
2. pilih edit
3. ubah data
4. simpan

## Reset password user

1. buka user yang bersangkutan
2. pastikan ada request reset password `pending`
3. isi password baru
4. isi catatan admin
5. simpan

## Aktifkan atau nonaktifkan user

1. buka detail user
2. ubah status user

Catatan:

- user nonaktif tidak bisa login

## Daftar Pemeriksaan Harian

Untuk staff/admin operasional:

1. cek dashboard
2. cek item `Late`
3. cek shipment `Draft` yang belum diproses
4. cek shipment `Shipped` yang belum diterima penuh
5. cek receiving hari ini
6. cek PO outstanding prioritas

Untuk supervisor:

1. cek dashboard
2. cek late PO
3. cek supplier paling perlu follow up
4. cek summary outstanding PO
5. cek traceability untuk kasus yang perlu investigasi

## Hal Yang Harus Dihindari

- jangan force close tanpa alasan yang jelas
- jangan cancel GR tanpa verifikasi qty dan dokumen
- jangan menggunakan label display sebagai acuan logika sistem
- jangan mengandalkan traceability page saja untuk investigasi detail; cek detail PO juga

## Penutup

SOP ini adalah panduan operasional. Jika flow sistem berubah, update SOP ini bersamaan dengan:

- kode
- test
- README
