# DIGITALISASI MONITORING PO & RECEIVING MATERIAL LABEL

Aplikasi internal ERP-like untuk kontrol proses purchasing hingga receiving material label berdasarkan BRD operasional manufacturing.

## Fitur Utama (Sesuai BRD)
- Master Data: Supplier, Item, UOM, Warehouse, Plant
- Purchase Order: direct entry, status aggregate berbasis item, timeline status
- Shipment Tracking
- Goods Receiving berbasis PO + kalkulasi outstanding otomatis
- Traceability & monitoring
- Dashboard KPI operasional dengan overview ringkas
- Halaman Monitoring Item lengkap dengan filter dan pencarian real-time
- Outstanding report (filter-first)
- Audit trail
- Settings (termasuk over-receipt)

## Aturan Master Data (Mandatory)
- `supplier_code` dan `item_code` dipaksa:
  - trim whitespace
  - uppercase
  - unique (aplikasi + index DB)
- Tidak ada hard delete untuk data referensi transaksi; gunakan status aktif/nonaktif.
- Pesan validasi Bahasa Indonesia:
  - `Kode supplier sudah digunakan`
  - `Kode item sudah digunakan`

## Instalasi
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Konfigurasi Environment
```env
APP_TIMEZONE=Asia/Jakarta
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_monitoring_po
DB_USERNAME=root
DB_PASSWORD=
```

## Format Dokumen Auto-Generate
- PO: `PO-YYYYMMDD-####`
- Shipment: `SHP-YYYYMMDD-####`
- GR: `GR-YYYYMMDD-####`

## Akun Demo
- Email: `admin@erp.local`
- Password: `password`

## Ringkasan Role
- Admin: full access, settings, audit, master data
- Purchasing: PO creation/edit, shipment update
- Purchasing Manager: approval & monitoring
- Warehouse: goods receiving
- Compliance/Viewer: monitoring read-only

## Alur PO & Receiving
1. Buat PO, sistem otomatis memberi status awal `PO Issued`.
2. Setiap item PO mulai dari status `Waiting`.
3. Purchasing mengisi ETD per item saat supplier sudah mengonfirmasi jadwal item tersebut.
4. Warehouse posting GR berbasis item outstanding.
5. Sistem update `received_qty`, `outstanding_qty`, status item, lalu refresh status agregat PO.

## Halaman Monitoring
- **Dashboard**: Overview KPI dan ringkasan item monitoring (limit 5 item) dengan tombol "Lihat Semua"
- **Monitoring Lengkap**: Tabel komprehensif semua item PO dengan status real-time, filter berdasarkan status item (Waiting/Confirmed/Partial/Late/Closed) dan status ETD (On-Time/At-Risk/N/A), serta pencarian real-time berdasarkan PO, item, atau supplier.

## Aturan Status Saat Ini
### Status item
- `Waiting`: item belum dikonfirmasi supplier, ETD belum diisi.
- `Confirmed`: item sudah dikonfirmasi supplier, ETD terisi, belum ada receiving.
- `Partial`: item sudah diterima sebagian, outstanding masih ada.
- `Late`: item sudah dikonfirmasi supplier, ETD terisi, tetapi ETD sudah lewat dan masih outstanding.
- `Closed`: qty item sudah terpenuhi.
- `Cancelled`: item dibatalkan atau force close.

### Status ETD (Estimated Time of Delivery)
- `On-Time`: ETD terisi dan masih di masa depan (>= hari ini).
- `At-Risk`: ETD terisi tetapi sudah lewat (< hari ini).
- `N/A`: ETD belum diisi supplier.

### Status PO
- `PO Issued`: belum ada item yang dikonfirmasi dan belum ada receiving.
- `Confirmed`: minimal ada 1 item sudah memiliki ETD, belum ada receiving.
- `Partial`: minimal ada 1 item sudah receiving, tetapi PO belum selesai.
- `Closed`: seluruh item sudah selesai atau outstanding habis.
- `Cancelled`: seluruh item dibatalkan atau PO dibatalkan.

## Seed Demo
- 10 suppliers
- 50 items
- 20 sample PO (variasi status)
- sampai 10 sample goods receipt

## Testing
```bash
php artisan test
```

## Known Limitations
- Export Excel/PDF belum diaktifkan pada fase ini.
- Upload attachment dokumen belum final.
- Laporan lanjutan (aging, supplier OTP detail) masih basic.

## Future Improvements
- Export Excel/PDF untuk seluruh report.
- Multi-level approval dinamis.
- Attachment management + preview.
- Integrasi BC reference lebih mendalam.

## SOP Penggunaan Aplikasi (Ringkas)
### A. Alur Purchase Order sampai Receiving
1. **Buat PO** dari menu `Dokumen PO`; status awal otomatis `PO Issued`.
2. Setiap item PO dibuat dengan status awal `Waiting`.
3. Saat supplier memberi kepastian jadwal, update ETD di item terkait.
4. Sistem akan mengubah status item menjadi:
   - `Confirmed` jika ETD sudah diisi dan belum ada receiving
   - `Partial` jika sudah ada receiving tetapi outstanding masih ada
   - `Closed` jika qty item sudah terpenuhi
   - `Cancelled` jika item dibatalkan atau force close
5. Status PO dihitung ulang otomatis sebagai agregasi item:
   - `PO Issued`: belum ada item yang dikonfirmasi dan belum ada receiving
   - `Confirmed`: minimal ada 1 item sudah punya ETD, belum ada receiving
   - `Partial`: minimal ada receiving pada salah satu item, tetapi PO belum selesai
   - `Closed`: semua item selesai atau outstanding habis
   - `Cancelled`: semua item dibatalkan atau PO dibatalkan
6. Masuk menu `Dokumen Receiving` untuk posting penerimaan **per item** pada tabel outstanding.

### B. Kenapa muncul perbedaan dengan dokumentasi flow lama?
Penyebab umum:
- Dokumentasi lama masih mengacu ke flow transisi manual seperti `Draft`, `Submitted`, dan `Approved`.
- Pada implementasi saat ini status PO tidak lagi diubah manual per milestone lama, tetapi dihitung dari status item dan receiving.
- PO yang sudah `Cancelled` atau `Closed` tidak bisa diproses lebih lanjut.

Solusi:
- Gunakan update ETD item dan proses receiving sebagai penggerak perubahan status.
- Hindari mengacu ke flow lama `Draft -> Submitted -> Approved` karena tidak lagi menjadi acuan utama.
- Pastikan item atau PO belum `Cancelled` atau `Closed`.

### C. SOP Receiving Item-by-Item
- Receiving dilakukan **satu item per transaksi** (bukan 1 PO full sekaligus).
- Gunakan tombol `Post Item` pada baris item yang datang.
- Isi tanggal terima, qty terima, dan nomor dokumen pendukung.
- Ulangi untuk item lain saat item benar-benar datang.

### D. Catatan Status Awal dan Konfirmasi Parsial
- Status awal PO yang benar pada implementasi saat ini adalah `PO Issued`, bukan `Draft`.
- Status awal item PO adalah `Waiting`.
- Saat baru 1 item dikonfirmasi (ETD terisi) sedangkan item lain belum dikonfirmasi, sistem saat ini akan menaikkan status PO menjadi `Confirmed`.
- Jadi, status PO `Confirmed` saat ini berarti **sudah ada item yang confirmed**, belum tentu **semua item** dalam PO sudah confirmed.

Saran bisnis:
- Jika tim ingin status PO `Confirmed` berarti seluruh item aktif sudah dikonfirmasi supplier, ubah aturan agregasi PO menjadi `Confirmed` hanya bila semua item non-cancelled sudah memiliki ETD.
- Untuk kondisi campuran, tambahkan status baru `Partially Confirmed` agar lebih jelas saat sebagian item sudah confirmed dan item lainnya masih `Waiting`.
- Jika belum ingin mengubah kode, gunakan status item sebagai acuan operasional utama, dan pahami bahwa PO `Confirmed` masih bisa berisi item lain yang belum dikonfirmasi.

Rekomendasi paling aman:
- Pertahankan header PO di `PO Issued` selama masih ada kombinasi item `Waiting` dan `Confirmed`.
- Jika ingin visibilitas lebih jelas di level header PO, tambahkan status `Partially Confirmed` sebagai status antara `PO Issued` dan `Confirmed`.

## Dokumen Konsep UI/UX Strategis
Lihat dokumen konsep lengkap: `UI_UX_PRODUCT_STRATEGY.md`.

## Cleanup Database (Tabel Tidak Dipakai)
Berdasarkan pemakaian kode saat ini, tabel berikut tidak lagi dipakai oleh flow aktif:
- `po_approvals`
- `supplier_confirmations`
- `shipment_items`

Migrasi pembersihan sudah disediakan di:
- `database/migrations/2026_03_19_130000_drop_unused_procurement_tables.php`

Jalankan:
```bash
php artisan migrate
```

Jika ingin rollback:
```bash
php artisan migrate:rollback --step=1
```
