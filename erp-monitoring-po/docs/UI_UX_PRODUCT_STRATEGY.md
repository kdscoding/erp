# UI/UX & Product Strategy
## ERP Monitoring PO, Shipment, dan Receiving

Dokumen ini sudah diperbarui berdasarkan audit repo per 2026-04-03. Tujuannya bukan lagi sekadar ide visual, tetapi menjadi panduan keputusan besar untuk merapikan struktur layar, menyederhanakan alur sistem, dan memisahkan dengan jelas antara state bisnis, term tampilan, dan halaman reporting.

Dokumen ini harus dibaca bersama:

- [README.md](../README.md) sebagai baseline perilaku sistem aktif
- [GAP_ANALYSIS.md](./GAP_ANALYSIS.md) sebagai ringkasan gap arsitektur
- [ACTION_PLAN.md](./ACTION_PLAN.md) sebagai urutan kerja refactor
- [STATUS_DICTIONARY.md](./STATUS_DICTIONARY.md) sebagai definisi status resmi yang sedang berjalan

---

## 1. Ringkasan Audit Repo

### 1.1 Masalah utama yang terlihat saat ini

1. **Terlalu banyak halaman monitoring/report yang isinya tumpang tindih**
   - `dashboard.blade.php`
   - `monitoring.blade.php`
   - `summary-po.blade.php`
   - `summary-item.blade.php`
   - `traceability/index.blade.php`
   - `supplier-performance.blade.php`
   - `audit/index.blade.php`

2. **Dashboard terlalu ramai**
   - dashboard saat ini memuat KPI, chart, health panel, supplier panel, action center, receiving terbaru, saved views, dan shortcut ke laporan lain
   - satu halaman mencoba menjadi executive dashboard, tactical dashboard, dan work queue sekaligus

3. **Ada blade yang secara fungsional menampilkan isi yang hampir sama**
   - `monitoring.blade.php` berisi summary per PO dan detail item outstanding dalam satu layar
   - `summary-po.blade.php` mengulang bagian summary per PO dengan filter dan chip yang sangat mirip
   - `summary-item.blade.php` mengulang bagian item outstanding dengan filter dan chip yang sangat mirip
   - akibatnya user sulit tahu halaman mana yang “utama”

4. **Struktur navigation belum mencerminkan mental model user**
   - ada menu `Dashboard`, `Monitoring PO`, `Summary PO`, `Summary Item`, `Traceability`, `Supplier Performance`, `Audit Viewer`
   - bagi user operasional, ini terasa seperti banyak layar laporan yang berdekatan tetapi tanpa hirarki yang tegas

5. **Status bisnis masih string literal**
   - `DocumentTermCodes` saat ini masih menyimpan nilai seperti `'Open'`, `'Late'`, `'Draft'`, `'Cancelled'`
   - artinya code internal dan label tampilan masih identik
   - ini belum benar-benar memisahkan domain code vs display term

6. **`document_terms` belum menjadi source of truth state machine**
   - `document_terms` saat ini berguna untuk:
     - label tampilan
     - badge class
     - sort order
     - opsi status di UI tertentu
   - `document_terms` saat ini belum berguna untuk:
     - menentukan transisi status
     - memvalidasi state machine
     - menyimpan stable internal status key
   - jadi kebingungan “kalau status masih string, gunanya term apa?” memang valid

7. **Layout detail vs list belum konsisten**
   - sebagian halaman memakai ringkasan + tabel
   - sebagian halaman memakai worklist + form builder di halaman yang sama
   - sebagian halaman memakai modal besar untuk detail
   - belum ada pola screen yang benar-benar konsisten

---

## 2. Keputusan Strategis Baru

### 2.1 Prinsip utama

1. **Kurangi jumlah layar, bukan tambah laporan baru terus-menerus**
2. **Pisahkan dengan jelas layar executive, layar monitoring, dan layar eksekusi**
3. **Jadikan satu layar satu tujuan utama**
4. **Status bisnis harus menjadi stable code, bukan label tampilan**
5. **`document_terms` dipakai untuk presentasi dan katalog istilah, bukan pengganti domain state**

### 2.2 Target besar

Sistem ini membutuhkan refactor besar, tetapi refactor tersebut harus diarahkan ke tiga hasil utama:

1. **screen consolidation**
2. **domain status normalization**
3. **workflow-first navigation**

---

## 3. Screen Consolidation Plan

### 3.1 Masalah layar saat ini

Halaman-halaman berikut perlu dipandang sebagai satu kelompok yang terlalu overlap:

- `dashboard`
- `monitoring`
- `summary-po`
- `summary-item`
- `traceability`
- `supplier-performance`

### 3.2 Target struktur layar baru

#### A. Dashboard

Fungsi:

- hanya untuk supervisor / executive overview
- hanya menjawab pertanyaan:
  - apa yang paling berisiko hari ini?
  - supplier mana yang paling bermasalah?
  - task mana yang perlu ditindak sekarang?

Dashboard **tidak boleh** menjadi tempat melihat semua tabel detail.

Isi yang dipertahankan:

- KPI utama
- top delayed suppliers
- action center
- saved views

Isi yang harus dipindahkan keluar dashboard:

- tabel detail yang panjang
- ringkasan PO penuh
- list item outstanding penuh
- traceability table

#### B. Monitoring Hub

Fungsi:

- menjadi layar utama untuk monitoring operasional
- menggantikan overlap antara:
  - `monitoring`
  - `summary-po`
  - `summary-item`

Target:

- satu halaman dengan mode/tab:
  - `PO View`
  - `Item View`
- filter tetap satu
- summary chip tetap satu
- data table berubah sesuai tab, bukan halaman terpisah

Konsekuensi:

- `summary-po` dan `summary-item` sebaiknya dihapus sebagai halaman mandiri setelah Monitoring Hub stabil
- export tetap boleh dipisah, tetapi UI user tidak perlu melihat tiga halaman yang mirip

#### C. Traceability

Fungsi:

- bukan sekadar tabel reporting
- harus menjadi timeline/detail drill-down

Target:

- traceability fokus pada satu PO atau satu item
- bukan menjadi tabel agregasi kedua yang bersaing dengan monitoring
- idealnya split view:
  - kiri: hasil pencarian/list
  - kanan: timeline event lengkap

#### D. Execution Screens

Fungsi:

- PO detail
- shipment draft builder
- receiving process

Target:

- layar ini harus fokus ke aksi
- jangan dibebani KPI dashboard atau report summary besar

#### E. Supporting Reports

Fungsi:

- `Supplier Performance`
- `Audit Viewer`

Target:

- tetap sebagai laporan khusus
- tidak bercampur dengan dashboard utama

### 3.3 Struktur menu target

1. **Dashboard**
   - Executive Dashboard

2. **Operations**
   - Purchase Orders
   - Shipment
   - Receiving

3. **Monitoring**
   - Monitoring Hub
   - Traceability
   - Supplier Performance

4. **Administration**
   - Audit Viewer
   - System Parameters
   - Users
   - Master Data

---

## 4. Workflow Changes Yang Disarankan

Bagian ini adalah **target flow**, bukan baseline aktif saat ini.

### 4.1 Flow target per role

#### Supervisor

1. buka `Dashboard`
2. lihat `at-risk`, `top delayed suppliers`, `action center`
3. klik masuk ke `Monitoring Hub` atau `Supplier Performance`
4. drill-down ke `Traceability` jika perlu audit timeline

#### Staff Operasional

1. buka `Purchase Orders` untuk kerja dokumen
2. update ETD dari detail PO atau planner khusus
3. buka `Shipment` untuk susun draft / edit shipment
4. buka `Receiving` untuk proses penerimaan
5. gunakan `Monitoring Hub` hanya untuk follow-up outstanding, bukan untuk input utama

#### Administrator

1. lakukan semua alur staff bila perlu
2. review `Audit Viewer`
3. kelola `Document Terms`, settings, dan users

### 4.2 Flow target halaman monitoring

Target hubungan layar:

- `Dashboard` -> overview
- `Monitoring Hub` -> operational analysis
- `PO Detail` -> corrective action
- `Traceability` -> event investigation

Artinya:

- jangan ada tiga layar berbeda yang sama-sama menjawab “lihat outstanding PO dan item”

---

## 5. Status Architecture Strategy

### 5.1 Masalah arsitektur saat ini

Saat ini:

- transaksi menyimpan string status langsung di tabel
- `DocumentTermCodes` hanya membungkus string yang sama
- `document_terms` dipakai terutama untuk label dan badge

Contoh saat ini:

- `DocumentTermCodes::PO_OPEN = 'Open'`
- `DocumentTermCodes::SHIPMENT_DRAFT = 'Draft'`
- `DocumentTermCodes::ITEM_CONFIRMED = 'Confirmed'`

Masalahnya:

1. code internal tidak berbeda dari display label
2. perubahan istilah bisnis berisiko menyentuh code logic
3. ada istilah yang sama lintas group:
   - `Cancelled`
   - `Closed`
   - `Draft`
4. query, filter, badge, dan business rule sama-sama bergantung ke string yang human-readable

### 5.2 Keputusan strategis

Ke depan, sistem harus memisahkan:

1. **domain status code**
   - stabil
   - tidak human-facing
   - dipakai di tabel transaksi dan business rule

2. **display term**
   - editable
   - dipakai untuk label, badge, dan urutan tampilan

### 5.3 Target desain status

Contoh target:

- PO:
  - `po_issued`
  - `po_open`
  - `po_late`
  - `po_closed`
  - `po_cancelled`

- PO item:
  - `item_waiting`
  - `item_confirmed`
  - `item_late`
  - `item_partial`
  - `item_closed`
  - `item_force_closed`
  - `item_cancelled`

- Shipment:
  - `shipment_draft`
  - `shipment_shipped`
  - `shipment_partial_received`
  - `shipment_received`
  - `shipment_cancelled`

- Goods receipt:
  - `gr_posted`
  - `gr_cancelled`

### 5.4 Peran `document_terms` setelah refactor

Setelah refactor, `document_terms` harus dipakai untuk:

- label display
- deskripsi
- badge class / color
- sort order
- apakah term aktif ditampilkan di UI

`document_terms` **tidak** boleh dipakai untuk:

- state transition logic
- source of truth domain state
- validasi status final/non-final

### 5.5 Kesimpulan penting

`term` tetap berguna, tetapi hanya jika kita benar-benar memisahkan:

- `status_code` = internal truth
- `term.label` = UI language

Selama keduanya masih string yang sama, manfaat `term` memang terasa setengah jadi.

---

## 6. Blade & UI Refactor Direction

### 6.1 Blade yang sebaiknya digabung atau disederhanakan

#### Kelompok monitoring/report

- `monitoring.blade.php`
- `summary-po.blade.php`
- `summary-item.blade.php`

Target:

- gabungkan menjadi satu `Monitoring Hub`

#### Dashboard

- `dashboard.blade.php`

Target:

- kurangi densitas informasi
- sisakan overview dan queue only

#### Traceability

- `traceability/index.blade.php`

Target:

- ubah dari “tabel summary lain” menjadi “investigation workspace”

### 6.2 Blade yang saat ini masih valid secara konsep

- `po/index.blade.php`
- `po/show.blade.php`
- `shipments/index.blade.php`
- `shipments/edit.blade.php`
- `receiving/index.blade.php`
- `supplier-performance.blade.php`
- `audit/index.blade.php`

Tetapi tetap perlu dirapikan agar:

- pola filter konsisten
- pola summary konsisten
- pattern “overview vs execution” tidak tercampur

### 6.3 Pola layar yang harus distandarkan

Setiap layar harus jatuh ke salah satu pola:

1. **Overview**
   - KPI
   - chart
   - short lists

2. **Monitoring**
   - filter bar
   - summary chips
   - 1 tabel utama
   - optional detail pane

3. **Execution**
   - form/action area
   - context info
   - supporting table

4. **Investigation**
   - filter/search
   - timeline/detail panel
   - audit/tracking info

---

## 7. Dashboard Redesign Rules

### 7.1 Dashboard saat ini terlalu ramai karena:

- terlalu banyak card dengan prioritas yang setara
- tabel dan chart bercampur tanpa hirarki kuat
- ada terlalu banyak jalan keluar ke halaman lain
- beberapa informasi lebih cocok berada di monitoring hub

### 7.2 Dashboard target

Dashboard maksimum terdiri dari:

1. KPI row
2. top risk supplier / at-risk summary
3. action center
4. shortcut ke monitoring hub

Dashboard tidak perlu menampilkan:

- tabel PO panjang
- tabel item panjang
- ringkasan outstanding lengkap
- investigasi traceability

### 7.3 Acceptance baru untuk dashboard

- supervisor tahu 3 prioritas hari ini dalam 10 detik
- tidak perlu scroll panjang untuk memahami risiko utama
- detail hanya muncul setelah klik ke monitoring/report terkait

---

## 8. Refactor Besar Yang Direkomendasikan

### Phase A. Information Architecture Cleanup

1. putuskan halaman utama monitoring
2. gabungkan `summary-po` dan `summary-item` ke `Monitoring Hub`
3. sederhanakan dashboard
4. rapikan sidebar sesuai struktur target

### Phase B. Status Normalization

1. ubah constant dari display string ke stable internal code
2. migrasikan nilai status transaksi bertahap
3. gunakan `document_terms` sebagai display catalog
4. buat `StatusResolver` dan `StatusTransitionPolicy` yang eksplisit

### Phase C. Traceability & Audit

1. ubah traceability menjadi timeline workspace
2. hubungkan audit viewer ke entitas PO / shipment / GR
3. tampilkan event berdasarkan timeline, bukan hanya agregasi tabel

### Phase D. UI Consistency

1. standardisasi filter bar
2. standardisasi page header
3. standardisasi summary chips
4. standardisasi empty state dan action state

---

## 9. Definition of Good UX Setelah Refactor

1. user tahu halaman utama untuk outstanding adalah `Monitoring Hub`, bukan memilih antara tiga report mirip
2. supervisor tidak lagi kebingungan membaca dashboard
3. staff tahu perbedaan jelas antara:
   - monitoring
   - eksekusi
   - investigasi
4. status tidak ambigu antara code internal dan label display
5. perubahan istilah bisnis tidak memaksa perubahan logic transaksi

---

## 10. Keputusan Dokumentasi

Mulai sekarang:

- [README.md](../README.md) tetap menjadi baseline flow aktif saat ini
- dokumen ini menjadi target redesign
- jika code nanti benar-benar berubah mengikuti dokumen ini, maka dokumen berikut wajib ikut diperbarui:
  - [README.md](../README.md)
  - [STATUS_DICTIONARY.md](./STATUS_DICTIONARY.md)
  - [SOP_OPERASIONAL.md](./SOP_OPERASIONAL.md)
  - [DEVELOPER_GUIDE.md](./DEVELOPER_GUIDE.md)
  - [TEST_SCENARIOS.md](./TEST_SCENARIOS.md)

Dokumen ini dengan sengaja mendorong perubahan besar, karena repo saat ini sudah memasuki titik di mana penambahan fitur kecil tanpa penyederhanaan struktur justru akan menambah kebingungan user dan biaya perawatan kode.
