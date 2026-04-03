# Action Plan Pengembangan Lanjutan

Dokumen ini adalah turunan praktis dari [GAP_ANALYSIS.md](./GAP_ANALYSIS.md) dan [README.md](../README.md). Fokusnya adalah apa yang perlu dilakukan secara bertahap agar pengembangan lebih terarah.

## Tujuan

Action plan ini disusun untuk membantu tim memilih pekerjaan yang:

- paling aman dilakukan lebih dulu
- paling berdampak pada stabilitas sistem
- paling membantu refactor besar ke depan

## Prioritas Umum

Urutan prioritas yang disarankan:

1. dokumentasi operasional
2. test dan guardrail
3. refactor struktur kode
4. traceability dan audit trail
5. analitik tambahan

## Apa Yang Harus Dikerjakan Minggu Ini

### 1. Buat dokumen SOP operasional

File yang disarankan:

- `SOP_OPERASIONAL.md`

Isi minimum:

- langkah kerja administrator
- langkah kerja staff
- langkah kerja supervisor
- cara create PO
- cara update ETD
- cara buat draft shipment
- cara mark shipped
- cara posting receiving
- cara cancel GR
- arti status yang muncul di UI

### 2. Buat kamus status

File yang disarankan:

- `STATUS_DICTIONARY.md`

Isi minimum:

- status PO
- status item
- status shipment
- status GR
- definisi
- trigger perubahan
- final state atau bukan

### 3. Tambah test untuk area paling rawan

Target tambahan:

- traceability row aggregation
- cancel PO pada item campuran
- force close terhadap item yang sudah partial
- receiving saat item sudah final
- reuse nomor dokumen setelah cancel shipment

### 4. Rapikan README jika ada perubahan flow baru

README harus tetap jadi baseline. Jangan biarkan dokumen tertinggal dari kode.

## Apa Yang Harus Dikerjakan Dalam 2-4 Minggu

### 0. Screen Consolidation & Status Normalization

Target:

- putuskan `Monitoring Hub` sebagai pengganti:
  - `summary-po`
  - `summary-item`
  - sebagian isi `monitoring`
- sederhanakan `dashboard` agar kembali menjadi executive overview
- tetapkan status internal sebagai stable code, bukan display string
- reposisikan `document_terms` menjadi katalog display, bukan source of truth state machine

Hasil yang diharapkan:

- user tidak bingung memilih halaman yang mirip
- dashboard tidak terlalu ramai
- status bisnis lebih aman untuk jangka panjang
- perubahan label tampilan tidak lagi bercampur dengan business logic

### 1. Refactor Traceability

Target:

- pindahkan query traceability ke class query terpisah
- rapikan filter
- tambahkan milestone shipment
- siapkan format timeline event

Hasil yang diharapkan:

- halaman traceability lebih akurat
- controller lebih pendek
- lebih mudah dikembangkan jadi timeline lengkap

### 2. Refactor Status Resolver

Target:

- buat resolver untuk status item
- buat resolver untuk status PO
- hilangkan logika status yang berulang di banyak controller dan query

Hasil yang diharapkan:

- definisi status lebih konsisten
- risiko regresi lebih kecil

### 3. Refactor Receiving dan Shipment

Target:

- pisahkan transaction logic ke service/action class
- batasi controller sebagai orchestration layer
- satukan validasi penting di request/service

Hasil yang diharapkan:

- code review lebih mudah
- bug lebih mudah dilokalisasi

### 4. Audit Viewer Dasar

Target:

- halaman list audit log
- filter by module
- filter by actor
- filter by date

Hasil yang diharapkan:

- admin bisa review perubahan tanpa buka database langsung

## Apa Yang Harus Dikerjakan Dalam 1-3 Bulan

### 1. Bangun traceability end-to-end

Konsep target:

- satu timeline per PO atau per item PO
- event dari create sampai reverse
- user dan timestamp tampil jelas

### 2. Tambah report analitik

Target report:

- aging outstanding
- supplier lateness
- PO cycle time
- shipment to receiving lead time
- on-time receipt ratio

### 3. Perkuat authorization

Target:

- pindahkan sebagian kontrol dari middleware role ke policy
- definisikan action sensitif
- audit keputusan user yang sensitif

### 4. Siapkan panduan developer

File yang disarankan:

- `DEVELOPER_GUIDE.md`

Isi minimum:

- struktur folder
- konvensi status
- cara tambah report baru
- cara update flow bisnis
- aturan update dokumentasi dan test

## Saran Berdasarkan Dampak

### Dampak tinggi, effort rendah

- buat SOP operasional
- buat kamus status
- tambah test edge case
- rapikan dokumentasi status di UI

### Dampak tinggi, effort menengah

- screen consolidation monitoring/dashboard
- status normalization
- refactor traceability
- refactor status resolver
- audit viewer dasar

### Dampak tinggi, effort tinggi

- refactor shipment/receiving penuh
- timeline traceability end-to-end
- report analitik lengkap

## Saran Berdasarkan Risiko

### Risiko paling tinggi jika disentuh

- kalkulasi `received_qty`
- kalkulasi `outstanding_qty`
- perubahan status PO header
- reverse GR cancellation
- rules unik delivery note dan invoice

Untuk area ini:

- selalu tambah test dulu sebelum refactor

### Risiko menengah

- dashboard query
- summary report
- export Excel
- detail PO tracking

### Risiko lebih rendah

- dokumentasi
- status dictionary
- SOP
- halaman audit read-only

## Saran Cara Kerja Tim

### Aturan kerja yang disarankan

- satu perubahan flow wajib update:
  - kode
  - test
  - README atau dokumen turunannya
- jangan ubah definisi status diam-diam
- gunakan branch kecil per area kerja
- hindari refactor besar sambil menambah fitur baru pada modul yang sama

### Urutan kerja yang aman

1. dokumentasikan baseline
2. tambah test
3. refactor struktur internal
4. tambah fitur baru

## Rekomendasi File Tambahan Berikutnya

Dokumen yang paling saya sarankan dibuat setelah ini:

1. `SOP_OPERASIONAL.md`
2. `STATUS_DICTIONARY.md`
3. `DEVELOPER_GUIDE.md`
4. `TEST_SCENARIOS.md`

## Rekomendasi Teknis Paling Penting

Jika hanya boleh memilih tiga pekerjaan berikutnya, saya sarankan:

1. buat `SOP_OPERASIONAL.md`
2. refactor `TraceabilityController` menjadi query-based module
3. sentralisasi perhitungan status item dan status PO

## Penutup

Sistem ini sudah cukup kuat secara bisnis. Langkah terbaik berikutnya bukan menambah banyak fitur acak, tetapi mengurangi gap antara:

- perilaku sistem
- dokumentasi
- struktur kode
- kebutuhan audit operasional

Semakin rapat empat hal itu, semakin mudah sistem ini tumbuh tanpa menjadi sulit dirawat.
